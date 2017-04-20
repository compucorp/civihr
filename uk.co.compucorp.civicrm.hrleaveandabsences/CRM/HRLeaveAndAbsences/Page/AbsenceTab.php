<?php

/**
 * CRM_HRLeaveAndAbsences_Page_AbsenceTab
 */
class CRM_HRLeaveAndAbsences_Page_AbsenceTab extends  CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(ts('Absence'));

    CRM_Core_Resources::singleton()
      ->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css')
      ->addScriptFile('org.civicrm.reqangular', 'dist/reqangular.min.js', 1010)
      ->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences',
        CRM_Core_Config::singleton()->debug ? 'js/angular/src/absence-tab.js' : 'js/angular/dist/absence-tab.min.js', 1010)
      ->addSettingsFactory(function () {
        return array(
          'absenceTabApp' => array(
            'contactId' => CRM_Utils_Request::retrieve('cid', 'Integer'),
            'path' => CRM_Core_Resources::singleton()->getUrl('uk.co.compucorp.civicrm.hrleaveandabsences'),
          )
        );
      });

    parent::run();
  }
}
