<?php

require_once 'CRM/Core/Page.php';

class CRM_Appraisals_Page_Appraisals extends CRM_Core_Page {
  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Appraisals'));

//    self::registerScripts();
    parent::run();
  }
  
  static function registerScripts() {
    static $loaded = FALSE;
    if ($loaded) {
      return;
    }
    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addSettingsFactory(function () {
      $config = CRM_Core_Config::singleton();
      return array(
        /*'PseudoConstant' => array(
          'locationType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'),
          'job_hours_time' => CRM_Hrjobcontract_Page_JobContractTab::getJobHoursTime(),
          'working_days' => CRM_Hrjobcontract_Page_JobContractTab::getDaysPerTime(),
        ),
        'FieldOptions' => CRM_Hrjobcontract_Page_JobContractTab::getFieldOptions(),
        'jobContractTabApp' => array(
          'contactId' => CRM_Utils_Request::retrieve('cid', 'Integer'),
          'domainId' => CRM_Core_Config::domainID(),
          'isLogEnabled'    => (bool) $config->logging,
          'loggingReportId' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
          'currencies' => CRM_Hrjobcontract_Page_JobContractTab::getCurrencyFormats(),
          'defaultCurrency' => $config->defaultCurrency,
          'path' => CRM_Core_Resources::singleton()->getUrl('org.civicrm.hrjobcontract'),
          'fields' => CRM_Hrjobcontract_Page_JobContractTab::getFields(),
          'contractList' => CRM_Hrjobcontract_Page_JobContractTab::getContractList(),
          'maxFileSize' => file_upload_max_size(),
        ),*/
        'debug' => $config->debug,
      );
    });
  }


  /**
   * Get a list of all fields to create model
   *
   * @return array e.g. $fields[$entityName][$fieldName] = ''
   */
  public static function getFields () {
    $entity = array('AppraisalCycle', 'Appraisal');
    $fields = array();

    foreach ($entity as $entityName) {
      $result = civicrm_api3($entityName, 'getfields', array(
          'sequential' => 1,
      ));

      $fields[$entityName] = $result['values'];
    }

    return $fields;
  }

  /**
   * Get a list of all interesting options
   *
   * @return array e.g. $fieldOptions[$entityName][$fieldName] contains key-value options
   */
/*  public static function getFieldOptions() {
    $fields = array(
      'HRJobContractRevision' => array(
        'hrjc_revision_change_reason' => 'change_reason',
      ),
      'HRJobDetails' => array(
        "contract_type",
        "level_type",
        "location",
        'notice_unit',
        'notice_unit_employee',
        'department'
      ),
      'HRJobHour' => array(
        'hours_type',
        'hours_unit',
      ),
      'HRJobPay' => array(
        'is_paid',
        'pay_unit',
        'pay_currency',
        'pay_cycle',
      ),
      'HRJobPension' => array(
        'pension_type',
      ),
      'HRJobHealth' => array(
        'provider',
        'plan_type',
        'provider_life_insurance',
        'plan_type_life_insurance',
      ),
      'HRJobRole' => array(
        'location',
        'department',
        'level_type',
        'role_hours_unit',
        'region'
      ),
    );
    $fieldOptions = array();
    foreach ($fields as $entityName => $fieldNames) {
      foreach ($fieldNames as $fieldName) {
        $fieldOptions[$entityName][$fieldName] = CRM_Core_PseudoConstant::get("CRM_Hrjobcontract_DAO_{$entityName}", $fieldName);
      }
    }
    
    $absenceTypeResult = civicrm_api3('HRAbsenceType', 'get', array(
        'sequential' => 1,
        'return' => 'id,title',
    ));
    
    foreach ($absenceTypeResult['values'] as $value) {
        $fieldOptions['HRJobLeave']['leave_type'][$value['id']] = $value['title'];
    }
    
    $fieldOptions['HRJobPay']['benefit_name'] = CRM_Hrjobcontract_Page_JobContractTab::getCustomOptions('hrjc_benefit_name');
    $fieldOptions['HRJobPay']['benefit_type'] = CRM_Hrjobcontract_Page_JobContractTab::getCustomOptions('hrjc_benefit_type');
    $fieldOptions['HRJobPay']['deduction_name'] = CRM_Hrjobcontract_Page_JobContractTab::getCustomOptions('hrjc_deduction_name');
    $fieldOptions['HRJobPay']['deduction_type'] = CRM_Hrjobcontract_Page_JobContractTab::getCustomOptions('hrjc_deduction_type');
    
    return $fieldOptions;
  }*/
  
  /**
   * Get custom options by option group name
   */
  static function getCustomOptions($optionGroupName) {
    $data = array();
    $result = civicrm_api3('OptionValue', 'get', array('option_group_id' => $optionGroupName));
    foreach ($result['values'] as $key => $val) {
      $data[$val['value']] = $val['label'];
    }
    return $data;
  }
}
