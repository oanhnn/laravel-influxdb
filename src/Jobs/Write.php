<?php

namespace Laravel\InfluxDB\Jobs;

use Laravel\InfluxDB\Facades\InfluxDB;

/**
 * Class Write
 *
 * @package     Laravel\InfluxDB\Jobs
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class Write extends Job
{
    /**
     * @var string|array
     */
    public $payload;

    /**
     * @var array
     */
    public $parameters;

    /**
     * Write constructor.
     *
     * @param array $parameters
     * @param string|array $payload
     */
    public function __construct(array $parameters, $payload)
    {
        $this->parameters = $parameters;
        $this->payload = $payload;
    }

    /**
     * @return void
     */
    public function handle()
    {
        InfluxDB::getClient()->write($this->parameters, $this->payload);
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return [static::class . ':' . (is_string($this->payload) ? 1 : count($this->payload))];
    }
}
