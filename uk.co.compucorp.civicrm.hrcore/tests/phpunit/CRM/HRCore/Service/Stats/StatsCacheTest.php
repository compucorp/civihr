<?php

use CRM_HRCore_Service_Stats_StatsCache as StatsCache;
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Service_Stats_StatsGatherer as StatsGatherer;
use CRM_HRCore_Service_FileCache as FileCache;
use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use Prophecy\Argument;

class StatsCacheTest extends BaseHeadlessTest {

  public function testWillFetchFreshIfNoneExist() {
    $gatherer = $this->prophesize(StatsGatherer::class);
    $cache = $this->prophesize(FileCache::class);
    $gatherer->gather()->shouldBeCalled();
    $statsCache = new StatsCache($gatherer->reveal(), $cache->reveal());
    $statsCache->fetchCurrent();
  }

  public function testWillFetchFreshIfCacheIsExpired() {
    $gatherer = $this->prophesize(StatsGatherer::class);
    $cache = $this->prophesize(FileCache::class);
    $oneSecond = new \DateInterval('PT1S');
    $oneWeekAgo = new \DateTime('midnight today - 7 days');
    $oneWeekAndOneSecondAgo = $oneWeekAgo->sub($oneSecond);
    $cache->getModified(StatsCache::CACHE_KEY)->willReturn($oneWeekAndOneSecondAgo);
    $cache->get(StatsCache::CACHE_KEY)->willReturn(new CiviHRStatistics());
    $cache->set(StatsCache::CACHE_KEY, Argument::any())->shouldBeCalled();
    $gatherer->gather()->shouldBeCalled();
    $statsCache = new StatsCache($gatherer->reveal(), $cache->reveal());
    $statsCache->fetchCurrent();
  }

  public function testWillNotFetchIfCacheIsFresh() {
    $gatherer = $this->prophesize(StatsGatherer::class);
    $cache = $this->prophesize(FileCache::class);
    $oneWeekAgo = new \DateTime('midnight today - 7 days');
    $cache->getModified(StatsCache::CACHE_KEY)->willReturn($oneWeekAgo);
    $cache->get(StatsCache::CACHE_KEY)->willReturn(new CiviHRStatistics());
    $gatherer->gather()->shouldNotBeCalled();
    $statsCache = new StatsCache($gatherer->reveal(), $cache->reveal());
    $statsCache->fetchCurrent();
  }
}
