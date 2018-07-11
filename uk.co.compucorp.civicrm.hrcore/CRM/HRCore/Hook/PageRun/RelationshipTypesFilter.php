<?php

class CRM_HRCore_Hook_PageRun_RelationshipTypesFilter {

  /**
   * Hides disabled relationship types in the interface for non-root admins
   *
   * @param CRM_Core_Page $page
   */
  public function handle($page) {
    if (!$this->shouldHandle($page)) {
      return;
    }

    $rows = $page->get_template_vars('rows');
    // remove disabled relationship types
    foreach ($rows as $index => $row) {
      if (CRM_Utils_Array::value('is_active', $row) == '0'&& in_array(CRM_Utils_Array::value('name', $row),['Case Coordinator is','Employee of','Head of Household for','Household member of'])) {
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
    global $user;
    return $page instanceof CRM_Admin_Page_RelationshipType && !in_array('administrator', $user->roles);
  }

}
