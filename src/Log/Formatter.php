<?php

namespace Laravel\InfluxDB\Log;

use Carbon\Carbon;
use InfluxDB\Point;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;

/**
 * Class Formatter
 *
 * @package     Laravel\InfluxDB\Log
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class Formatter extends NormalizerFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record = parent::format($record);

        return $this->prepareMessage($record);
    }

    /**
     * @param  array $record
     * @return array
     * @throws \InfluxDB\Database\Exception
     */
    protected function prepareMessage(array $record): array
    {
        $tags = $this->prepareTags($record);
        $message = [
            'name' => 'Error',
            'value' => 1,
            'timestamp' => Carbon::now()->getTimestamp(),
        ];

        if (count($tags)) {
            foreach ($tags as $key => $value) {
                if (is_numeric($value)) {
                    $message['fields'][$key] = (int)$value;
                }
            }
            $message['tags'] = $tags;
        }
        if (isset($message['fields']['Debug']['message'])) {
            $message['fields']['Debug']['message'] = $this->trimLines($message['fields']['Debug']['message']);
        }

        // Prepare Point object
        $message['point'] = new Point(
            $message['name'],
            $message['value'],
            $message['tags'] ?? [],
            $message['fields'] ?? [],
            $message['timestamp']
        );

        return $message;
    }

    /**
     * @param  array $record
     * @return array
     */
    protected function prepareTags(array $record): array
    {
        $tags = [];
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $tags['serverName'] = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($record['level'])) {
            $tags['severity'] = $this->rfc5424ToSeverity($record['level']);
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $tags['endpoint_url'] = $_SERVER['REQUEST_URI'];
        }
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $tags['method'] = $_SERVER['REQUEST_METHOD'];
        }
        if (isset($record['context']['user_id'])) {
            $tags['user_id'] = $record['context']['user_id'];
        }
        if (isset($record['context']['project_id'])) {
            $tags['project_id'] = $record['context']['project_id'];
        }
        if (isset($record['context']['file'])) {
            $tags['file'] = $this->replaceDigitData($record['context']['file']);
        }
        if (isset($record['context']['event']['api_stats'][0])) {
            foreach ($record['context']['event']['api_stats'][0] as $key => $value) {
                if (is_string($value) || is_int($value)) {
                    $tags[$key] = $value;
                }
            }
        }

        return $tags;
    }

    /**
     * @param int $level
     * @return mixed
     */
    private function rfc5424ToSeverity(int $level)
    {
        $levels = [
            Logger::DEBUG => 'Debugging',
            Logger::INFO => 'Informational',
            Logger::NOTICE => 'Notice',
            Logger::WARNING => 'Warning',
            Logger::ERROR => 'Error',
            Logger::CRITICAL => 'Critical',
            Logger::ALERT => 'Alert',
            Logger::EMERGENCY => 'Emergency',
        ];

        $result = $levels[$level] ?? $levels[Logger::EMERGENCY];

        return $result;
    }

    /**
     * @param  string $string
     * @return string
     */
    private function replaceDigitData(string $string): string
    {
        $string = preg_replace('~\/[0-9]+~', '/*', $string);
        $string = preg_replace('~\=[0-9]+~', '=*', $string);

        return $string;
    }

    /**
     * @param  string $message
     * @return string
     */
    private function trimLines(string $message): string
    {
        $limit = intval(config('influxdb.logging.limit', 5));
        if ($limit) {
            $messageArray = explode(PHP_EOL, $message);
            if ($limit < count($messageArray)) {
                $message = implode(PHP_EOL, array_slice($messageArray, 0, $limit));
            }
        }

        return $message;
    }
}
