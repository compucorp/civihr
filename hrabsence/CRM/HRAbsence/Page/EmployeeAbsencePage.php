<?php

require_once 'CRM/Core/Page.php';

class CRM_HRAbsence_Page_EmployeeAbsencePage extends CRM_Core_Page {
  function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');

    if (!empty($contactID)) {
      if (!(self::checkPermissions($contactID, 'viewWidget'))) {
        CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm'));
        CRM_Core_Error::statusBounce(ts('You do not have permission to access this page'));
      }
      CRM_Utils_System::setTitle(ts('Absences for %1', array(
        1 => CRM_Contact_BAO_Contact::displayName($contactID)
      )));
      self::registerResources($contactID);
    }
    else {
      $session = CRM_Core_Session::singleton();
      if (is_numeric($session->get('userID'))) {
        if (!(self::checkPermissions($session->get('userID'), 'viewWidget'))) {
          CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm'));
          CRM_Core_Error::statusBounce(ts('You do not have permission to access this page'));
        }
        CRM_Utils_System::setTitle(ts('My Absences'));
        self::registerResources($session->get('userID'));
      }
      else {
        throw new CRM_Core_Exception("Failed to determine contact ID");
      }
    }

    parent::run();
  }

  public static function registerResources($contactID, $absenceTypes = NULL, $activityTypes = NULL, $periods = NULL) {
    static $loaded = FALSE;
    if ($loaded) {
      return;
    }
    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addSettingsFactory(function () use ($contactID, $absenceTypes, $activityTypes, $periods) {

      if ($periods === NULL) {
        $res = civicrm_api3('HRAbsencePeriod', 'get', array('options' => array('sort' => "start_date DESC")));
        $periods = $res['values'];
      }
      if ($absenceTypes === NULL) {
        $res = civicrm_api3('HRAbsenceType', 'get', array());
        $absenceTypes = $res['values'];
      }
      if ($activityTypes === NULL) {
        $activityTypes = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
      }

      $legend = new CRM_HRAbsence_TypeLegend(9, $absenceTypes, $activityTypes);
      $i = 1;
      foreach($periods as $key=>$val){
        $sortPeriods[$i] = $val;
        $i++;
      }

      return array(
        'PseudoConstant' => array(
          'locationType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'),
          'absenceStatus' => CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus(),
        ),
        'Permissions' => array(
           'viewWidget' => CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'viewWidget'),
           'newAbsences' => CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'enableNewAbsence'),
           'enableEntitlement' => CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'enableEntitlements'),
           'getJobInfo' => CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'getJobInfo'),
           'getOwnJobInfo' => CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'getOwnJobInfo'),
          ),
        'FieldOptions' => CRM_HRAbsence_Page_EmployeeAbsencePage::getFieldOptions(),
        'absenceApp' => array(
          'contactId' => $contactID,
          'activityTypes' => $activityTypes,
          'absenceTypes' => $absenceTypes,
          'legend' => $legend->getMap(),
          'periods' => $periods,
          'sortPeriods' => $sortPeriods,
          'standardDay' => 8 * 60,
          'apiTsFmt' => 'YYYY-MM-DD HH:mm:ss',
        ),
      );
    })
      ->addScriptFile('civicrm', 'packages/momentjs/moment.min.js', 100, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/json2.js', 100, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.js', 120, 'html-header')
      ->addScriptFile('civicrm', 'packages/backbone/backbone.marionette.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.modelbinder.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'js/crm.backbone.js', 130, 'html-header', FALSE)
      ->addStyleFile('org.civicrm.hrabsence', 'css/hrabsence.css', 140, 'html-header')
      ->addStyleFile('org.civicrm.hrabsence', 'css/jquery.multiselect.css', 140, 'html-header')
      ->addScriptFile('org.civicrm.hrabsence', 'js/jquery.multiselect.js', 140, 'html-header');

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


  public static function checkPermissions($contactID, $case=NULL) {
    $session = CRM_Core_Session::singleton();
    $cid = $session->get('userID');
    $aclPerm = CRM_HRAbsence_Form_AbsenceRequest::isContactAccessible($contactID);
    switch ($case) {
    case 'viewWidget'://view widget
      if (CRM_Core_Permission::check('administer CiviCRM') ||
        (CRM_Core_Permission::check('view HRAbsences') && $aclPerm ) ||
        (CRM_Core_Permission::check('edit HRAbsences') && $aclPerm ) ||
        (CRM_Core_Permission::check('manage own HRAbsences') && $cid == $contactID)) {
        return TRUE;
      }
      break;
    case 'enableNewAbsence': //enable new absence
      if (CRM_Core_Permission::check('administer CiviCRM') ||
        (CRM_Core_Permission::check('edit HRAbsences') && $aclPerm == CRM_Core_Permission::EDIT) ||
        (CRM_Core_Permission::check('manage own HRAbsences') && $cid == $contactID)) {
        return TRUE;
      }
      break;
    case 'enableEntitlements': //enable entitlements
      if (CRM_Core_Permission::check('administer CiviCRM') ||
        (CRM_Core_Permission::check('edit HRAbsences') && $aclPerm == CRM_Core_Permission::EDIT)) {
        return TRUE;
      }
      break;
    case 'getJobInfo': //Job related api get info
      if (CRM_Core_Permission::check('administer CiviCRM') ||
        CRM_Core_Permission::check('access HRJobs')) {
        return TRUE;
      }
      break;
    case 'getOwnJobInfo': //Own job related api get info
      if (CRM_Core_Permission::check('administer CiviCRM') ||
        (CRM_Core_Permission::check('access HRJobs') || CRM_Core_Permission::check('access own HRJobs') && $aclPerm)) {
        return TRUE;
      }
      break;
    }
      return FALSE;
    }

  }
