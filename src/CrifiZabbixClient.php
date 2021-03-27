<?php

namespace Crifi\CrifiZabbixBundle;

use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

final class CrifiZabbixClient
{
    /**
     * Connects to Zabbix API and returns a API object
     *
     * @return CrifiZabbixApi
     */
    public static function create()
    {
        if (!isset($_ENV['ZABBIX_URL']) || !(isset($_ENV['ZABBIX_USER']) || !isset($_ENV['ZABBIX_PASSWORD']))) {
            throw new InvalidArgumentException('Some zabbix environment variables are missing.');
        }
        return new CrifiZabbixApi();
    }
}