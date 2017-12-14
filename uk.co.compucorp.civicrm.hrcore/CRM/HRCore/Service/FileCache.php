<?php

class CRM_HRCore_Service_FileCache {

  /**
   * @var string
   *   The directory to store the cache files
   */
  protected $cacheDir;

  public function __construct() {
   $this->cacheDir = Civi::paths()->getPath('ext/civihr/filecache');

   if (!file_exists($this->cacheDir)) {
     if (!mkdir($this->cacheDir, 0777, TRUE)) {
       $err = sprintf('Could not create cache dir "%s"', $this->cacheDir);
       throw new \Exception($err);
     }
   }
  }

  /**
   * @param $key
   * @return null|string
   */
  public function get($key) {
    $filename = $this->getCacheFilePath($key);
    if (is_file($filename) && is_readable($filename)) {
      return file_get_contents($filename);
    }

    return NULL;
  }

  /**
   * @param string $key
   * @param mixed $data
   */
  public function set($key, $data) {
    $filename = $this->getCacheFilePath($key);
    $isDir = file_exists($filename) && is_dir($filename);
    if (file_exists($filename)) {
      $isWritable = is_writable($filename);
    } else {
      $isWritable = is_writable(dirname($filename));
    }

    if ($isDir) {
      throw new \Exception('Cache file is a directory');
    } elseif (!$isWritable) {
      throw new \Exception(sprintf('Cannot write to %s', $filename));
    }

    if (FALSE === file_put_contents($filename, $data)) {
      throw new \Exception(sprintf('Failed writing to "%s"', $filename));
    }
  }

  /**
   * @param $key
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
   * @param $key
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
   * @param $key
   * @return string
   */
  private function getCacheFilePath($key) {
    return $this->cacheDir . DIRECTORY_SEPARATOR . $key;
  }
}
