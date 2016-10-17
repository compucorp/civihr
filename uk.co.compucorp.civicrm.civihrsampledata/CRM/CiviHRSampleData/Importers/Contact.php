<?php


/**
 * Class CRM_CiviHRSampleData_Importers_Contact
 *
 */
class CRM_CiviHRSampleData_Importers_Contact extends CRM_CiviHRSampleData_DataImporter
{

  public function __construct() {
    // Move contact images to public civicrm upload directory
    $imgDir = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.civihrsampledata') . "/resources/photos/";
    $config = CRM_Core_Config::singleton();
    $uploadDir= $config->customFileUploadDir;
    $this->recurseCopy($imgDir, $uploadDir);
  }

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `id` & `contact_type`
   */
  protected function insertRecord(array $row) {
    $currentID = $row['id'];

    // for "default organization" contact => id = 1 , keep the ID to update it instead of creating it twice
    if ($row['id'] != 1) {
      unset($row['id']);
    }

    // convert imageURL to real URL
    if (!empty($row['image_URL'])) {
      $row['image_URL'] = $this->imageToURL($row['image_URL']);
    }

    $result = $this->callAPI('Contact', 'create', $row);

    $this->setDataMapping('contact_mapping', $currentID, $result['id']);
  }

  /**
   * Convert contact image name to url
   * @param string $imageName Image file name
   * @return string
   */
  private function imageToURL($imageName) {
    return CRM_Utils_System::url('civicrm/contact/imagefile', 'photo=' . $imageName, TRUE, NULL, TRUE, TRUE);
  }

  /**
   * Copy files from one Directory to another
   * @param string $src Source Directory
   * @param string $dst Destination Directory
   */
  private function recurseCopy($src, $dst) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
      if (( $file != '.' ) && ( $file != '..' )) {
        if ( is_dir($src . '/' . $file) ) {
          recurse_copy($src . '/' . $file, $dst . '/' . $file);
        }
        else {
          copy($src . '/' . $file, $dst . '/' . $file);
        }
      }
    }
    closedir($dir);
  }

}
