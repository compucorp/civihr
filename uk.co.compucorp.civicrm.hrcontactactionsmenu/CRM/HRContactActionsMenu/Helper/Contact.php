<?php

/**
 * Class CRM_HRContactActionsMenu_Helper_Contact
 */
class CRM_HRContactActionsMenu_Helper_Contact {

  /**
   * Gets the CMS user information for a Civicrm contact
   *
   * @param int $contactID
   *
   * @return array
   */
  public static function getUserInformation($contactID) {
    $output = [];
    $output['contact_id'] = $contactID;

    try {
      $result = civicrm_api3('User', 'getsingle', [
        'contact_id' => $contactID,
      ]);

      //When a contact has CMS account, the cmsId parameter is needed for the
      //HRCore CMSData classes
      $output['cmsId'] = $result['id'];
    } catch(Exception $e) {}

    return $output;
  }
}
