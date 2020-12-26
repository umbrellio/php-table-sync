# PHP TableSync
###### PHP's implementation of the library providing data synchronization between microservices

## Installation
First you need to add the repository in the `composer.json` file
```
"repositories": [
    {
        "type": "vcs",
        "url": "git@gitlab.task4work.info:common-libs/php-table-sync.git"
    }
]
```
Installation can be done with composer
```
composer require common-libs/php-table-sync
```

## Usage
Let's describe the model that needs to be synchronized using an example `User.php`
```
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
```
...
'log' => [
    'channel' => 'table_sync',
],
...
```
- and describe this channel in `config/logging.php`
```
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
```
...
'table_sync' => [
    'driver' => 'daily',
],
...
```
