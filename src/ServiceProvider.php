<?php

namespace Laravel\InfluxDB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use InfluxDB\Client;
use InfluxDB\Database;
use Laravel\InfluxDB\Log\Handler;
use Monolog\Logger;

/**
 * Class ServiceProvider
 *
 * @package     Laravel\InfluxDB
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__) . '/config/influxdb.php' => config_path('influxdb.php'),
            ], 'config');
        }

        Log::extend('influxdb', function ($app, array $config) {
            $handler = new Handler(
                $config['level'] ?? Logger::WARNING,
                $config['bubble'] ?? true
            );

            return new Logger($config['name'], [$handler]);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/influxdb.php', 'influxdb');

        $this->app->singleton(Database::class, function ($app) {
            $config = $app['config']->get('influxdb.database');

            if (empty($config['dsn'])) {
                if ($config['ssl'] && $config['protocol'] === 'http') {
                    $config['protocol'] = 'https';
                }

                $protocol = 'influxdb';
                if (in_array($config['protocol'], ['https', 'udp'])) {
                    $protocol = $config['protocol'] . '+' . $protocol;
                }

                $config['dsn'] = sprintf(
                    '%s://%s:%s@%s:%s/%s',
                    $protocol,
                    urlencode($config['username']),
                    urlencode($config['password']),
                    $config['host'],
                    $config['port'],
                    $config['dbname']
                );
            }

            $db = Client::fromDSN($config['dsn'], $config['timeout'], $config['verify_ssl']);
            if ($db instanceof Client) {
                $db = $db->selectDB($config['dbname']);
            }

            return $db;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Database::class,
        ];
    }
}
