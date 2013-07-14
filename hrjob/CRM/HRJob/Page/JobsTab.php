<?php

require_once 'CRM/Core/Page.php';

class CRM_HRJob_Page_JobsTab extends CRM_Core_Page {
  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('JobsTab'));

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
      ->addSettingsFactory(function () {
      return array(
        'PseudoConstant' => array(
          'locationType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'),
        ),
        'FieldOptions' => CRM_HRJob_Page_JobsTab::getFieldOptions(),
        'jobTabApp' => array(
          'contact_id' => CRM_Utils_Request::retrieve('cid', 'Integer')
        ),
      );
    })
      ->addScriptFile('civicrm', 'packages/backbone/json2.js', 100, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/underscore.js', 110, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.js', 120, 'html-header')
      ->addScriptFile('civicrm', 'packages/backbone/backbone.marionette.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.modelbinder.js', 125, 'html-header', FALSE)
      ->addStyleFile('org.civicrm.hrjob', 'css/hrjob.css', 140, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/hrapp.js', 150, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/renderutil.js', 155, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/entities/hrjob.js', 155, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/intro/show_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/intro/show_views.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/tree/tree_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/tree/tree_views.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/summary/summary_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/summary/summary_views.js', 160, 'html-header')
    ;
    foreach (array('general', 'health', 'hour', 'leave', 'pay', 'pension', 'role') as $module) {
      CRM_Core_Resources::singleton()
        ->addScriptFile('org.civicrm.hrjob', "js/jobtabapp/$module/edit_controller.js", 160, 'html-header')
        ->addScriptFile('org.civicrm.hrjob', "js/jobtabapp/$module/edit_views.js", 160, 'html-header')
        ;
    }

    $templateDir = CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrjob') . '/templates/';
    $region = CRM_Core_Region::instance('page-header');
    foreach (glob($templateDir . 'CRM/HRJob/Underscore/*.tpl') as $file) {
      $fileName = substr($file, strlen($templateDir));
      $region->add(array(
        'template' => $fileName,
      ));
    }
  }

  /**
   * Get a list of all interesting options
   *
   * @return array e.g. $fieldOptions[$entityName][$fieldName] contains key-value options
   */
  public static function getFieldOptions() {
    $fields = array(
      'HRJob' => array(
        "contract_type",
        "seniority",
        "period_type",
        "location",
      ),
      'HRJobHour' => array(
        'hours_type',
        'hours_unit',
      ),
      'HRJobPay' => array(
        'pay_grade',
        'pay_unit',
      ),
      'HRJobPension' => array(
      ),
      'HRJobHealth' => array(
        'provider',
        'plan_type',
      ),
      'HRJobLeave' => array(
        'leave_type',
      ),
      'HRJobRole' => array(
        'location',
      ),
    );
    $fieldOptions = array();
    foreach ($fields as $entityName => $fieldNames) {
      foreach ($fieldNames as $fieldName) {
        $fieldOptions[$entityName][$fieldName] = CRM_Core_PseudoConstant::get("CRM_HRJob_DAO_{$entityName}", $fieldName);
      }
    }
    return $fieldOptions;
  }

}
