<?php

class CRM_HRCore_Hook_PageRun_LocationTypeFilter {

  /**
   * Removes certain location types from the admin edit page for them
   *
   * @param CRM_Core_Page $page
   */
  public function handle($page) {
    if (!$this->shouldHandle($page)) {
      return;
    }

    $rows = $page->get_template_vars('rows');
    // remove the "Billing" location type
    foreach ($rows as $index => $row) {
      if (CRM_Utils_Array::value('name', $row) === 'Billing') {
        unset($rows[$index]);
      }
    }
    $page->assign('rows', $rows);
  }

  /**
   * Checks if this is the right page
   *
   * @param CRM_Core_Page $page
   *
   * @return bool
   */
  public function shouldHandle($page) {
    return $page instanceof CRM_Admin_Page_LocationType;
  }

}
