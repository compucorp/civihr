<?php

use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;

trait CRM_HRLeaveAndAbsences_WorkPatternHelpersTrait {

  public function getWorkDayTypeOptionsArray() {
    return array_flip(WorkDay::buildOptions('type', 'validate'));
  }

}
