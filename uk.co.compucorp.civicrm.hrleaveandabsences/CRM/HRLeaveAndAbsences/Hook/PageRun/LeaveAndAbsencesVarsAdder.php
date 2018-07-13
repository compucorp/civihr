<?php

class CRM_HRLeaveAndAbsences_Hook_PageRun_LeaveAndAbsencesVarsAdder {
  private $resources;

  public function __construct(CRM_Core_Resources $resources) {
    $this->resources = $resources;
  }

  /**
   * Adds variables needed by specific Leave and Absences pages.
   *
   * @param CRM_Core_Page $page
   */
  public function handle($page) {
    if (!$this->shouldHandle($page)) {
      return;
    }

    $this->resources->addVars('leaveAndAbsences', [
      'attachmentToken' => CRM_Core_Page_AJAX_Attachment::createToken(),
      'baseURL' => CRM_Core_Resources::singleton()->getUrl('uk.co.compucorp.civicrm.hrleaveandabsences'),
      'contactId' => CRM_Utils_Request::retrieve('cid', 'Integer'),
      'loggedInUserId' => CRM_Core_Session::getLoggedInContactID(),
    ]);
  }

  /**
   * Determines if the hook should add the variables depending on the page
   * being requested.
   *
   * @param CRM_Core_Page $page
   */
  public function shouldHandle($page) {
    $pageClassName = get_class($page);
    $pagesWhereTheVarIsDefined = [
      'CRM_HRLeaveAndAbsences_Page_AbsenceTab',
      'CRM_HRLeaveAndAbsences_Page_Dashboard',
      'CRM_HRLeaveAndAbsences_Page_LeaveRequestCalendarFeedConfig',
    ];

    return in_array($pageClassName, $pagesWhereTheVarIsDefined);
  }

}
