<?php

/**
 * Upgrader to change values installed / configured on enablement of
 * 'CiviReport' and 'CiviCase' extensions.
 */
trait CRM_HRUI_Upgrader_Steps_4703 {

  /**
   * Deletes relationship types that do not make sense for a CiviHR installation.
   *
   * @return bool
   *   True on success, false otherwise
   */
  public function upgrade_4703() {
    $relationshipsToDelete = [
      'Benefits Specialist',
      'Parent of',
      'Health Services Coordinator',
      'Homeless Services Coordinator',
      'Senior Services Coordinator',
      'Sibling of',
      'Supervisor',
      'Volunteer is',
      'Spouse of',
      'Partner of'
    ];

    $params = [
      'name_b_a' => ['IN' => $relationshipsToDelete],
      'api.RelationshipType.delete' => ['id' => '$value.id']
    ];

    civicrm_api3('RelationshipType', 'get', $params);

    return TRUE;
  }
}
