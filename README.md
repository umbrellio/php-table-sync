# PHP TableSync

###### PHP's implementation of the library providing data synchronization between microservices

[![Github Status](https://github.com/umbrellio/php-table-sync/workflows/CI/badge.svg)](https://github.com/umbrellio/php-table-sync/actions)
[![Coverage Status](https://coveralls.io/repos/github/umbrellio/php-table-sync/badge.svg?branch=master)](https://coveralls.io/github/umbrellio/php-table-sync?branch=master)
[![Latest Stable Version](https://poser.pugx.org/umbrellio/php-table-sync/v/stable.png)](https://packagist.org/packages/umbrellio/php-table-sync)
[![Total Downloads](https://poser.pugx.org/umbrellio/php-table-sync/downloads.png)](https://packagist.org/packages/umbrellio/php-table-sync)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Build Status](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/badges/build.png?b=master)](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/umbrellio/php-table-sync/?branch=master)


## Installation
```shell
composer require umbrellio/php-table-sync
```

## Usage
Let's describe the model that needs to be synchronized using an example `User.php`
```php
...
User extends Model implements SyncableModel
{
    use TableSyncable;

...

    public function routingKey(): string
    {
        return 'users';
    }

    public function getTableSyncableAttributes(): array
    {
        return [
            'id' => $this->external_id,
            'login' => $this->name,
            'email' => $this->email,
        ];
    }
...
```

When the model changes, the data will be sent according to the rules of `TableSyncObserver`, to get the data you need to run the command `table_sync:work`

## Logging
Logging based on the Monolog package and contains some extensions for it.
- specify the logging channel in `config/table_sync.php`
```php
...
'log' => [
    'channel' => 'table_sync',
],
...
```
- and describe this channel in `config/logging.php`
```php
...
'table_sync' => [
    'driver' => 'stack',
    'channels' => ['table_sync_daily', 'influxdb'],
],
'table_sync_daily' => [
    'driver' => 'daily',
    'formatter' => LineTableSyncFormatter::class,
    'formatter_with' => [
        'format' => '[%datetime%] %message% - %model% %event%',
    ],
    'path' => storage_path('logs/table_sync/daily.log'),
],
'influxdb' => [
    'driver' => 'influxdb',
    'measurement' => 'table_sync',
],
...
```

##### You can use the built-in `LineTableSyncFormatter::class` with the available parameters: `%datetime%` `%message%` `%direction%` `%model%` `%event%` `%routing%` `%attributes%` `%exception%`

###### Driver `influxdb` is an additional option and is not required to add in config
```php
...
'table_sync' => [
    'driver' => 'daily',
],
...
```

## Authors

Created by Korben Dallas.

<a href="https://github.com/umbrellio/">
<img style="float: left;" src="https://umbrellio.github.io/Umbrellio/supported_by_umbrellio.svg" alt="Supported by Umbrellio" width="439" height="72">
</a>
