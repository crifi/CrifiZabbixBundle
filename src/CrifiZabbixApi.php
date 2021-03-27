<?php

namespace Crifi\CrifiZabbixBundle;

use Crifi\CrifiZabbixBundle\Exception\CrifiZabbixException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CrifiZabbixApi
{

    /**
     * @var string|null
     */
    private $authtoken = null;

    /**
     * @var int
     */
    private $requestId = 1;

    public function __construct()
    {
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Connects to the zabbix api and sets the authtoken
     */
    private function connect() {
        if ($this->authtoken === null) {
            $this->authtoken = $this->request('user.login', [
                'user' => $_ENV['ZABBIX_USER'],
                'password' => $_ENV['ZABBIX_PASSWORD'],
            ]);
        }
    }

    private function disconnect() {
        $this->request('user.logout', []);
    }

    /**
     * @param string $method
     * @param array $params
     * @return array
     */
    public function request(string $method, array $params): array {
        try {
            $response = HttpClient::create()->request('POST', $_ENV['ZABBIX_URL'], [
                'json' => [
                    'jsonrpc' => '2.0',
                    'method' => $method,
                    'params' => $params,
                    'id' => $this->requestId,
                    'auth' => $this->authtoken,
                ],
            ]);
            if (200 !== $response->getStatusCode()) {
                throw new CrifiZabbixException('Zabbix API error (HTTP-Statuscode): ' .$response->getStatusCode());
            }
        } catch (TransportExceptionInterface $e) {
            throw new CrifiZabbixException('Zabbix API error: ' . $e->getMessage());
        }
        try {
            $this->requestId++;
            return $response->toArray()['result'];
        }
        catch (
            ClientExceptionInterface |
            DecodingExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            throw new CrifiZabbixException('Zabbix API error: ' . $e->getMessage());
        }
    }
}