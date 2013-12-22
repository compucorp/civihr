<?php

require_once 'CRM/Core/Page.php';

class CRM_HRAbsence_Page_EmployeeAbsencePage extends CRM_Core_Page {
  function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');

    if (!empty($contactID)) {
      // TODO: Check validity & permissions of $contactID
      // This shouldn't be critical because all the data-access will go
      // through permissioned APIs, but it would be a good precaution.

      CRM_Utils_System::setTitle(ts('Absences for %1', array(
        1 => $contactID
      )));
      self::registerResources($contactID);
    }
    else {
      $session = CRM_Core_Session::singleton();
      if (is_numeric($session->get('userID'))) {
        CRM_Utils_System::setTitle(ts('My Absences'));
        self::registerResources($session->get('userID'));
      } else {
        throw new CRM_Core_Exception("Failed to determine contact ID");
      }
    }

    parent::run();
  }

  public static function registerResources($contactID) {
    static $loaded = FALSE;
    if ($loaded) {
      return;
    }
    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addSettingsFactory(function () use ($contactID) {
      return array(
        'PseudoConstant' => array(
          'locationType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'),
        ),
        'FieldOptions' => CRM_HRAbsence_Page_EmployeeAbsencePage::getFieldOptions(),
        'absenceApp' => array(
          'contactId' => $contactID,
          'activityTypes' => array(
            1 => ts('Sick'),
            2 => ts('Vacation'),
            3 => ts('TOIL'),
            4 => ts('TOIL (Credit)'),
            5 => ts('Maternity'),
            6 => ts('Paternity'),
            7 => ts('Adoption'),
            8 => ts('Other'),
          ),
          'periods' => array(
            8 => 'Jan 1, 2011 to Dec 31, 2011',
            9 => 'Jan 1, 2012 to Dec 31, 2012',
            10 => 'Jan 1, 2013 to Dec 31, 2013'
          ),
        ),
      );
    })
      ->addScriptFile('civicrm', 'packages/backbone/json2.js', 100, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/underscore.js', 110, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.js', 120, 'html-header')
      ->addScriptFile('civicrm', 'packages/backbone/backbone.marionette.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.modelbinder.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmContactField.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'js/crm.backbone.js', 130, 'html-header', FALSE)
      ->addStyleFile('org.civicrm.hrabsence', 'css/hrabsence.css', 140, 'html-header');

    self::addScriptFiles('org.civicrm.hrabsence', 'js/*.js', 200, 'html-header');
    self::addScriptFiles('org.civicrm.hrabsence', 'js/*/*.js', 300, 'html-header');
    self::addScriptFiles('org.civicrm.hrabsence', 'js/*/*/*.js', 400, 'html-header');
    self::addTemplateFiles('org.civicrm.hrabsence', 'CRM/HRAbsence/Underscore/*.tpl', 'page-header');
    // self::addTemplates('civicrm', 'CRM/Form/validate.tpl', 'page-header');
  }

  /**
   * Add a batch of JS files using a glob pattern
   *
   * FIXME: Move to CRM_Core_Resources
   *
   * @param string $ext the name of the extension containing the files
   * @param string $pattern glob file pattern (eg "js/*.js")
   * @param int $baseWeight
   * @param string $region
   * @return CRM_Core_Resources
   */
  public static function addScriptFiles($ext, $pattern, $baseWeight = CRM_Core_Resources::DEFAULT_WEIGHT, $region = CRM_Core_Resources::DEFAULT_REGION) {
    $resources = CRM_Core_Resources::singleton();
    $weight = $baseWeight;
    $baseDir = CRM_Extension_System::singleton()->getMapper()->keyToBasePath($ext) . '/';
    $files = (array) glob($baseDir . $pattern, GLOB_BRACE); // some platforms return array(); others, FALSE
    foreach ($files as $file) {
      $fileName = substr($file, strlen($baseDir));
      $resources->addScriptFile($ext, $fileName, $weight++, $region);
    }
    return $resources;
  }

  /**
   * Add a batch of tpl files using a glob pattern
   *
   * @param string $ext the ame of the extension containing the template files
   * @param string $pattern glob file pattern (eg "CRM/Foo/*.tpl")
   * @param string $region
   */
  public static function addTemplateFiles($ext, $pattern, $region = CRM_Core_Resources::DEFAULT_REGION) {
    $templateDir = CRM_Extension_System::singleton()->getMapper()->keyToBasePath($ext) . '/templates/';
    $region = CRM_Core_Region::instance($region);
    $files = (array) glob($templateDir . $pattern, GLOB_BRACE); // some platforms return array(); others, FALSE
    foreach ($files as $file) {
      $fileName = substr($file, strlen($templateDir));
      $region->add(array(
        'template' => $fileName
      ));
    }
  }

  /**
   * Get a list of all interesting options
   *
   * @return array e.g. $fieldOptions[$entityName][$fieldName] contains key-value options
   */
  public static function getFieldOptions() {
    $fields = array( /*
      'HRAbsenceFoo' => array(
        'location',
        'department'
      ),
      */
    );
    $fieldOptions = array();
    foreach ($fields as $entityName => $fieldNames) {
      foreach ($fieldNames as $fieldName) {
        $fieldOptions[$entityName][$fieldName] = CRM_Core_PseudoConstant::get("CRM_HRAbsence_DAO_{$entityName}", $fieldName);
      }
    }
    return $fieldOptions;
  }

}
