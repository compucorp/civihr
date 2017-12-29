<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HrJobRolesTestTrait {

  /**
   * Creates sample option group and values to be used in tests
   *
   */
  public function createSampleOptionGroupsAndValues()  {

    // Create required Option Groups
    $optionGroupsValuesList = [
      'hrjc_location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'cost_centres' => 'abdali'
    ];

    $optionGroupsList = array_keys($optionGroupsValuesList);
    $INList = implode("','", $optionGroupsList );

    CRM_Core_DAO::executeQuery(
      "UPDATE civicrm_option_group SET is_active = 1
       WHERE name IN ('$INList')"
    );

    $query = "SELECT id,name FROM civicrm_option_group WHERE
              name IN ('$INList')";
    $optionGroups = CRM_Core_DAO::executeQuery($query);

    $existingGroups = [];
    while($optionGroups->fetch())  {
      $existingGroups[$optionGroups->id] = $optionGroups->name;
    }

    $newGroups = [];
    foreach($optionGroupsList as $neededGroup) {
      if(array_search($neededGroup, $existingGroups) === FALSE)  {
        $params = ['name' => $neededGroup, 'is_active' => 1];
        $newGroup = CRM_Core_BAO_OptionGroup::add($params);
        $newGroups[$newGroup->id] = $newGroup->name;
      }
    }

    $finalGroupList = $existingGroups + $newGroups;
    // Create sample option values
    foreach ($optionGroupsValuesList as $group => $value)  {
      $groupID = array_search($group, $finalGroupList);
      $params = ['option_group_id' => $groupID, 'name' => $value, 'value' => $value ];
      CRM_Core_BAO_OptionValue::create($params);
    }

  }

  /**
   * Creates a new Job Contract for the given contact
   *
   * If a startDate is given, it will also create a JobDetails instance to save
   * the contract's start date and end date(if given)
   *
   * @param $contactID
   * @param null $startDate
   * @param null $endDate
   * @param array $extraParams
   *
   * @return \CRM_HRJob_DAO_HRJobContract|NULL
   */
  protected function createJobContract($contactID, $startDate = null, $endDate = null, $extraParams = array()) {
//    $detailsParams = null;
//    if($startDate) {
//      $detailsParams = [
//        'period_start_date' => CRM_Utils_Date::processDate($startDate),
//        'period_end_date' => NULL,
//      ];
//
//      if ($endDate) {
//        $detailsParams['period_end_date'] = CRM_Utils_Date::processDate($endDate);
//      }
//    }
//
//    return CRM_Hrjobcontract_Test_Fabricator_HRJobContract::fabricate(
//      ['contact_id' => $contactID],
//      $detailsParams
//    );

    $contract = CRM_Hrjobcontract_BAO_HRJobContract::create(['contact_id' => $contactID]);
    if($startDate) {
      $params = [
        'jobcontract_id' => $contract->id,
        'period_start_date' => CRM_Utils_Date::processDate($startDate),
        'period_end_date' => null,
      ];

      if($endDate) {
        $params['period_end_date'] = CRM_Utils_Date::processDate($endDate);
      }
      $params = array_merge($params, $extraParams);
      CRM_Hrjobcontract_BAO_HRJobDetails::create($params);
    }

    return $contract;
  }

}
