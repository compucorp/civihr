<?php

require_once 'CRM/Core/Page.php';

class CRM_Hrjobcontract_Page_JobContractTab extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('JobContractTab'));

    self::registerScripts();
    parent::run();
  }

  static function registerScripts() {
    static $loaded = FALSE;

    if ($loaded) {
      return;
    }

    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addStyleFile('org.civicrm.hrjobcontract', 'css/hrjc.css')
      ->addScriptFile('org.civicrm.hrjobcontract', CRM_Core_Config::singleton()->debug ? 'js/src/job-contract.js' : 'js/dist/job-contract.min.js', 1010)
      ->addSettingsFactory(function () {
        $config = CRM_Core_Config::singleton();

        return array(
          'debug' => $config->debug,
          'PseudoConstant' => array(
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
          )
        );
      });
  }


  /**
   * Get a list of all fields to create model
   *
   * @return array e.g. $fields[$entityName][$fieldName] = ''
   */
  public static function getFields () {
    $entity = array('HRJobDetails','HRJobHour','HRJobPay','HRJobPension','HRJobHealth','HRJobLeave');
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
  public static function getFieldOptions() {
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
        'department',
        'hrjc_contract_end_reason' => 'end_reason',
      ),
      'HRJobHour' => array(
        'hours_type',
        'hours_unit',
      ),
      'HRJobPay' => array(
        //'pay_scale',
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

    $absenceTypeResult = civicrm_api3('AbsenceType', 'get', array(
        'sequential' => 1,
        'is_active' => 1,
        'options' => array(
          'sort' => 'weight'
        ),
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
  }

  /**
   * Get initial contact contract list
   */
  public static function getContractList () {
    $contract_list = [];

     $result = civicrm_api3('HRJobContract', 'get', array(
        'contact_id' => CRM_Utils_Request::retrieve('cid', 'Integer'),
        'deleted' => 0,
        'sequential' => 1,
    ));

    if ($result['is_error'] || empty($result['values'])) {
      return $contract_list;
    }

    return $contract_list = $result['values'];
  }

  /**
   * Get a list of templates demonstrating how to format currencies.
   */
  static function getCurrencyFormats() {
    $currencies = CRM_Core_PseudoConstant::get('CRM_Hrjobcontract_DAO_HRJobPay', 'pay_currency');
    $formats = array();
    foreach ($currencies as $currency => $label) {
      $formats[$currency] = CRM_Utils_Money::format(1234.56, $currency);
    }
    return $formats;
  }

  /**
   * Get a job hours duration for full time, part time and casual.
   */
  static function getJobHoursTime() {
    $job_hours_time = array();
    $result = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' =>'hrjc_hours_type',
    ));
    foreach ($result['values'] as $key => $val) {
      $job_hours_time[$val['name']] = $val['value'];
    }
    return $job_hours_time;
  }

  /**
   * Get a days per week/month as per configuration file
   */
  static function getDaysPerTime() {
    $unitSettingMap = array(
      'work_days_per_week' => 'DaysPerWeek',
      'work_days_per_month' => 'DaysPerMonth'
    );
    $settings = civicrm_api3('Setting', 'getsingle', array(
      'return' => array_keys($unitSettingMap),
    ));

    $days['perWeek'] = $settings['work_days_per_week'];//DAYS_PER_WEEK;
    $days['perMonth'] = $settings['work_days_per_month'];//DAYS_PER_MONTH;
    return $days;
  }

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
