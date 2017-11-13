<?php

trait CRM_HRLeaveAndAbsences_WorkDayHelpersTrait {

  use CRM_HRLeaveAndAbsences_OptionGroupHelpersTrait;

  protected function getWorkDayTypes() {
    $optionValues = $this->getWorkDayTypesFromXML();
    $workDayTypes = [];
    foreach($optionValues as $optionValue) {
      $workDayTypes[$optionValue['name']] = $optionValue;
    }

    return $workDayTypes;
  }

}
