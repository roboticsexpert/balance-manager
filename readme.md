# Laravel Balance manager

//TODO this is test package



bring feature flag functionality to your project simple as possible

if you want to know what feature flag is, check martin fowler topic about it:
https://martinfowler.com/articles/feature-toggles.html

## Installation

```php
composer require roboticsexpert/feature-flag
```

after instaling composer package, if you use auto discovery for service providers, everything ok, but if you blocked
that just add this line to `config/app.php` in `providers` section:

```php
Roboticsexpert\FeatureFlag\LaravelDecimalServiceProvider::class
```

after that you should run

```bash 
php artisan migrate
```

## Usage

you can use this project with 2 strategy,Facade or Dependency injection!

I suggest to you to use it with dependecy injection for IDE auto complete feature but use it as you prefer!

### Facade

you can get feature flag service like this:

```php
use Roboticsexpert\FeatureFlag\FeatureFlagFacade as FeatureFlag;


FeatureFlag::getTypes()
```

### Dependecy Injection

you can get feature flag service like this:

```php
use Roboticsexpert\FeatureFlag\Services\FeatureFlagService;

$featureFlag=app(\Roboticsexpert\FeatureFlag\Services\FeatureFlagService::class); //or you can get this service from input of controller method

$featureFlag->getTypes();
```

## Methods

### Create Feature flag

```php
$featureFlagModel=$featureFlagService->createFeatureFlag('FEATURE_NAME');
dd($featureFlagModel->name);
```

### Change type of feature flag (Admin)

```php
//OPTIONS: DISABLED , PRIVATE , PUBLIC
$featureFlagModel=$featureFlagService->changeFeatureFlagType('FEATURE_NAME','DISABLED');
//or    
$featureFlagModel=$featureFlagService->changeFeatureFlagType('FEATURE_NAME',\Roboticsexpert\FeatureFlag\Models\FeatureFlag::TYPE_DISABLED);
```

### Delete feature flag (Admin)

```php
$featureFlagService->destroyFeatureFlag('FEATURE_1');
```

### Get all Feature flags (Admin)

```php
$featureFlags=$featureFlagService->getAllFeatureFlags();
```

### Attach / Detach  a user to/from a feature flag

```php
$featureFlagService->attachUserToFeatureFlag(1,'FEATURE_1');

$featureFlagService->detachUserToFeatureFlag(1,'FEATURE_1');
```

### Get List of not active features for a user or public user

first of all is should explain why you should user disabled features intead of enabled features !

- when a feature became public for all users, we should to tell all clients that the feature is enable until clients be
  update, but old clients need to have that feature for ever!!!
- if you return enabled features, your list of features became large and large!

finally i prefred to implement disabled features and you should return this list to client !

```php
//It will return array of names of features (string)
//No input for not logged in users
$featureFlagService->getDisabledFeatureFlagsName();

//User identifier for logged in users
$featureFlags=$featureFlagService->getDisabledFeatureFlagsName(1);
```
    

