<?php

use CRM_HRCore_Test_Fabricator_Relationship as RelationshipFabricator;

trait CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait {

  public function setContactAsLeaveApproverOf($leaveApprover, $contact, $startDate = null, $endDate = null, $isActive = true) {
    $relationshipType = $this->getLeaveApproverRelationshipType();

    RelationshipFabricator::fabricate([
      'contact_id_a' => $contact['id'],
      'contact_id_b' => $leaveApprover['id'],
      'relationship_type_id' => $relationshipType['id'],
      'start_date' => $startDate,
      'end_date' => $endDate,
      'is_active' => $isActive
    ]);
  }

  private function getLeaveApproverRelationshipType() {
    $result = civicrm_api3('RelationshipType', 'get', [
      'name_a_b' => 'has Leave Approved by',
    ]);

    if(empty($result['values'])) {
      return $this->createLeaveApproverRelationshipType();
    }

    return array_shift($result['values']);
  }

  private function createLeaveApproverRelationshipType() {
    $result = civicrm_api3('RelationshipType', 'create', array(
      'sequential'     => 1,
      'name_a_b'       => 'has Leave Approved by',
      'name_b_a'       => 'is Leave Approver of',
      'contact_type_a' => 'individual',
      'contact_type_b' => 'individual',
    ));

    return $result['values'][0];
  }
}
