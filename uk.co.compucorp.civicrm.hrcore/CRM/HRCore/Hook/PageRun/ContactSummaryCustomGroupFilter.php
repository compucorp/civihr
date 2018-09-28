<?php

class CRM_HRCore_Hook_PageRun_ContactSummaryCustomGroupFilter {

  /**
   * Removes certain custom groups from the contact card
   * display on the contact summary page on the personal
   * details tab so that the custom fields for these custom
   * groups will not be displayed. Presently, only the
   * Contact_Length_Of_Service custom group is filtered out.
   *
   * @param CRM_Core_Page $page
   */
  public function handle($page) {
    if (!$this->shouldHandle($page)) {
      return;
    }

    $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => 'Contact_Length_Of_Service',
    ]);

    if (!$customGroup['id']) {
      return;
    }

    $customData = $page->get_template_vars('viewCustomData');
    //remove the Contact_Length_Of_Service from the custom group list.
    unset($customData[$customGroup['id']]);
    $page->assign('viewCustomData', $customData);
  }


  /**
   * Checks if this is the right page
   *
   * @param CRM_Core_Page $page
   *
   * @return bool
   */
  public function shouldHandle($page) {
    return $page instanceof CRM_Contact_Page_View_Summary;
  }
}
