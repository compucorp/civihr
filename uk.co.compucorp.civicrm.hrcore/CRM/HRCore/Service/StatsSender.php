<?php

use CRM_HRCore_Service_CiviHRStatsGatherer as StatsGatherer;
use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Service_CiviHRStatisticsJSONConvertor as CiviHRStatisticsJSONConvertor;

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
   * @param CRM_Core_BAO_Cache $cache
   * @param StatsGatherer $statsGatherer
   */
  public function __construct(
    CRM_Core_BAO_Cache $cache,
    StatsGatherer $statsGatherer
  ) {
    $this->cache = $cache;
    $this->statsGatherer = $statsGatherer;
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
    // todo send to server
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

}
