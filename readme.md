# Laravel Balance manager

in many projects, you need to have credit (balance) concept for your user that make you distract from your main business
logic.

with this package you will have balance for your users easily without being worry about `Race Condition`
and `Double Spending`.

this project mainly designed for exchange systems, but you can use it in any project.

## Installation

```php
composer require roboticsexpert/balance-manager
```

after instaling composer package, if you use auto discovery for service providers, everything ok, but if you blocked
that just add this line to `config/app.php` in `providers` section:

```php
Roboticsexpert\BalanceManager\BalanceManagerServiceProvider::class
```

after that you should run

```bash 
php artisan migrate
```

and this:

```bash 
php artisan vendor:publish --provider="Roboticsexpert\BalanceManager\BalanceManagerServiceProvider"
```

it will create `balance.php` file in your config file.

## Usage

First of all, you should decide with currencies you want to have in your system, and consider a KEY for each currency
and add those keys in balance.php config file

```php
return [

    // add symbols of your currencies
    // symbols must be lower than 16 char

    'currencies'=>[
        'BTC',
        'TMN',
        'DOGE',
    ]
];
```

you can use this project with 2 strategy,Facade or Dependency injection!

I suggest to you to use it with dependecy injection for IDE auto complete feature but use it as you prefer!

after that you can get `BalanceManager` from with these to methods:

### Facade

you can get BalanceManager service like this:

```php
use Roboticsexpert\BalanceManager\BalanceManagerFacade as BalanceManager;


BalanceManager::getAllBalancesByUserId(1)
```

### Dependency Injection

you can get BalanceManager service from `app()` like this:

```php
use Roboticsexpert\BalanceManager\Services\BalanceManager;

$balanceManager=app(BalanceManager::class); 

$balanceManager->getAllBalancesByUserId(1);
```

or get from laravel automatic dependency injection

```php
use Roboticsexpert\BalanceManager\Services\BalanceManager;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(BalanceManager $balanceManager){
        dd($balanceManager->getAllBalancesByUserId(1));
    }
}

```

## Methods

### Get all balance for user

it will return array of `Balance` model

```php
$balances= $balanceManager->getAllBalancesByUserId("USER_ID");
dd($balances);
```

### Change balance of user

```php
$balanceChangeResult= $balanceManager->changeBalanceByUserIdAndCurrency(
        int $userId, //user id
        string $currency, // like USDT, TMN
        string $reason, // a unique string for each action
        IBalanceHistoryRelated $model, // a model that is author of change balance
        Decimal $valueChange, // value you want to add or sub from user balance
        Decimal $lockedValueChange // in general usage it should be new \Decimal\Decimal(0)
);


