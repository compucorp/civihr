<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'ContractSummaryTestTrait.php';

class CRM_Contactsummary_Utils_AbsencesTest extends CiviUnitTestCase {

  use ContractSummaryTestTrait;

  function setUp() {
    $this->cleanDB();
    parent::setUp();
    $jobContractUpgrader = CRM_Hrjobcontract_Upgrader::instance();
    $jobContractUpgrader->install();
    $hrAbsenceUpgrader = CRM_HRAbsence_Upgrader::instance();
    $hrAbsenceUpgrader->installAbsenceTypes();
    $params = array(
      'weight' => 100,
      'label' => 'Absence',
      'filter' => 1,
      'is_active' => 1,
      'is_optgroup' => 0,
      'is_default' => 0,
      'grouping' => 'Timesheet',
    );
    civicrm_api3('activity_type', 'create', $params);
  }

  function tearDown() {
    parent::tearDown();
  }

  function testGetTotalAbsencesForSickLeaveType() {

    // create absence periods
    $StartDate1 = date('YmdHis', strtotime('-1 year'));
    $EndDate1 = date('YmdHis', strtotime('+1 year'));
    $StartDate2 = date('YmdHis', strtotime('-4 years'));
    $EndDate2 = date('YmdHis', strtotime('-3 years'));
    $StartDate3 = date('YmdHis', strtotime('+3 years'));
    $EndDate3 = date('YmdHis', strtotime('+4 years'));

    $currentPeriodID = $this->createAbsencePeriod($StartDate1, $EndDate1);
    $this->createAbsencePeriod($StartDate2, $EndDate2);
    $this->createAbsencePeriod($StartDate3, $EndDate3);

    // create contacts
    $contact1 = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID1 = $this->individualCreate($contact1);
    $contact2 = array('first_name'=>'walter1', 'last_name'=>'white1');
    $contactID2 = $this->individualCreate($contact2);
    $contact3 = array('first_name'=>'walter2', 'last_name'=>'white2');
    $contactID3 = $this->individualCreate($contact3);
    $contact4 = array('first_name'=>'walter3', 'last_name'=>'white3');
    $contactID4 = $this->individualCreate($contact4);


    // create contracts
    $this->createJobContract($contactID1, date('Y-m-d', strtotime('-14 days')));
    $this->createJobContract($contactID2, date('Y-m-d', strtotime('-5 days')));
    $this->createJobContract($contactID3, date('Y-m-d', strtotime('-10 days')));

    // get sick leave type ID
    $sickType = civicrm_api3('HRAbsenceType', 'getsingle', array(
      'sequential' => 1,
      'name' => "sick",
    ));
    $sickTypeID = $sickType['id'];

    // create absence entitlements
    $params1 =  array(
      'contact_id' => $contactID1,
      'type_id' => $sickTypeID,
      'amount' => 20,
      'period_id' => $currentPeriodID
    );
    $this->createAbsenceEntitlement($params1);

    $params2 =  array(
      'contact_id' => $contactID2,
      'type_id' => $sickTypeID,
      'amount' => 20,
      'period_id' => $currentPeriodID
    );
    $this->createAbsenceEntitlement($params2);

    $params3 =  array(
      'contact_id' => $contactID3,
      'type_id' => $sickTypeID,
      'amount' => 20,
      'period_id' => $currentPeriodID
    );
    $this->createAbsenceEntitlement($params3);

    // request sick leaves

    $this->requestLeave(
      'sick',
      $contactID1,
      date('Y-m-d', strtotime('+5 days')),
      date('Y-m-d', strtotime('+8 days')),
      'full_day'
    );
    $this->requestLeave(
      'sick',
      $contactID2,
      date('Y-m-d', strtotime('+5 days')),
      date('Y-m-d', strtotime('+12 days')),
      'full_day'
    );

    $leaveDays = CRM_Contactsummary_Utils_Absences::getTotalAbsences('sick', $currentPeriodID);
    $this->assertEquals(5760, round($leaveDays, 2));

  }

}
