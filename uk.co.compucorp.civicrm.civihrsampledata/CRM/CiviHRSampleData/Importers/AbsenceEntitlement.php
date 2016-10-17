<?php


/**
 * Class CRM_CiviHRSampleData_Importers_AbsenceEntitlement
 *
 */
class CRM_CiviHRSampleData_Importers_AbsenceEntitlement extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @var array To store absence types types
   */
  private $absenceTypes =[];

  /**
   * @var array To store absence periods types
   */
  private $absencePeriods =[];

  public function prepareData() {
    $this->fetchAbsenceTypes();
    $this->fetchAbsencePeriods();
  }


  public function insertRecord(array $row) {
    // convert the absence contact ID to the actual contact ID
    $row['contact_id'] = self::$data['contact_mapping'][$row['contact_id']];

    // convert absence type name to ID
    $row['type_id'] = $this->absenceTypes[$row['type_id']];

    // convert absence period name to ID
    $row['period_id'] = $this->absencePeriods[$row['period_id']];

    civicrm_api3('HRAbsenceEntitlement', 'create', $row);
  }

  /**
   * Fetch absence types and cache them in ['name' => 'id' ...] format.
   *
   * @throws CiviCRM_API3_Exception
   */
  private function fetchAbsenceTypes() {
    $absTypes = civicrm_api3('HRAbsenceType', 'get', array(
      'sequential' => 1,
      'return' => array("id", "name"),
      'options' => array('limit' => 0),
    ));

    $result = [];
    foreach($absTypes['values'] as $absType) {
      $result[$absType['name']] = $absType['id'];
    }

    $this->absenceTypes = $result;
  }

  /**
   * Fetch absence periods and cache them in ['name' => 'id' ...] format.
   *
   * @throws CiviCRM_API3_Exception
   */
  private function fetchAbsencePeriods() {
    $absPeriods = civicrm_api3('HRAbsencePeriod', 'get', array(
      'sequential' => 1,
      'return' => array("id", "name"),
      'options' => array('limit' => 0),
    ));

    $result = [];
    foreach($absPeriods['values'] as $absPeriod) {
      $result[$absPeriod['name']] = $absPeriod['id'];
    }

    $this->absencePeriods = $result;
  }

}
