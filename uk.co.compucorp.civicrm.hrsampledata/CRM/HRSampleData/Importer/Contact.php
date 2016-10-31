<?php

/**
 * Class CRM_HRSampleData_Importer_Contact
 *
 */
class CRM_HRSampleData_Importer_Contact extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `id` & `contact_type`
   */
  protected function operate(array $row) {
    $currentID = $row['id'];

    // for "default organization" contact => id = 1 , keep the ID to update it instead of creating it twice
    if ($row['id'] != 1) {
      unset($row['id']);
    }

    if (!empty($row['image_URL'])) {
      $row['image_URL'] = $this->imageToURL($row['image_URL']);
    }

    $result = $this->callAPI('Contact', 'create', $row);

    $this->setDataMapping('contact_mapping', $currentID, $result['id']);
  }

  /**
   * Converts contact image to url
   *
   * @param string $imageName
   *   Image file name
   *
   * @return string
   */
  private function imageToURL($imageName) {
    return CRM_Utils_System::url('civicrm/contact/imagefile', 'photo=' . $imageName, TRUE, NULL, TRUE, TRUE);
  }

}
