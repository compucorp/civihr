<?php

/**
 * Class CRM_HRSampleData_Cleaner_PhotoFilesCleaner
 */
class CRM_HRSampleData_Cleaner_PhotoFilesCleaner extends CRM_HRSampleData_CSVHandler
{
  private $uploadDir;

  public function __construct() {
    $config = CRM_Core_Config::singleton();
    $this->uploadDir = $config->customFileUploadDir;
  }

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {
    if (!empty($row['image_URL'])) {
      $this->deletePhoto($row['image_URL']);
    }
  }

  /**
   * Deletes contact photo
   *
   * @param $photoName
   */
  private function deletePhoto($photoName) {
    unlink("{$this->uploadDir}/{$photoName}");
  }

}
