<?php


namespace Roboticsexpert\BalanceManager\Exceptions;


use Decimal\Decimal;

class NotEnoughBalanceException extends \Exception
{
    private string $currency;
    private Decimal $amount;

    /**
     * NotEnoughBalanceException constructor.
     * @param string $currency
     * @param Decimal $amount
     */
    public function __construct(string $currency, Decimal $amount)
    {
        $this->currency = $currency;
        $this->amount = $amount;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return Decimal
     */
    public function getAmount(): Decimal
    {
        return $this->amount;
    }

}
