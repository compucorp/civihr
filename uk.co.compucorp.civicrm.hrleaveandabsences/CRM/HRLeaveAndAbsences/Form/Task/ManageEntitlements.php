<?php

/**
 * This class provides the functionality to manage the leave entitlements for the
 * selected contacts.
 */
class CRM_HRLeaveAndAbsences_Form_Task_ManageEntitlements extends CRM_Contact_Form_Task {

  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    // initialize the task and row fields
    parent::preProcess();
    CRM_Utils_System::setTitle('Manage leave entitlements');
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    $this->setPreviousUrl();
    $this->add(
      'select',
      'absence_period',
      ts('Select Period'),
      $this->getAbsencePeriods(),
      true,
      ['placeholder' => true]
    );

    $this->addDefaultButtons(ts('Next'));
  }

  /**
   * {@inheritdoc}
   */
  public function postProcess() {
    $returnUrl = '';
    $session = CRM_Core_Session::singleton();
    if ($session->get('AdvancedSearchManageEntitlementsReturnUrl')) {
      $returnUrl = $session->get('AdvancedSearchManageEntitlementsReturnUrl');
      $session->set('AdvancedSearchManageEntitlementsReturnUrl', null);
    }
    $absencePeriodId = $this->exportValue('absence_period');

    $url = CRM_Utils_System::url(
      'civicrm/admin/leaveandabsences/periods/manage_entitlements',
      http_build_query([
        'id' => $absencePeriodId,
        'cid' => $this->_contactIds,
        'reset' => 1,
        'returnUrl' => $returnUrl
      ])
    );

    CRM_Utils_System::redirect($url);
  }

  /**
   * Returns a list of Absence Periods to be listed in the "Select Period"
   * dropdown. The returned list is an array in the ['id' => 'title'] format.
   *
   * @return array
   */
  private function getAbsencePeriods() {
    $object = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $object->orderBy('weight');
    $object->find();

    $rows = [];
    while($object->fetch()) {
      $rows[$object->id] = $object->title;
    }

    return $rows;
  }

  /**
   * This method builds and saves the URL the user was before coming to this form
   * to the session
   */
  private function setPreviousUrl() {
    if (isset($_GET['qfKey'])) {
      $session = CRM_Core_Session::singleton();

      $returnUrl = CRM_Utils_System::url(
        'civicrm/contact/search/advanced',
        http_build_query([
          '_qf_Advanced_display' => true,
          'qfKey' => $_GET['qfKey'],
        ])
      );

      $session->set('AdvancedSearchManageEntitlementsReturnUrl', $returnUrl);
    }
  }

}
