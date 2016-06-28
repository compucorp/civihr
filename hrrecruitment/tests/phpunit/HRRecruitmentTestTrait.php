<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HRRecruitmentTestTrait {

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
   * Helper function to load data into DB between iterations of the unit-test
   */
  protected static function phpunitPopulateDB() {
    //populate vacancy_status and case_status of type Application
    $result = civicrm_api3('OptionGroup', 'create', array(
        'name' => 'vacancy_status',
        'title' => ts('Vacancy Status'),
        'is_reserved' => 1,
        'is_active' => 1,
      )
    );
    $vacancyStatus = array(
      'Draft' => ts('Draft'),
      'Open' => ts('Open'),
      'Closed' => ts('Closed'),
      'Cancelled' => ts('Cancelled'),
      'Rejected' => ts('Rejected')
    );
    $weight = 1;
    foreach ($vacancyStatus as $name => $label) {
      $statusParam = array(
        'option_group_id' => $result['id'],
        'label' => $label,
        'name' => $name,
        'value' => $weight++,
        'is_active' => 1,
      );
      if ($name == 'Draft') {
        $statusParam['is_default'] = 1;
      }
      elseif ($name == 'Open') {
        $statusParam['is_reserved'] = 1;
      }
      civicrm_api3('OptionValue', 'create', $statusParam);
    }
    $stages = array(
      'Apply' => ts('Apply'),
      'Ongoing_Vacancy' => ts('Ongoing'),
      'Phone_Interview' => ts('Phone Interview'),
      'Manager_Interview' => ts('Manager Interview'),
      'Board_Interview' => ts('Board Interview'),
      'Group_Interview' => ts('Group Interview'),
      'Psych_Exam' => ts('Psych Exam'),
      'Offer' => ts('Offer'),
      'Hired' => ts('Hired'),
    );
    $count = count(CRM_Core_OptionGroup::values('case_status'));
    foreach ($stages as $name => $label) {
      $count++;
      $caseStatusParam = array(
        'option_group_id' => 'case_status',
        'label' => $label,
        'name' => $name,
        'value' => $count,
        'grouping' => 'Vacancy',
        'filter' => 1,
      );
      civicrm_api3('OptionValue', 'create', $caseStatusParam);
    }
    $import = new CRM_Utils_Migrate_Import();
    $import->run(
      CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrrecruitment')
      . '/xml/auto_install.xml'
    );
  }

}
