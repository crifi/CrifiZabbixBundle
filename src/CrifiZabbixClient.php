<?php

namespace Crifi\CrifiZabbixBundle;

final class CrifiZabbixClient
{
    /**
     * Connects to Zabbix API and returns an API object
     *
     * @return CrifiZabbixApi
     */
    public static function create()
    {
        if (!isset($_ENV['ZABBIX_URL']) || !(isset($_ENV['ZABBIX_TOKEN']))) {
            throw new \InvalidArgumentException('Some zabbix environment variables are missing.');
        }
        return new CrifiZabbixApi();
    }
}