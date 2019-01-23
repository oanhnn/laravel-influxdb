<?php

namespace Laravel\InfluxDB\Facades;

use Illuminate\Support\Facades\Facade;
use InfluxDB\Database;
use Laravel\InfluxDB\Jobs\Write;
use Laravel\InfluxDB\Jobs\WritePayload;
use Laravel\InfluxDB\Jobs\WritePoints;
use RuntimeException;

/**
 * InfluxDB class
 *
 * @package     Laravel\InfluxDB\Facades
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 *
 * @method static void drop()
 * @method static bool exists()
 * @method static string getName()
 * @method static \InfluxDB\Client getClient()
 * @method static array listRetentionPolicies()
 * @method static \InfluxDB\Query\Builder getQueryBuilder()
 * @method static \InfluxDB\ResultSet query(string $query, array $params = [])
 * @method static void alterRetentionPolicy(\InfluxDB\Database\RetentionPolicy $retentionPolicy)
 * @method static \InfluxDB\ResultSet create(\InfluxDB\Database\RetentionPolicy $retentionPolicy = null)
 * @method static \InfluxDB\ResultSet createRetentionPolicy(\InfluxDB\Database\RetentionPolicy $retentionPolicy)
 */
class InfluxDB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return Database::class;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        switch ($method) {
            case 'write':
            case 'writePoints':
            case 'writePayload':
                return static::$method(...$arguments);
            default:
                return static::getInstance()->$method(...$arguments);
        }
    }

    /**
     * @param array $parameters
     * @param string|array $payload
     * @return bool
     */
    public static function write(array $parameters, $payload): bool
    {
        if (config('influxdb.queue.enable', false) === true) {
            dispatch(new Write($parameters, $payload))->onQueue(config('influxdb.queue.name', 'default'));

            return true;
        }

        return static::getInstance()->getClient()->write($parameters, $payload);
    }

    /**
     * @param  string|array $payload
     * @param  string $precision
     * @param  string|null $retentionPolicy
     * @return bool
     * @throws \InfluxDB\Exception
     */
    public static function writePayload(
        $payload,
        $precision = Database::PRECISION_SECONDS,
        $retentionPolicy = null
    ): bool {
        if (config('influxdb.queue.enable', false) === true) {
            dispatch(new WritePayload($payload, $precision, $retentionPolicy))
                ->onQueue(config('influxdb.queue.name', 'default'));

            return true;
        }

        return static::getInstance()
                ->writePayload($payload, $precision, $retentionPolicy);
    }

    /**
     * @param  \InfluxDB\Point[] $points
     * @param  string $precision
     * @param  string|null $retentionPolicy
     * @return bool
     * @throws \InfluxDB\Exception
     */
    public static function writePoints(
        array $points,
        $precision = Database::PRECISION_SECONDS,
        $retentionPolicy = null
    ): bool {
        if (config('influxdb.queue.enable', false) === true) {
            dispatch(new WritePoints($points, $precision, $retentionPolicy))
                ->onQueue(config('influxdb.queue.name', 'default'));

            return true;
        }

        return static::getInstance()
                ->writePoints($points, $precision, $retentionPolicy);
    }

    /**
     * @return Database
     * @throws \RuntimeException
     */
    protected static function getInstance(): Database
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance;
    }
}
