<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HrJobRolesTestTrait {

  /**
   * Creates a new Job role from the given data
   *
   * @param array $params
   */
  protected function createJobRole($params = array()) {
    CRM_Hrjobroles_BAO_HrJobRoles::create($params);
  }

  /**
   * Creates single (Individuals) contact from the provided data.
   *
   * @param array $params should contain first_name and last_name
   * @return int return the contact ID
   * @throws \CiviCRM_API3_Exception
   */
  protected function createContact($params) {
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => "Individual",
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      'display_name' => $params['first_name'] . ' ' . $params['last_name'],
    ));
    return $result['id'];
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

  /**
   * Creates a new department option value
   *
   * @param string $name
   * @param string $label
   * @return int $newDepartment
   * @throws \CiviCRM_API3_Exception
   */
  protected function createDepartment($name, $label) {
    $newDepartment = civicrm_api3('OptionValue', 'create', array(
      'option_group_id' => 'hrjc_department',
      'name' => $name,
      'label'=> $label,
    ));

    return $newDepartment['id'];
  }

}
