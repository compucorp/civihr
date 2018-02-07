<?php

class CRM_HRCore_Page_Reports extends CRM_Core_Page {

  /**
   * {@inheritdoc}
   */
  public function run() {
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/crm/vendor/iframeResizer.min.js');

    return parent::run();
  }
}
