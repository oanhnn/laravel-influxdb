<?php

namespace Laravel\InfluxDB\Log;

use InfluxDB\Point;
use Laravel\InfluxDB\Facades\InfluxDB;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class Handler
 *
 * @package     Laravel\InfluxDB\Log
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class Handler extends AbstractProcessingHandler
{
    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     * @throws \Exception
     */
    protected function write(array $record): void
    {
        $this->send([$record]);
    }

    /**
     * Handles a set of records at once.
     *
     * @param  array $records The records to handle (an array of record arrays)
     * @return void
     * @throws \Exception
     */
    public function handleBatch(array $records): void
    {
        foreach ($records as &$record) {
            if (!$this->isHandling($record)) {
                continue;
            }

            $record = $this->processRecord($record);
            $record['formatted'] = $this->getFormatter()->format($record);
        }

        $this->send($records);
    }

    /**
     * @param  array $records
     * @return bool
     * @throws \Exception
     */
    protected function send(array $records): bool
    {
        $points = collect($records)
            ->filter(function ($item) {
                return isset($item['formatted']['point']) && ($item['formatted']['point'] instanceof Point);
            })
            ->map(function ($item) {
                return $item['formatted']['point'];
            });

        if (!$points->count()) {
            return true;
        }

        try {
            return InfluxDB::writePoints($points->toArray());
        } catch (\Exception $exception) {
            if (!config('influxdb.logging.ignore_error')) {
                throw $exception;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new Formatter(config('influxdb.logging.datetime_format'));
    }
}
