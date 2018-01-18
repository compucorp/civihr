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

      //When a user has no CMS account, the contact_id parameter is not present
      //we need to add it here.
      //Also when contact has CMS account, the cmsId parameter is needed for the
      //HRCore CMSData classes
      $result['cmsId'] = $result['id'];
    } catch(Exception $e) {
      //When a user has no CMS account, the contact_id parameter is not present
      //and an exception is thrown, we need to add it here.
      $result['contact_id'] = $contactID;
    }

    return $result;
  }

  /**
   * Checks whether a contact has been soft deleted or not.
   *
   * @param int $contactID
   *
   * @return bool
   */
  public static function isContactDeleted($contactID) {
    $result = civicrm_api3('Contact', 'get', array(
      'is_deleted' => 1,
      'id' => $contactID
    ));

    return $result['count'] > 0;
  }
}
