<?php

return [
    /*
    |--------------------------------------------------------------------------
    | InfluxDB Database config
    |--------------------------------------------------------------------------
    |
    |
    */
    'database' => [
        /**
         * InfluxDB DSN
         * https+influxdb://username:pass@localhost:8086/databasename
         * udp+influxdb://username:pass@localhost:4444/databasename
         */
        'dsn' => env('INFLUXDB_DSN'),

        // http or https or udp
        'protocol' => env('INFLUXDB_PROTOCOL', 'http'),
        'host' => env('INFLUXDB_HOST', 'localhost'),
        'port' => env('INFLUXDB_PORT', 8086),
        'username' => env('INFLUXDB_USER', ''),
        'password' => env('INFLUXDB_PASSWORD', ''),
        'dbname' => env('INFLUXDB_DBNAME', 'dbname'),
        'ssl' => env('INFLUXDB_SSL', false),

        'verify_ssl' => env('INFLUXDB_VERIFYSSL', false),
        'timeout' => env('INFLUXDB_TIMEOUT', 0),
    ],

    'queue' => [
        'enable' => false,
        'name' => env('INFLUXDB_QUEUE_NAME', 'default'),
    ],

    'logging' => [
        'limit' => 5,
        'ignore_error' => true,
        'datetime_format' => 'Y-m-d H:i:s',
    ],
];
