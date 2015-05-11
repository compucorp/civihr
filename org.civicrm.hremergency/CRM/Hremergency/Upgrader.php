<?php

/**
 * Collection of upgrade steps
 */
class CRM_Hremergency_Upgrader extends CRM_Hremergency_Upgrader_Base {

  /**
   * Change the custom_group ID to 99999 as we have this exported through Drupal webforms so the ID needs to be always the same
   */
  public function enable() {
      
    // Disable check for foregin keys
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0');
    
    $params = array(
        'version' => 3,
        'name' => 'Emergency_Contacts'
    );
    
    // Get the ID for the Emergency Contacts custom group
    $emergency_contact_group = civicrm_api("CustomGroup", "Get", $params);
    
    $name_id = '100000';
    $phone_id = '100001';
    $email_id = '100002';
    $relationship_select_id = '100003';
    $notes_id = '100004';
    
    // Whatever ID is created update it to 99999 to prevent issues with the exported webforms on the Drupal side with the hardcoded IDs
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_group SET id = 99999 WHERE id = ' . $emergency_contact_group['id'] . ';');
    
    // Set the custom field value IDs to our custom one
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_field SET id = ' . $name_id . ' WHERE name = "Name" AND custom_group_id = ' . $emergency_contact_group['id'] . ';');
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_field SET id = ' . $phone_id . ' WHERE name = "Phone_number" AND custom_group_id = ' . $emergency_contact_group['id'] . ';');
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_field SET id = ' . $email_id . ' WHERE name = "Email" AND custom_group_id = ' . $emergency_contact_group['id'] . ';');
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_field SET id = ' . $relationship_select_id . ' WHERE name = "Relationship_with_Employee" AND custom_group_id = ' . $emergency_contact_group['id'] . ';');
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_field SET id = ' . $notes_id . ' WHERE name = "Notes" AND custom_group_id = ' . $emergency_contact_group['id'] . ';');
    
    // Set the group ID to our custom one
    CRM_Core_DAO::executeQuery('UPDATE civicrm_custom_field SET custom_group_id = 99999 WHERE custom_group_id = ' . $emergency_contact_group['id'] . ';');
    
    // Enable foreign key checks
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1');
    
  }
}
