<?php


namespace Roboticsexpert\BalanceManager\Models;


use Illuminate\Support\Facades\Facade;
use Roboticsexpert\FeatureFlag\Services\FeatureFlagService;

class BalanceManagerFacade extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return FeatureFlagService::class;
    }
}
