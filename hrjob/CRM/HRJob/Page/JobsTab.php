<?php

require_once 'CRM/Core/Page.php';

class CRM_HRJob_Page_JobsTab extends CRM_Core_Page {
  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('JobsTab'));

    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));

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
        'jobTabApp' => array(
          'contact_id' => CRM_Utils_Request::retrieve('cid', 'Integer')
        ),
      );
    })
      ->addScriptFile('civicrm', 'packages/backbone/json2.js', 100, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/underscore.js', 110, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.js', 120, 'html-header')
      ->addScriptFile('civicrm', 'packages/backbone/backbone.marionette.js', 125, 'html-header', FALSE)
      //->addScriptFile('civicrm', 'packages/jquery/plugins/jstree/jquery.jstree.js', 0, 'html-header', FALSE)
      //->addStyleFile('civicrm', 'packages/jquery/plugins/jstree/themes/default/style.css', 0, 'html-header')
      ->addStyleFile('org.civicrm.hrjob', 'css/hrjob.css', 140, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/hrapp.js', 150, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/entities/hrjob.js', 155, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/intro/show_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/intro/show_views.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/tree/tree_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/tree/tree_views.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/summary/summary_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/summary/summary_views.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/general/edit_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/general/edit_views.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/role/edit_controller.js', 160, 'html-header')
      ->addScriptFile('org.civicrm.hrjob', 'js/jobtabapp/role/edit_views.js', 160, 'html-header')
      ;
    /*
      ->addScriptFile('civicrm', 'js/crm.backbone.js', 150)
      ->addScriptFile('civicrm', 'js/model/crm.schema-mapped.js', 200)
      ->addScriptFile('civicrm', 'js/model/crm.uf.js', 200)
      ->addScriptFile('civicrm', 'js/model/crm.designer.js', 200)
      ->addScriptFile('civicrm', 'js/model/crm.profile-selector.js', 200)
      ->addScriptFile('civicrm', 'js/view/crm.designer.js', 200)
      ->addScriptFile('civicrm', 'js/view/crm.profile-selector.js', 200)
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmProfileSelector.js', 250)
      ->addScriptFile('civicrm', 'js/crm.designerapp.js', 250)
    */

    CRM_Core_Region::instance('page-header')->add(array(
      'template' => 'CRM/HRJob/Page/JSTemplates.tpl',
    ));
  }

}
