<?php

trait CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait {

  /**
   * Returns a list of relationship types stored on the
   * 'relationship_types_allowed_to_approve_leave' setting.
   *
   * Basically, this is just a helper method that gets this information from
   * the Settings Manager service.
   *
   * @return array
   */
  public function getLeaveApproverRelationshipsTypes() {
    return Civi::service('hrleaveandabsences.settings_manager')->get('relationship_types_allowed_to_approve_leave');
  }

  /**
   * Returns a list of of relationship types IDs, stored on the
   * 'relationship_types_allowed_to_approve_leave' setting.
   *
   * This methods is guaranteed to return an array that can be used to
   * build a WHERE IN() query, since it always return a non empty array. If
   * there's no relationship type in settings, it will return a array with a
   * single invalid ID (-1), which will make the query return nothing. The idea
   * is that, if no relationship is set, then the leave approver should not be
   * able to see anything.
   */
  public function getLeaveApproverRelationshipsTypesForWhereIn() {
    $relationshipTypes = $this->getLeaveApproverRelationshipsTypes();
    $relationshipTypes[] = -1;

    return $relationshipTypes;
  }
}
