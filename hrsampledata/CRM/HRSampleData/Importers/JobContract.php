<?php


/**
 * Class CRM_HRSampleData_Importers_JobContract
 *
 */
class CRM_HRSampleData_Importers_JobContract extends CRM_HRSampleData_DataImporter
{

  /**
   * @var array To store standard hours IDs/locations
   */
  private $standardHours =[];

  /**
   * @var array To store pay scales IDs/Names
   */
  private $payScales =[];

  /**
   * @var array To store absence types types
   */
  private $absenceTypes =[];


  public function __construct() {
    $this->standardHours = $this->getFixData('HRHoursLocation', 'location', 'id');
    $this->payScales = $this->getFixData('HRPayScale', 'pay_scale', 'id');
    $this->absenceTypes = $this->getFixData('HRAbsenceType', 'name', 'id');
  }

  protected function insertRecord(array $row) {
    $entities = $this->parseRow($row);

    $currentID = $this->unsetArrayElement($entities['HRJobContract'], 'id');


    // Fix data for insert
    $entities['HRJobContract']['contact_id'] =  $this->getDataMapping('contact_mapping', $entities['HRJobContract']['contact_id']);

    $entities['HRJobDetails']['sequential'] = 1;

    $entities['HRJobHealth']['provider'] = $this->getDataMapping('contact_mapping', $entities['HRJobHealth']['provider']);
    $entities['HRJobHealth']['provider_life_insurance'] = $this->getDataMapping('contact_mapping', $entities['HRJobHealth']['provider_life_insurance']);

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
      // Prepare Job Leave Data before insert
      if ($entity == 'HRJobLeave') {
        $this->prepareLeaveData($entities['HRJobLeave'], $contractID, $currentRevisionID);
      }

      // Create entity record
      $result = $this->createEntityRecord($entity, $entities[$entity], $contractID, $currentRevisionID);

      if ($entity == 'HRJobContract') {
        $contractID = $result['id'];
      }

      if ($entity == 'HRJobDetails') {
        $currentRevisionID = $result['values'][0]['jobcontract_revision_id'];
      }
    }

    $this->setDataMapping('contracts_mapping', $currentID, $contractID);

    /*$transaction = new CRM_Core_Transaction();
    try {
      foreach($entitiesList as $entity) {
        // Prepare Job Leave Data before insert
        if ($entity == 'HRJobLeave') {
          $this->prepareLeaveData($entities['HRJobLeave'], $contractID, $currentRevisionID);
        }

        // Create entity record
        $result = $this->createEntityRecord($entity, $entities[$entity], $contractID, $currentRevisionID);

        if ($entity == 'HRJobContract') {
          $contractID = $result['id'];
        }

        if ($entity == 'HRJobDetails') {
          $currentRevisionID = $result['values'][0]['jobcontract_revision_id'];
        }
      }

      $this->setDataMapping('contracts_mapping', $currentID, $contractID);

    } catch(CiviCRM_API3_Exception $e) {
      $transaction->rollback();
    }*/
  }


  /**
   * Parse and separate row columns into separate entities
   *
   * @param array $row
   * @return array associative array in
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
   * Create a record for the specified job contract entity.
   *
   * @param string $entity Job Contract Entity Name to create record for.
   * @param array $data Array of entity record data to insert.
   * @param int|NULL $contractID Contract ID if exist.
   * @param int|NULL $revisionID Revision ID if exist.
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
   * Prepare Job Leave entity data for valid API format.
   *
   * @param array $row Job leave entity data.
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
