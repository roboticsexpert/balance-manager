<?php


namespace Roboticsexpert\BalanceManager\Services;


use Carbon\Carbon;
use Decimal\Decimal;
use Illuminate\Support\Facades\DB;
use Roboticsexpert\BalanceManager\DataTransferObjects\BalanceChangeResult;
use Roboticsexpert\BalanceManager\Exceptions\NotEnoughBalanceException;
use Roboticsexpert\BalanceManager\Interfaces\IBalanceHistoryRelated;
use Roboticsexpert\BalanceManager\Models\Balance;
use Roboticsexpert\BalanceManager\Models\BalanceHistory;
use Roboticsexpert\BalanceManager\Models\LockedBalance;

class BalanceManager
{
    private array $currencies;

    /**
     * BalanceManager constructor.
     * @param array $currencies
     */
    public function __construct(array $currencies=[])
    {
        $this->currencies = $currencies;
    }

    /**
     * @param int $userId
     * @param string $currency
     * @return Balance
     */
    public function getBalanceByUserIdAndCurrency(int $userId, string $currency): Balance
    {
        return Balance::firstOrCreate([
            'currency' => $currency,
            'user_id' => $userId
        ]);
    }

    /**
     * @param int $userId
     * @return Balance[]
     */
    public function getAllBalancesByUserId(int $userId): array
    {
        $results = [];
        $balances = Balance::where('user_id', $userId)->get();

        $found = [];
        foreach ($this->currencies as $currency) {
            $found[$currency] = false;
        }
        /** @var Balance $balance */
        foreach ($balances as $balance) {
            $results[] = $balance;
            $found[$balance->currency] = true;
        }

        foreach ($found as $currency => $flag) {
            if ($flag == false) {
                $balance = new Balance;
                $balance->user_id = $userId;
                $balance->currency = $currency;
                $results[] = $balance;
            }
        }
        return $results;
    }


