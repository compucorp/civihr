<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle as SequentialTitle;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;

class CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern extends SequentialTitle {

  public static function fabricate($params = []) {
    $params = array_merge(static::getDefaultParams(), $params);

    $contactWorkPattern = ContactWorkPattern::create($params);

    return $contactWorkPattern;
  }

  private static function getDefaultParams() {
    return [
      'contact_id' => 1,
      'pattern_id' => 1,
      'effective_date' => CRM_Utils_Date::processDate(date('Y-01-01')),
    ];
  }
}
