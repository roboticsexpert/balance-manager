<?php


namespace Roboticsexpert\BalanceManager\Interfaces;


interface IBalanceHistoryRelated
{
    public function getType(): string;

    public function getIdentifier(): string;
}
