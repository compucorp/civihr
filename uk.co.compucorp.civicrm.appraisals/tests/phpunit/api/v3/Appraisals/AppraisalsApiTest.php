<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRVisa_ActivityTest
 *
 * @group headless
 */
class api_v3_Appraisals_AppraisalsApiTest extends CiviUnitTestCase implements HeadlessInterface , TransactionalInterface {
    const SECONDS_IN_DAY = 86400;

    protected $_contacts = array(
        array(
            'first_name' => 'First',
            'email' => 'firstappraisalcontact@notmail000.com',
        ),
        array(
            'first_name' => 'Second',
            'email' => 'secondappraisalcontact@notmail000.com',
        ),
        array(
            'first_name' => 'Third',
            'email' => 'thirdappraisalcontact@notmail000.com',
        ),
        array(
            'first_name' => 'Fourth',
            'email' => 'fourthappraisalcontact@notmail000.com',
        ),
    );

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

    function setUp() {
        $upgrader = CRM_Appraisals_Upgrader::instance();
        $upgrader->install();
    }

    function tearDown() {

    }

    /**
     * Test Appraisals API flow including
     * - create Appraisal Cycle
     * - create Appraisals for Appraisal Cycle
     * - modify Appraisals and populate Appraisal Cycle Due Dates into Appraisal Due Dates.
     * - delete Appraisal Cycle and Appraisal
     */
    function testAppraisalsApiFlow() {
        $time = time();
        $expected = array();

        ////////// Create Test Contacts ////////////////////////////////////////
        foreach ($this->_contacts as $key => $contact) {
            $result = civicrm_api3('Contact', 'create', array(
                'sequential' => 1,
                'contact_type' => "Individual",
                'first_name' => $contact['first_name'],
                'last_name' => "Appraisal Test Contact",
                'email' => $contact['email'],
            ));
            $this->_contacts[$key]['id'] = $result['id'];
        }

        $cycleStartDate = $time;
        $cycleEndDate = $time + 30 * self::SECONDS_IN_DAY;
        $selfAppraisalDue = $time + 5 * self::SECONDS_IN_DAY;
        $managerAppraisalDue = $time + 15 * self::SECONDS_IN_DAY;
        $gradeDue = $time + 25 * self::SECONDS_IN_DAY;

        ////////// Create Test Appraisal Cycle 1: //////////////////////////////
        civicrm_api3('AppraisalCycle', 'create', array(
            'sequential' => 1,
            'name' => "Test Appraisal Cycle 1",
            'cycle_start_date' => date('Y-m-d', $cycleStartDate),
            'cycle_end_date' => date('Y-m-d', $cycleEndDate),
            'self_appraisal_due' => date('Y-m-d', $selfAppraisalDue),
            'manager_appraisal_due' => date('Y-m-d', $managerAppraisalDue),
            'grade_due' => date('Y-m-d', $gradeDue),
            'type_id' => 1,
        ));
        $expected['AppraisalCycle1'] = array(
            'id' => 1,
            'name' => "Test Appraisal Cycle 1",
            'cycle_start_date' => date('Y-m-d', $cycleStartDate),
            'cycle_end_date' => date('Y-m-d', $cycleEndDate),
            'self_appraisal_due' => date('Y-m-d', $selfAppraisalDue),
            'manager_appraisal_due' => date('Y-m-d', $managerAppraisalDue),
            'grade_due' => date('Y-m-d', $gradeDue),
            'type_id' => 1,
        );

        $appraisalCycle1 = civicrm_api3('AppraisalCycle', 'get', array(
            'sequential' => 1,
            'id' => 1,
        ));
        $this->assertAPIArrayComparison(
            CRM_Utils_Array::first($appraisalCycle1['values']),
            $expected['AppraisalCycle1']
        );

        ////////// Create Test Appraisal Cycle 2: //////////////////////////////
        civicrm_api3('AppraisalCycle', 'create', array(
            'sequential' => 1,
            'name' => "Test Appraisal Cycle 2",
            'cycle_start_date' => date('Y-m-d', $cycleStartDate + 30 * self::SECONDS_IN_DAY),
            'cycle_end_date' => date('Y-m-d', $cycleEndDate + 30 * self::SECONDS_IN_DAY),
            'self_appraisal_due' => date('Y-m-d', $selfAppraisalDue + 30 * self::SECONDS_IN_DAY),
            'manager_appraisal_due' => date('Y-m-d', $managerAppraisalDue + 30 * self::SECONDS_IN_DAY),
            'grade_due' => date('Y-m-d', $gradeDue + 30 * self::SECONDS_IN_DAY),
            'type_id' => 2,
        ));
        $expected['AppraisalCycle2'] = array(
            'id' => 2,
            'name' => "Test Appraisal Cycle 2",
            'cycle_start_date' => date('Y-m-d', $cycleStartDate + 30 * self::SECONDS_IN_DAY),
            'cycle_end_date' => date('Y-m-d', $cycleEndDate + 30 * self::SECONDS_IN_DAY),
            'self_appraisal_due' => date('Y-m-d', $selfAppraisalDue + 30 * self::SECONDS_IN_DAY),
            'manager_appraisal_due' => date('Y-m-d', $managerAppraisalDue + 30 * self::SECONDS_IN_DAY),
            'grade_due' => date('Y-m-d', $gradeDue + 30 * self::SECONDS_IN_DAY),
            'type_id' => 2,
        );

        $appraisalCycle2 = civicrm_api3('AppraisalCycle', 'get', array(
            'sequential' => 1,
            'id' => 2,
        ));
        $this->assertAPIArrayComparison(
            CRM_Utils_Array::first($appraisalCycle2['values']),
            $expected['AppraisalCycle2']
        );

        ////////// Create Test Appraisal 1 for Appraisal Cycle 1: //////////////
        civicrm_api3('Appraisal', 'create', array(
            'sequential' => 1,
            'appraisal_cycle_id' => 1,
            'contact_id' => $this->_contacts[0]['id'],
            'manager_id' => $this->_contacts[2]['id'],
            'self_appraisal_file_id' => "",
            'manager_appraisal_file_id' => "",
            'self_appraisal_due' => "",
            'manager_appraisal_due' => "",
            'grade_due' => "",
            'due_changed' => "0",
            'meeting_date' => date('Y-m-d', $cycleEndDate - 2 * self::SECONDS_IN_DAY),
            'meeting_completed' => "0",
            'approved_by_employee' => "0",
            'grade' => "",
            'notes' => "Test Notes of Test Appraisal 1",
            'status_id' => 1,
        ));
        $expected['Appraisal1'] = array(
            "id" => "1",
            "appraisal_cycle_id" => "1",
            "contact_id" => "{$this->_contacts[0]['id']}",
            "manager_id" => "{$this->_contacts[2]['id']}",
            //"self_appraisal_file_id" => "",
            //"manager_appraisal_file_id" => "",
            "self_appraisal_due" => date('Y-m-d', $selfAppraisalDue),
            "manager_appraisal_due" => date('Y-m-d', $managerAppraisalDue),
            "grade_due" => date('Y-m-d', $gradeDue),
            "due_changed" => "0",
            "meeting_date" => date('Y-m-d', $cycleEndDate - 2 * self::SECONDS_IN_DAY),
            "meeting_completed" => "0",
            "approved_by_employee" => "0",
            //"grade" => "",
            "notes" => "Test Notes of Test Appraisal 1",
            "status_id" => "1",
        );

        $appraisal1 = civicrm_api3('Appraisal', 'get', array(
            'sequential' => 1,
            'id' => 1,
        ));
        $this->assertAPIArrayComparison(
            CRM_Utils_Array::first($appraisal1['values']),
            $expected['Appraisal1']
        );

        ////////// Create Test Appraisal 2 for Appraisal Cycle 1: //////////////
        civicrm_api3('Appraisal', 'create', array(
            'sequential' => 1,
            'appraisal_cycle_id' => 1,
            'contact_id' => $this->_contacts[1]['id'],
            'manager_id' => $this->_contacts[2]['id'],
            'meeting_date' => date('Y-m-d', $cycleEndDate - 2 * self::SECONDS_IN_DAY),
            'notes' => "Test Notes of Test Appraisal 2",
        ));
        $expected['Appraisal2'] = array(
            "id" => "2",
            "appraisal_cycle_id" => "1",
            "contact_id" => $this->_contacts[1]['id'],
            "manager_id" => $this->_contacts[2]['id'],
            "self_appraisal_due" => date('Y-m-d', $selfAppraisalDue),
            "manager_appraisal_due" => date('Y-m-d', $managerAppraisalDue),
            "grade_due" => date('Y-m-d', $gradeDue),
            "due_changed" => "0",
            "meeting_date" => date('Y-m-d', $cycleEndDate - 2 * self::SECONDS_IN_DAY),
            "meeting_completed" => "0",
            "approved_by_employee" => "0",
            "notes" => "Test Notes of Test Appraisal 2",
            "status_id" => "1",
        );

        $appraisal2 = civicrm_api3('Appraisal', 'get', array(
            'sequential' => 1,
            'id' => 2,
        ));
        $this->assertAPIArrayComparison(
            CRM_Utils_Array::first($appraisal2['values']),
            $expected['Appraisal2']
        );

        ////////// Change 'grade_due' for Appraisal 2: /////////////////////////
        civicrm_api3('Appraisal', 'create', array(
            'sequential' => 1,
            'id' => 2,
            'grade_due' => date('Y-m-d', $gradeDue + 1 * self::SECONDS_IN_DAY),
        ));

        ////////// Change Due Dates for Appraisal Cycle 1: /////////////////////
        civicrm_api3('AppraisalCycle', 'create', array(
            'sequential' => 1,
            'id' => 1,
            "self_appraisal_due" => date('Y-m-d', $selfAppraisalDue + 2 * self::SECONDS_IN_DAY),
            "manager_appraisal_due" => date('Y-m-d', $managerAppraisalDue + 2 * self::SECONDS_IN_DAY),
            'grade_due' => date('Y-m-d', $gradeDue + 2 * self::SECONDS_IN_DAY),
        ));
        // Checking Due Dates for Appraisal 1.
        // They should be changed according to the AppraisalCycle create call above.
        $expected['Appraisal1DueDates'] = array(
            'id' => "1",
            'self_appraisal_due' => date('Y-m-d', $selfAppraisalDue + 2 * self::SECONDS_IN_DAY),
            'manager_appraisal_due' => date('Y-m-d', $managerAppraisalDue + 2 * self::SECONDS_IN_DAY),
            'grade_due' => date('Y-m-d', $gradeDue + 2 * self::SECONDS_IN_DAY),
        );

        $appraisal1DueDates = civicrm_api3('Appraisal', 'get', array(
          'sequential' => 1,
          'id' => 1,
          'return' => "id,self_appraisal_due,manager_appraisal_due,grade_due",
        ));
        $this->assertAPIArrayComparison(
            CRM_Utils_Array::first($appraisal1DueDates['values']),
            $expected['Appraisal1DueDates']
        );

        // Checking Due Dates for Appraisal 2.
        // They should be unchanged as we've modified individual 'grade_due' date.
        $expected['Appraisal2DueDates'] = array(
            'id' => "2",
            'self_appraisal_due' => date('Y-m-d', $selfAppraisalDue),
            'manager_appraisal_due' => date('Y-m-d', $managerAppraisalDue),
            'grade_due' => date('Y-m-d', $gradeDue + 1 * self::SECONDS_IN_DAY),
        );

        $appraisal2DueDates = civicrm_api3('Appraisal', 'get', array(
          'sequential' => 1,
          'id' => 2,
          'return' => "id,self_appraisal_due,manager_appraisal_due,grade_due",
        ));
        $this->assertAPIArrayComparison(
            CRM_Utils_Array::first($appraisal2DueDates['values']),
            $expected['Appraisal2DueDates']
        );

        ////////// Delete Appraisal 1: /////////////////////////////////////////
        civicrm_api3('Appraisal', 'delete', array(
          'sequential' => 1,
          'id' => 1,
        ));
        $this->assertAPIDeleted('Appraisal', 1);

        ////////// Delete Appraisal Cycle 2: /////////////////////////////////////////
        civicrm_api3('AppraisalCycle', 'delete', array(
          'sequential' => 1,
          'id' => 2,
        ));
        $this->assertAPIDeleted('AppraisalCycle', 2);
    }
}
