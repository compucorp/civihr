<?php

/**
 * Responsible for storing a persistent file cache. Serializes all data to be
 * stored and returns the unserialized variable.
 */
class CRM_HRCore_Service_FileCache {

  /**
   * @var string
   *   The directory to store the cache files
   */
  protected $cacheDir;

  public function __construct() {
    $this->cacheDir = Civi::paths()->getPath('ext/civihr/filecache');

    if (!file_exists($this->cacheDir)) {
      $this->createCacheDir();
    }
  }

  /**
   * Fetches the cache entry, returns it (unserialized) if it exists, or NULL if
   * nothing was found for that key.
   *
   * @param $key
   *
   * @return mixed
   */
  public function get($key) {
    $filename = $this->getCacheFilePath($key);
    if (is_file($filename) && is_readable($filename)) {
      return unserialize(file_get_contents($filename));
    }

    return NULL;
  }

  /**
   * Takes any object or scalar value, serializes it and stores it in the cache
   *
   * @param string $key
   * @param mixed $data
   */
  public function set($key, $data) {
    $data = serialize($data);
    $filename = $this->getCacheFilePath($key);
    $isDir = file_exists($filename) && is_dir($filename);
    if (file_exists($filename)) {
      $isWritable = is_writable($filename);
    }
    else {
      $isWritable = is_writable(dirname($filename));
    }

    if ($isDir) {
      throw new \Exception('Cache file is a directory');
    }
    elseif (!$isWritable) {
      throw new \Exception(sprintf('Cannot write to %s', $filename));
    }

    if (FALSE === file_put_contents($filename, $data)) {
      throw new \Exception(sprintf('Failed writing to "%s"', $filename));
    }
  }

  /**
   * Deletes the cache file for a certain key
   *
   * @param string $key
   */
  public function remove($key) {
    $filename = $this->getCacheFilePath($key);
    if (file_exists($filename) && !is_dir($filename)) {
      if (FALSE === unlink($filename)) {
        throw new \Exception(sprintf('Failed to delete "%s"', $filename));
      }
    }
  }

  /**
   * Gets the modified date for a cache entry. Returns NULL if cache doesn't
   * exist
   *
   * @param string $key
   * @return DateTime|null
   */
  public function getModified($key) {
    $filename = $this->getCacheFilePath($key);
    if (!is_file($filename) || !is_readable($filename)) {
      return NULL;
    }

    $timeStamp = filemtime($filename);
    $modified = new \DateTime();
    $modified->setTimestamp($timeStamp);

    return $modified;
  }

  /**
   * Fetches the path to a cache file based on its key.
   *
   * @param string $key
   * @return string
   */
  private function getCacheFilePath($key) {
    return $this->cacheDir . DIRECTORY_SEPARATOR . $key;
  }

  /**
   * Create the cache directory recursively. Throws an exception if it cannot.
   */
  private function createCacheDir() {
    if (!mkdir($this->cacheDir, 0777, TRUE)) {
      $err = sprintf('Could not create cache dir "%s"', $this->cacheDir);
      throw new \Exception($err);
    }
  }

}
