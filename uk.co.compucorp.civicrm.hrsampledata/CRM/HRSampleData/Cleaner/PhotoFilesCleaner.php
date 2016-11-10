<?php

/**
 * Class CRM_HRSampleData_Cleaner_PhotoFilesCleaner
 */
class CRM_HRSampleData_Cleaner_PhotoFilesCleaner extends CRM_HRSampleData_CSVCleanerVisitor
{
  private $uploadDir;

  public function __construct() {
    $config = CRM_Core_Config::singleton();
    $this->uploadDir = $config->customFileUploadDir;
  }

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    if (!empty($row['image_URL'])) {
      $this->deletePhoto($row['image_URL']);
    }
  }

  /**
   * Deletes contact photo
   *
   * @param string $photoName
   */
  private function deletePhoto($photoName) {
    $photoPath = "{$this->uploadDir}/{$photoName}";

    if(file_exists($photoPath)){
      unlink($photoPath);
    }
  }

}
