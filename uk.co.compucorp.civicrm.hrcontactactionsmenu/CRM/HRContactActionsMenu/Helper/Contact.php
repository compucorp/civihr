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
    $result = civicrm_api3('User', 'getsingle', [
      'contact_id' => $contactID,
    ]);

    //When a user has no CMS account, the contact_id parameter is not present
    //we need to add it here.
    //Also when contact has CMS account, the cmsId parameter is needed for the
    //HRCore CMSData classes
    if(empty($result['id'])) {
      $result['contact_id'] = $contactID;
    }
    else{
      $result['cmsId'] =$result['id'];
    }

    return $result;
  }
}
