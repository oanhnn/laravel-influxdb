<?php

namespace Laravel\InfluxDB\Jobs;

use InfluxDB\Database;
use Laravel\InfluxDB\Facades\InfluxDB;

/**
 * Class WritePoints
 *
 * @package     Laravel\InfluxDB\Jobs
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class WritePoints
{
    /**
     * @var array
     */
    public $points;

    /**
     * @var string
     */
    public $precision;

    /**
     * @var string|null
     */
    public $retentionPolicy;

    /**
     * WritePoints constructor.
     *
     * @param  \InfluxDB\Point[] $points
     * @param  string $precision
     * @param  string|null $retentionPolicy
     */
    public function __construct(
        array $points,
        string $precision = Database::PRECISION_SECONDS,
        $retentionPolicy = null
    ) {
        $this->points = $points;
        $this->precision = $precision;
        $this->retentionPolicy = $retentionPolicy;
    }

    /**
     * @return void
     * @throws \InfluxDB\Exception
     */
    public function handle()
    {
        InfluxDB::writePoints(
            $this->points,
            $this->precision,
            $this->retentionPolicy
        );
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return [static::class . ':' . count($this->points)];
    }
}
