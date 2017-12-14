<?php

use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Service_Stats_StatsJSONConvertor as StatsJSONConvertor;
use Psr\Log\LoggerInterface;
use CRM_Utils_HttpClient as HttpClient;

/**
 * Responsible for taking a stats object and sending it to the stats server.
 */
class CRM_HRCore_Service_Stats_StatsSender {

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @var HttpClient
   */
  private $httpClient;

  /**
   * @param HttpClient $httpClient
   * @param LoggerInterface $logger
   */
  public function __construct(
    HttpClient $httpClient,
    LoggerInterface $logger = NULL
  ) {
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * Send CiviHR statistics to the listening stats server.
   *
   * @param CiviHRStatistics $stats
   */
  public function send(CiviHRStatistics $stats) {

    $json = StatsJSONConvertor::toJson($stats);
    $this->doRequest($json);

    // Log successful sending
    $siteName = $stats->getSiteName();
    $nowFmt = (new \DateTime())->format(\DateTime::ISO8601);
    $msg = sprintf('Successfully sent stats from %s at %s', $siteName, $nowFmt);

    if ($this->logger) {
      $this->logger->info($msg);
    }
  }

  /**
   * @param $json
   */
  private function doRequest($json) {
    $headers = [
      'Content-Type: application/json',
      'Content-Length: ' . strlen($json)
    ];

    $endpoint = 'http://localhost:8000';
    if (defined('CIVIHR_STATISTICS_ENDPOINT')) {
      $endpoint = CIVIHR_STATISTICS_ENDPOINT;
    }

    $response = $this->httpClient->post($endpoint, $json);
    list ($status, $response) = $response;

    // todo other checks are probably required on response
    if (HttpClient::STATUS_OK !== $status) {
      $msg = sprintf('Failed sending CiviHR stats: %s', $response);
      if ($this->logger) {
        $this->logger->error($msg);
      }
      throw new \Exception($msg);
    }
  }

}
