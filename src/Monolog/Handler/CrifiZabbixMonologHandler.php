<?php

namespace Crifi\CrifiZabbixBundle\Monolog\Handler;

use Disc\Zabbix\Sender;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class CrifiZabbixMonologHandler extends AbstractProcessingHandler
{
    private $zabbixHost;

    private $zabbixPort;

    private $zabbixTrapperHost;

    private $zabbixTrapperKey;

    public function __construct(
        string $zabbixHost,
        int $zabbixPort,
        string $zabbixTrapperHost,
        string $zabbixTrapperKey,
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->zabbixHost = $zabbixHost;
        $this->zabbixPort = $zabbixPort;
        $this->zabbixTrapperHost = $zabbixTrapperHost;
        $this->zabbixTrapperKey = $zabbixTrapperKey;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records): void
    {
        $messages = [];

        foreach ($records as $record) {
            if ($record['level'] < $this->level) {
                continue;
            }
            $messages[] = $this->processRecord($record);
        }

        if (!empty($messages)) {
            $this->send($messages);
        }
    }

    /**
     * Sends a zabbix trapper message to zabbix
     *
     * @param array $records the array of log records that formed this content
     */
    protected function send(array $records)
    {
        $sender = new Sender($this->zabbixHost, $this->zabbixPort);
        $message = (new LineFormatter())->format($this->getHighestRecord($records));
        $sender->addData($this->zabbixTrapperHost, $this->zabbixTrapperKey, $message);
        $sender->send();
    }

    /**
     * @inheritDoc
     */
    protected function write(array $record): void
    {
        $this->send([$record]);
    }

    protected function getHighestRecord(array $records): array
    {
        $highestRecord = null;
        foreach ($records as $record) {
            if (null === $highestRecord || $highestRecord['level'] < $record['level']) {
                $highestRecord = $record;
            }
        }

        return $highestRecord;
    }
}