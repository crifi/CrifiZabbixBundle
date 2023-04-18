<?php

namespace Crifi\CrifiZabbixBundle;

use Crifi\CrifiZabbixBundle\Exception\CrifiZabbixException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CrifiZabbixApi
{

  private int $requestId = 1;

  /**
   * @param string $method
   * @param array $params
   * @return mixed
   */
  public function request(string $method, array $params) {
    try {
      $client = HttpClient::create();
      $response = $client->request('POST', $_ENV['ZABBIX_URL'], [
        'json' => [
          'jsonrpc' => '2.0',
          'method' => $method,
          'params' => $params,
          'id' => $this->requestId,
        ],
        'headers' => [
          'Authorization' => sprintf('Bearer %s', $_ENV['ZABBIX_TOKEN']),
          'Content-Type' => 'application/json-rpc'
        ]
      ]);
      if (200 !== $response->getStatusCode()) {
        throw new CrifiZabbixException('Zabbix API error (HTTP-Statuscode): ' .$response->getStatusCode());
      }
    } catch (TransportExceptionInterface $e) {
      throw new CrifiZabbixException('Zabbix API error: ' . $e->getMessage());
    }
    try {
      $this->requestId++;
      $responseArray = $response->toArray();
      if (isset($responseArray['error'])) {
        throw new CrifiZabbixException(sprintf('Message: %s - Data: %s',
          $responseArray['error']['message'],
          $responseArray['error']['data']
        ));
      }
      return $responseArray['result'];
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