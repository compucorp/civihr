<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HRJobContractTestTrait {

  /**
   * Property used to keep track of the contacts created by the createContacts
   * method
   *
   * @var array
   */
  protected $contacts;

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
   * Creates as many contacts (Individuals) as the number of contacts given.
   *
   * @param int $numberOfContacts
   *
   * @throws \CiviCRM_API3_Exception
   */
  protected function createContacts($numberOfContacts = 1) {
    $numberOfCreatedContacts = count($this->contacts);
    for($i = $numberOfCreatedContacts; $i < $numberOfCreatedContacts + $numberOfContacts; $i++) {
      $result = civicrm_api3('Contact', 'create', [
        'contact_type' => 'Individual',
        'first_name' => 'First',
        'middle_name' => 'N. ',
        'last_name' => 'Last' . $i,
        'email' => 'name_'.$i.'@example.org',
      ]);

      $this->contacts[] = array_shift($result['values']);
    }
  }


  /**
   * Deletes the contract with the given ID.
   *
   * This method uses the delectecontract action of the HRJobContract API, to
   * make sure all the related entities will also be deleted, and the contract
   * will be marked as deleted on the database.
   *
   * @param $id
   *
   * @throws \CiviCRM_API3_Exception
   */
  protected function deleteContract($id) {
    civicrm_api3('HRJobContract', 'deletecontract', ['id' => $id]);
  }

}
