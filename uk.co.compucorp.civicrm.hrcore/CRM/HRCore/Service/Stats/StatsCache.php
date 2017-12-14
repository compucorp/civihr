<?php

use CRM_HRCore_Service_Stats_StatsGatherer as StatsGatherer;
use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Service_FileCache as FileCache;

class CRM_HRCore_Service_Stats_StatsCache {

  const CACHE_KEY = 'civihr_stats_cache';

  /**
   * @var StatsGatherer
   */
  protected $gatherer;

  /**
   * @var FileCache
   */
  protected $cache;

  /**
   * @param StatsGatherer $gatherer
   * @param FileCache $cache
   */
  public function __construct(StatsGatherer $gatherer, FileCache $cache) {
    $this->gatherer = $gatherer;
    $this->cache = $cache;
  }

  /**
   * @return CiviHRStatistics
   */
  public function fetchCurrent() {
    $cachedStats = $this->cache->get(self::CACHE_KEY);
    $modified = $this->cache->getModified(self::CACHE_KEY);

    if (NULL === $cachedStats || $this->isExpired($modified)) {
      $cachedStats = $this->gatherer->gather();
      $this->cache->set(self::CACHE_KEY, $cachedStats);
    }

    return $cachedStats;
  }

  /**
   * Check if a cache was generated a week ago or more.
   *
   * @param \DateTime|null $modified
   *
   * @return bool
   */
  private function isExpired($modified) {
    if (NULL === $modified) {
      return FALSE;
    }

    $oneWeekAgo = new \DateTime('now - 1 week 00:00:00');

    return $oneWeekAgo > $modified;
  }
}
