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
    try {
      $result = civicrm_api3('User', 'getsingle', [
        'contact_id' => $contactID,
      ]);

      //Also when contact has CMS account, the cmsId parameter is needed for the
      //HRCore CMSData classes
      $result['cmsId'] = $result['id'];
    } catch(Exception $e) {
      //When a user has no CMS account, the contact_id parameter is not present
      //and an exception is thrown, we need to add it here.
      $result['contact_id'] = $contactID;
      unset($result['error_message']);
    }

    return $result;
  }
}
