<?php

/**
 * Class CRM_Contactaccessrights_Helper_Contact
 */
class CRM_Contactaccessrights_Helper_ContactActionsMenu_Contact {

  /**
   * Gets the ACL Groups that the contact belongs to.
   *
   * @param int $contactID
   *
   * @return array
   */
  public static function getACLGroups($contactID) {
    $result = civicrm_api3('GroupContact', 'get', [
      'sequential' => 1,
      'contact_id' => $contactID,
      'api.Group.get' => ['id' => "\$value.group_id", 'sequential' => 1],
    ]);

    $aclGroups = [];

    if ($result['count'] < 0) {
      return $aclGroups;
    }

    $accessControlValue = self::getAccessControlOptionValue();
    foreach($result['values'] as $group) {
      $group = $group['api.Group.get']['values'][0];
      if (in_array($accessControlValue, $group['group_type'])) {
        $aclGroups[$group['id']] = $group['title'];
      }
    }

    return $aclGroups;
  }

  /**
   * Returns the Option Value of the Access Control
   * Group Type option value.
   *
   * @return string
   */
  private static function getAccessControlOptionValue() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'group_type',
      'name' => 'Access Control',
    ]);

    return $result['values'][0]['value'];
  }
}
