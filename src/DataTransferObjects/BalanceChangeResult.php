<?php

namespace Roboticsexpert\BalanceManager\DataTransferObjects;

use Decimal\Decimal;
use Roboticsexpert\BalanceManager\Models\BalanceHistory;

class BalanceChangeResult
{
    private bool $isSuccess;
    private ?BalanceHistory $balanceHistory;
    private Decimal $valueChanged;
    private Decimal $lockedValueChange;
    private Decimal $value;
    private Decimal $lockedValue;

    /**
     * BalanceChangeResult constructor.
     * @param bool $isSuccess
     * @param Decimal $valueChanged
     * @param Decimal $lockedValueChange
     * @param BalanceHistory|null $balanceHistory
     */
    public function __construct(bool $isSuccess, Decimal $value, Decimal $lockedValue, Decimal $valueChanged, Decimal $lockedValueChange, ?BalanceHistory $balanceHistory = null)
    {
        $this->isSuccess = $isSuccess;
        $this->balanceHistory = $balanceHistory;
        $this->valueChanged = $valueChanged;
        $this->lockedValueChange = $lockedValueChange;
        $this->value = $value;
        $this->lockedValue = $lockedValue;
    }

    /**
     * @return Decimal
     */
    public function getValue(): Decimal
    {
        return $this->value;
    }

    /**
     * @return Decimal
     */
    public function getLockedValue(): Decimal
    {
        return $this->lockedValue;
    }


    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @return BalanceHistory|null
     */
    public function getBalanceHistory(): ?BalanceHistory
    {
        return $this->balanceHistory;
    }

    /**
     * @return Decimal
     */
    public function getValueChanged(): Decimal
    {
        return $this->valueChanged;
    }

    /**
     * @return Decimal
     */
    public function getLockedValueChange(): Decimal
    {
        return $this->lockedValueChange;
    }
}
