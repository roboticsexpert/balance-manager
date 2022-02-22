<?php


namespace Roboticsexpert\BalanceManager;


use Illuminate\Support\Facades\Facade;
use Roboticsexpert\BalanceManager\Services\BalanceManager;

class BalanceManagerFacade extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return BalanceManager::class;
    }
}