    /**
     * @param int $userId
     * @param string $currency
     * @param string $reason
     * @param IBalanceHistoryRelated $lockOwner
     * @param Decimal|null $value
     * @return void
     * @throws \Throwable
     */
    public function releaseLockedBalanceByUserIdAndCurrency(int $userId, string $currency, string $reason, IBalanceHistoryRelated $lockOwner, ?Decimal $value = null): void
    {
        DB::transaction(function () use ($userId, $currency, $reason, $lockOwner, $value) {

            if (!$value) {
                /** @var LockedBalance $lockedBalance */
                $lockedBalance = LockedBalance::where('currency', $currency)
                    ->where('user_id', $userId)
                    ->where('owner_type', $lockOwner->getType())
                    ->where('owner_id', $lockOwner->getIdentifier())->first();

                if (!$lockedBalance) {
                    //TODO change exception !
                    throw new \Exception('could not unlock locked balance');
                }

                $value = $lockedBalance->value;
            }

            if (!$value->isZero()) {
                $this->changeBalanceByUserIdAndCurrency($userId, $currency, $reason, $lockOwner, new Decimal('0'), $value->mul(-1));
            }

            $affectedRows = LockedBalance::where('value', '=', $value->isZero() ? '0' : $value->toFixed(16))
                ->where('currency', $currency)
                ->where('user_id', $userId)
                ->where('owner_type', $lockOwner->getType())
                ->where('owner_id', $lockOwner->getIdentifier())->delete();

            //This should not be happen anything ! i don't know how to produce it ... but it happend , i will add this line as test!
            // @codeCoverageIgnoreStart
            if ($affectedRows != 1) {
                //TODO change exception !
                throw new \Exception('could not unlock locked balance');
            }
            // @codeCoverageIgnoreEnd


        }, 3);
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param string $reason
     * @param IBalanceHistoryRelated $lockOwner
     * @param IBalanceHistoryRelated $related
     * @param Decimal $valueForConsume
     * @return BalanceChangeResult
     */
    public function consumeLockedBalanceByUserIdAndCurrency(int $userId, string $currency, string $reason, IBalanceHistoryRelated $lockOwner, IBalanceHistoryRelated $related, Decimal $valueForConsume): BalanceChangeResult
    {
        return DB::transaction(function () use ($userId, $currency, $reason, $lockOwner, $related, $valueForConsume) {
            $balanceChangeResult = $this->changeBalanceByUserIdAndCurrency($userId, $currency, $reason, $related, $valueForConsume->mul(-1), $valueForConsume->mul(-1));
            $affectedRows = LockedBalance::where('value', '>=', $valueForConsume->toFixed(16))
                ->where('currency', $currency)
                ->where('user_id', $userId)
                ->where('owner_type', $lockOwner->getType())
                ->where('owner_id', $lockOwner->getIdentifier())->update([
                    'value' => DB::raw('`value` - ' . $valueForConsume->toFixed(16))
                ]);

            if ($affectedRows != 1)
                //TODO change exception !
                throw new \Exception('could not consume lock, user_id: ' . $userId . ' currency: ' . $currency . ' value: ' . $valueForConsume->toFixed(16) . ' lock owner ' . $lockOwner->getType() . ":" . $lockOwner->getIdentifier());

            return $balanceChangeResult;

        }, 3);
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param string $reason
     * @param IBalanceHistoryRelated $lockOwner
     * @param Decimal $lockValue
     * @return BalanceChangeResult
     * @throws NotEnoughBalanceException
     */
    public function getLockedBalanceByUserIdAndCurrency(int $userId, string $currency, string $reason, IBalanceHistoryRelated $lockOwner, Decimal $lockValue): BalanceChangeResult
    {
        return DB::transaction(function () use ($userId, $currency, $reason, $lockOwner, $lockValue) {

            $balanceChange = $this->changeBalanceByUserIdAndCurrency($userId, $currency, $reason, $lockOwner, new Decimal('0'), $lockValue);

            LockedBalance::create([
                'currency' => $currency,
                'user_id' => $userId,
                'owner_type' => $lockOwner->getType(),
                'owner_id' => $lockOwner->getIdentifier(),
                'value' => $lockValue
            ]);

            return $balanceChange;
        }, 3);
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param string $reason
     * @param IBalanceHistoryRelated $relatedModel
     * @param Decimal $valueChange
     * @param Decimal $lockedValueChange
     * @return BalanceChangeResult
     * @throws NotEnoughBalanceException|\Throwable
     */
    public function changeBalanceByUserIdAndCurrency(
        int $userId,
        string $currency,
        string $reason,
        IBalanceHistoryRelated $relatedModel,
        Decimal $valueChange,
        Decimal $lockedValueChange
    ): BalanceChangeResult
    {
        $balance = $this->getBalanceByUserIdAndCurrency($userId, $currency);
        $remainingBalance = $balance->value->sub($balance->locked_value);
        if ($balance->value->add($valueChange)->isNegative())
            throw new NotEnoughBalanceException($currency, $remainingBalance);
        if ($balance->value->sub($lockedValueChange)->sub($balance->locked_value)->isNegative()) {
            throw new NotEnoughBalanceException($currency, $remainingBalance);
        }
        /** @var BalanceChangeResult $balanceChangeResult */
        $balanceChangeResult = DB::transaction(function () use ($userId, $currency, $valueChange, $reason, $relatedModel, $lockedValueChange, $balance) {


            $query = 'update `balances` set ';
            $wheres = ['`user_id`=' . $userId, '`currency`=' . "'$currency'"];
            $updates = [];
            if ($valueChange->isNegative()) {
                $wheres[] =
                    '`value`' .
                    '>=' .
                    $valueChange->mul(-1)->toFixed(16);
            }
            if ($lockedValueChange->isNegative()) {
                $wheres[] =
                    '`locked_value`' .
                    '>=' .
                    $lockedValueChange->mul(-1)->toFixed(16);
            } else {
                $wheres[] =
                    '`value` - `locked_value` - (' . $lockedValueChange->toFixed(16) . ')' .
                    '>=' .
                    '0';
            }

            $updates[] =
                '`value`=@value:=`value`+(' . $valueChange->toFixed(16) . ')';

            $updates[] =
                '`locked_value`=@locked_value:=`locked_value`+(' . $lockedValueChange->toFixed(16) . ')';

            $query .= ' ' . implode(',', $updates);
            $query .= ' where ' . implode(' and ', $wheres);


            $affectedRows = DB::update($query);


            $value = $balance->value->add($valueChange);
            $lockedValue = $balance->locked_value->add($lockedValueChange);

            if ($affectedRows != 1) {
                throw new NotEnoughBalanceException($currency, $value->sub($lockedValue));
            }

            $balanceHistory = null;
            if (!$valueChange->isZero()) {

                $query = 'select @value as value,@locked_value as locked_value';
                $values = DB::selectOne($query);

                $value = new Decimal((string)$values->value);
                $lockedValue = new Decimal((string)$values->locked_value);

                $balanceHistory = new BalanceHistory();
                $balanceHistory->currency = $currency;
                $balanceHistory->user_id = $userId;
                $balanceHistory->change_value = $valueChange;
                $balanceHistory->reason = $reason;
                $balanceHistory->related_model_id = $relatedModel->getIdentifier();
                $balanceHistory->related_model_type = $relatedModel->getType();
                $balanceHistory->value = new Decimal((string)$values->value);
                $balanceHistory->save();
            }

            return new BalanceChangeResult(true, $value, $lockedValue, $valueChange, $lockedValueChange, $balanceHistory);
        }, 3);


        return $balanceChangeResult;
    }

    public function getBalanceHistoryPaginated(?int $userId = null, ?string $currency = null)
    {
        $query = BalanceHistory::orderBy('id', 'desc');
        if ($userId) {
            $query = $query->where('user_id', $userId);
        }

        if ($currency) {
            $query = $query->where('currency', $currency);
        }

        return $query->paginate();
    }

    public function get30DaysReport(int $userId): array
    {

        $rows = BalanceHistory::where('created_at', '>=', Carbon::today()->subDays(30))
            ->where('user_id', $userId)
            ->groupByRaw('currency,date(created_at)')
            ->selectRaw('sum(change_value) as change_value,currency,date(created_at) as created_at')
            ->get();

        $changePerDateAndCurrency = [];

        foreach ($rows as $row) {
            $created_at = $row->created_at->toDateString();
            if (!isset($changePerDateAndCurrency[$created_at])) {
                $changePerDateAndCurrency[$created_at] = [];
            }
            $changePerDateAndCurrency[$created_at][$row->currency] = new Decimal((string)$row->change_value);
        }

        $today = Carbon::today();
        $currentBalance = $this->getAllBalancesByUserIdAndCurrencyKey($userId);

        $lastBalance = [];
        /**
         * @var string $currency
         * @var Balance $balance
         */
        foreach ($currentBalance as $currency => $balance) {
            $lastBalance[$currency] = $balance->value;
        }

        $result = [];
        while ($today >= Carbon::now()->subDays(30)) {
            $result[$today->toDateString()] = $lastBalance;
            if (isset($changePerDateAndCurrency[$today->toDateString()])) {
                foreach ($changePerDateAndCurrency[$today->toDateString()] as $currency => $changeValue) {
                    $lastBalance[$currency] = $lastBalance[$currency]->sub($changeValue);
                }
            }
            $today->subDay();
        }

        return $result;
    }


}
