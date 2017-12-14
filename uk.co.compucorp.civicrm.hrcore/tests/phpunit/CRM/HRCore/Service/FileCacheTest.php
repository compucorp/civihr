<?php

use CRM_HRCore_Service_FileCache as FileCache;
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;

/**
 * @group headless
 */
class FileCacheTest extends BaseHeadlessTest {

  public function testNonExistantKeyWillReturnNull() {
    $cache = new FileCache();
    $this->assertNull($cache->get('idonotexist'));
  }

  public function testRemoveWillNullifyCache() {
    $cache = new FileCache();
    $cache->set('foo', 'bar');
    $cache->remove('foo');
    $this->assertNull($cache->get('foo'));
  }

  public function testCacheWillReturnDataWithSameKey() {
    $cache = new FileCache();
    $cache->set('foo', 'bar');
    $this->assertEquals('bar', $cache->get('foo'));
    $cache->remove('foo');
  }

  public function testSetOnExistingCacheWillReplaceIt() {
    $cache = new FileCache();
    $cache->set('foo', 'bar');
    $cache->set('foo', 'baaar');
    $this->assertEquals('baaar', $cache->get('foo'));
    $cache->remove('foo');
  }

  public function testModifiedDateForEmptyCacheWillBeNull() {
    $cache = new FileCache();
    $this->assertNull($cache->getModified('idonotexist'));
  }

  public function testModifiedDateWillBeNowForNewCache() {
    $cache = new FileCache();
    $cache->set('foo', 'bar');
    $modified = $cache->getModified('foo');
    $now = new \DateTime();

    $this->assertTrue($this->datesWithinSecondsOfEachOther($modified, $now, 2));
  }

  /**
   * Checks whether two dates are within the provided number of seconds from
   * each other
   *
   * @param DateTime $first
   * @param DateTime $second
   * @param int $seconds
   * @return bool
   */
  private function datesWithinSecondsOfEachOther(
    \DateTime $first,
    \DateTime $second,
    $seconds
  ) {
    return abs($first->getTimestamp() - $second->getTimestamp()) <= $seconds;
  }
}
