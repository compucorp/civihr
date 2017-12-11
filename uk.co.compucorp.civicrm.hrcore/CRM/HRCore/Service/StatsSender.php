<?php

use CRM_HRCore_Service_CiviHRStatsGatherer as StatsGatherer;
use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Service_CiviHRStatisticsJSONConvertor as CiviHRStatisticsJSONConvertor;
use Psr\Log\LoggerInterface;

class CRM_HRCore_Service_StatsSender {

  const CACHE_GROUP = 'uk.co.compucorp.civicrm.hrcore';
  const CACHE_PATH = 'civihr_stats_cache';

  /**
   * @var CRM_Core_BAO_Cache
   */
  protected $cache;

  /**
   * @var StatsGatherer
   */
  protected $statsGatherer;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @param CRM_Core_BAO_Cache $cache
   * @param StatsGatherer $statsGatherer
   * @param LoggerInterface $logger
   */
  public function __construct(
    CRM_Core_BAO_Cache $cache,
    StatsGatherer $statsGatherer,
    LoggerInterface $logger
  ) {
    $this->cache = $cache;
    $this->statsGatherer = $statsGatherer;
    $this->logger = $logger;
  }

  /**
   * Send CiviHR statistics to the listening stats server.
   */
  public function send() {
    $cache = $this->cache;
    /** @var CiviHRStatistics $cachedStats */
    $cachedStats = $cache::getItem(self::CACHE_GROUP, self::CACHE_PATH);

    if (NULL === $cachedStats || $this->isExpired($cachedStats)) {
      $cachedStats = $this->statsGatherer->gather();
      $cache::setItem($cachedStats, self::CACHE_GROUP, self::CACHE_PATH);
    }

    $json = CiviHRStatisticsJSONConvertor::toJson($cachedStats);
    $ch = $this->prepareRequest($json);
    $response = curl_exec($ch);

    // todo error checking on real response
    if (!$response) {
      $msg = sprintf('Failed sending CiviHR stats: %s', curl_error($ch));
      $this->logger->error($msg);
      throw new \Exception($msg);
    }

    // Log successful sending
    $siteName = $cachedStats->getSiteName();
    $nowFmt = (new \DateTime())->format(\DateTime::ISO8601);
    $msg = sprintf('Successfully sent stats from %s at %s', $siteName, $nowFmt);
    $this->logger->info($msg);
  }

  /**
   * Check if a cache was generated a week ago or more.
   *
   * @param CiviHRStatistics $cachedStats
   *
   * @return bool
   */
  private function isExpired(CiviHRStatistics $cachedStats) {
    $oneWeekAgo = new \DateTime('now - 1 week 00:00:00');

    return $oneWeekAgo > $cachedStats->getGenerationDate();
  }

  /**
   * @param $json
   *
   * @return resource
   */
  private function prepareRequest($json) {
    $headers = [
      'Content-Type: application/json',
      'Content-Length: ' . strlen($json)
    ];

    $ch = curl_init('http://localhost:8000');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    return $ch;
  }

}
