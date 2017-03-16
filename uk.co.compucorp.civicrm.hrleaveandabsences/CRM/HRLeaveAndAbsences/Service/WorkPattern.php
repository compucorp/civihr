<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;

/**
 * Class CRM_HRLeaveAndAbsences_Service_WorkPattern
 */
class CRM_HRLeaveAndAbsences_Service_WorkPattern {

  /**
   * Checks If a work pattern has ever been used by a contact
   *
   * @param int $workPatternID
   *
   * @return boolean
   */
  public function workPatternHasEverBeenUsed($workPatternID) {
    if ($this->isDefaultWorkPattern($workPatternID) ||
      $this->workPatternIsLinkedToAContact($workPatternID))
    {
      return true;
    }

    return false;
  }

  /**
   * Deletes the WorkPattern with the given id.
   * Checks first to see if the work pattern can be deleted or not.
   *
   * @param int $workPatternID
   */
  public function delete($workPatternID) {
    if ($this->workPatternHasEverBeenUsed($workPatternID)) {
      throw new UnexpectedValueException('Work pattern cannot be deleted because it is used by one or more contacts');
    }

    WorkPattern::del($workPatternID);
  }

  /**
   * Checks if the work pattern is the default work pattern
   *
   * @param int $workPatternID
   *
   * @return boolean
   */
  private function isDefaultWorkPattern($workPatternID) {
    $workPattern = WorkPattern::findById($workPatternID);

    if ($workPattern->is_default) {
      return true;
    }

    return false;
  }

  /**
   * Checks if the work pattern is linked to at least one contact work pattern
   *
   * @param int $workPatternID
   *
   * @return boolean
   */
  private function workPatternIsLinkedToAContact($workPatternID) {
    $contactWorkPattern = new ContactWorkPattern();
    $contactWorkPattern->pattern_id = $workPatternID;
    $contactWorkPattern->find();

    if ($contactWorkPattern->N > 0) {
      return true;
    }

    return false;
  }
}
