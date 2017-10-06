<?php

/**
 * Class CRM_HRSampleData_Importer_JobContract
 */
class CRM_HRSampleData_Importer_JobContract extends CRM_HRSampleData_CSVImporterVisitor
{

  /**
   * Stores standard hours IDs/locations
   *
   * @var array
   */
  private $standardHours = [];

  /**
   * Stores pay scales IDs/Names
   *
   * @var array
   */
  private $payScales = [];

  /**
   * Stores absence types
   *
   * @var array
   */
  private $absenceTypes = [];


  public function __construct() {
    $this->standardHours = $this->getFixData('HRHoursLocation', 'location', 'id');
    $this->payScales = $this->getFixData('HRPayScale', 'pay_scale', 'id');
    $this->absenceTypes = $this->getFixData('HRAbsenceType', 'name', 'id');
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $entities = $this->parseRow($row);

    $currentID = $this->unsetArrayElement($entities['HRJobContract'], 'id');


    // Fix data for insert
    $entities['HRJobContract']['contact_id'] =  $this->getDataMapping('contact_mapping', $entities['HRJobContract']['contact_id']);

    $entities['HRJobDetails']['sequential'] = 1;

    $entities['HRJobHealth']['provider'] = $this->getDataMapping('contact_mapping', $entities['HRJobHealth']['provider']);
    $entities['HRJobHealth']['provider_life_insurance'] = $this->getDataMapping('contact_mapping', $entities['HRJobHealth']['provider_life_insurance']);

    $entities['HRJobPension']['pension_type'] = $this->getDataMapping('contact_mapping', $entities['HRJobPension']['pension_type']);

    $entities['HRJobHour']['location_standard_hours'] = $this->standardHours[$entities['HRJobHour']['location_standard_hours']];

    $entities['HRJobPay']['pay_scale'] = $this->payScales[$entities['HRJobPay']['pay_scale']];


    $contractID = NULL;
    $currentRevisionID = NULL;
    $entitiesList = [
      'HRJobContract',
      'HRJobDetails',
      'HRJobHealth',
      'HRJobHour',
      'HRJobPay',
      'HRJobPension',
      'HRJobLeave',
    ];

    foreach($entitiesList as $entity) {
      if ($entity == 'HRJobLeave') {
        $this->prepareLeaveData($entities['HRJobLeave'], $contractID, $currentRevisionID);
      }

      $result = $this->createEntityRecord($entity, $entities[$entity], $contractID, $currentRevisionID);

      if ($entity == 'HRJobContract') {
        $contractID = $result['id'];
      }

      if ($entity == 'HRJobDetails') {
        $currentRevisionID = $result['values'][0]['jobcontract_revision_id'];
      }
    }

    $this->setDataMapping('contracts_mapping', $currentID, $contractID);
  }


  /**
   * Parses and separates row columns into separate entities
   *
   * @param array $row
   *
   * @return array
   *   Associative array in the following format :
   *   ['entity_1' => ['filed_1' => 'value_1', ..etc]], 'entity_2' => ['filed_2' => 'value_2']..etc]]
   */
  private function parseRow($row) {
    $data = [];
    foreach($row as $key => $value) {
      list($entity, $field) = explode('-', $key);
      $data[$entity][$field] = $value;
    }
    return $data;
  }

  /**
   * Creates a record for the specified job contract entity.
   *
   * @param string $entity
   *   Job Contract Entity Name to create record for.
   * @param array $data
   *   Array of entity record data to insert.
   * @param int|NULL $contractID
   *   Contract ID if exist.
   * @param int|NULL $revisionID
   *   Revision ID if exist.
   *
   * @return array
   */
  private function createEntityRecord($entity, $data, $contractID = NULL, $revisionID = NULL) {
    if ($contractID != NULL) {
      $data['jobcontract_id'] = $contractID;
    }

    if ($revisionID != NULL) {
      $data['jobcontract_revision_id'] = $revisionID;
    }

    // For leave revision `replace` should be used instead of `create`
    $action = ($entity == 'HRJobLeave') ? 'replace' : 'create';

    return $this->callAPI($entity, $action, $data);
  }

  /**
   * Prepares Job Leave entity data to a valid API format.
   *
   * @param array $row
   *   Job leave entity data.
   * @param int $contractID
   * @param int $revisionID
   */
  private function prepareLeaveData(&$row, $contractID, $revisionID) {
    // create leave
    $leaveRows = [];
    $leaveEntitlements= array_map('trim', explode(',', $row['leave_amount']));
    foreach($leaveEntitlements as $leave) {
      list($leaveType, $amount) = array_map('trim', explode(':', $leave));
      $leaveType = $this->absenceTypes[$leaveType];
      $amount = number_format($amount, 2);
      $leaveRows[] = [
        'leave_type' => "$leaveType",
        'leave_amount' => "$amount",
        'add_public_holidays' => "0",
        "jobcontract_revision_id" => "$revisionID",
        "jobcontract_id" => "$contractID",
      ];
    }

    $row['values'] = $leaveRows;
    unset($row['leave_amount']);
  }

}
