<?php

namespace Laravel\InfluxDB\Jobs;

use InfluxDB\Database;
use Laravel\InfluxDB\Facades\InfluxDB;

/**
 * Class WritePayload
 *
 * @package     Laravel\InfluxDB\Jobs
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class WritePayload extends Job
{
    /**
     * @var string|array
     */
    public $payload;

    /**
     * @var string
     */
    public $precision;

    /**
     * @var string|null
     */
    public $retentionPolicy;

    /**
     * WritePayload constructor.
     *
     * @param  string|array $payload
     * @param  string $precision
     * @param  string|null $retentionPolicy
     */
    public function __construct(
        $payload,
        string $precision = Database::PRECISION_SECONDS,
        string $retentionPolicy = null
    ) {
        $this->payload = $payload;
        $this->precision = $precision;
        $this->retentionPolicy = $retentionPolicy;
    }

    /**
     * @return void
     * @throws \InfluxDB\Exception
     */
    public function handle()
    {
        InfluxDB::writePayload(
            $this->payload,
            $this->precision,
            $this->retentionPolicy
        );
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return [static::class . ':' . (is_string($this->payload) ? 1 : count($this->payload))];
    }
}
