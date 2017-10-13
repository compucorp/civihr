<?php

use CRM_HRCore_Test_Fabricator_Relationship as RelationshipFabricator;

trait CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait {

  public function setLeaveApproverRelationshipTypes($relationshipTypes) {
    $relationshipTypesIDs = [];
    foreach($relationshipTypes as $relationshipType) {
      $relationshipType = $this->getRelationshipType($relationshipType);
      $relationshipTypesIDs[] = $relationshipType['id'];
    }

    Civi::service('hrleaveandabsences.settings_manager')->set(
      'relationship_types_allowed_to_approve_leave',
      $relationshipTypesIDs
    );
  }

  public function setContactAsLeaveApproverOf($leaveApprover, $contact, $startDate = null, $endDate = null, $isActive = true, $relationshipType = null) {
    if(!$relationshipType) {
      $relationshipType = 'leave approver is';
    }

    $relationshipType = $this->getRelationshipType($relationshipType);

    RelationshipFabricator::fabricate([
      'contact_id_a' => $contact['id'],
      'contact_id_b' => $leaveApprover['id'],
      'relationship_type_id' => $relationshipType['id'],
      'start_date' => $startDate,
      'end_date' => $endDate,
      'is_active' => $isActive
    ]);
  }

  private function getRelationshipType($typeName) {
    $result = civicrm_api3('RelationshipType', 'get', [
      'name_a_b' => $typeName,
    ]);

    if(empty($result['values'])) {
      return $this->createRelationshipType($typeName);
    }

    return array_shift($result['values']);
  }

  private function createRelationshipType($typeName) {
    $result = civicrm_api3('RelationshipType', 'create', array(
      'sequential'     => 1,
      'name_a_b'       => $typeName,
      'name_b_a'       => $typeName,
      'contact_type_a' => 'individual',
      'contact_type_b' => 'individual',
    ));

    $relationshipType = $result['values'][0];

    $this->addRelationShipTypeAsLeaveApproverType($relationshipType['id']);

    return $relationshipType;
  }

  private function addRelationShipTypeAsLeaveApproverType($relationshipTypeID) {
    $currentSettings = Civi::service('hrleaveandabsences.settings_manager')->get(
      'relationship_types_allowed_to_approve_leave'
    );

    if(!$currentSettings) {
      $currentSettings = [];
    }

    Civi::service('hrleaveandabsences.settings_manager')->set(
      'relationship_types_allowed_to_approve_leave',
      array_merge($currentSettings, [$relationshipTypeID])
    );
  }

  public function createLeaveManagerServiceMock($isAdmin = false, $isManager = false) {
    $leaveManagerService = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Service_LeaveManager::class)
                                ->setMethods(['currentUserIsAdmin', 'currentUserIsLeaveManagerOf'])
                                ->getMock();

    $leaveManagerService->expects($this->any())
                        ->method('currentUserIsAdmin')
                        ->will($this->returnValue($isAdmin));

    $leaveManagerService->expects($this->any())
                        ->method('currentUserIsLeaveManagerOf')
                        ->will($this->returnValue($isManager));

    return $leaveManagerService;
  }
}
