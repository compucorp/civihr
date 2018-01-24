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
  private $logger;

  /**
   * @var HttpClient
   */
  private $httpClient;

  /**
   * @var string
   *   The default endpoint to post statistics to
   */
  private $statsEndpoint = 'https://civihr.org/civicrm/civihr-statistics';

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

    // allow override
    if (defined('CIVIHR_STATISTICS_ENDPOINT')) {
      $this->statsEndpoint = CIVIHR_STATISTICS_ENDPOINT;
    }
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
   * Send the JSON response body to the server.
   *
   * @param string $json
   */
  private function doRequest($json) {
    $response = $this->httpClient->post($this->statsEndpoint, $json);
    list($status, $responseBody) = $response;

    // Response should be empty
    if ($responseBody) {
      $status = HttpClient::STATUS_DL_ERROR;
    }

    if (HttpClient::STATUS_OK !== $status) {
      $msg = sprintf('Failed sending CiviHR stats: %s', $responseBody);
      if ($this->logger) {
        $this->logger->error($msg);
      }
      throw new \Exception($msg);
    }
  }

}
