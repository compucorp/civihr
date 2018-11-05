<?php

class CRM_HRCore_Hook_Links_ContactCustomActions {

  /**
   * Determines what happens if the hook is handled.
   *
   * @param string $op
   * @param string $objectName
   * @param mixed $objectId
   * @param array $links
   * @param string $mask
   * @param array $values
   */
  public function handle($op, $objectName, $objectId, &$links, &$mask, &$values) {
    if (!$this->shouldHandle($objectName, $op)) {
      return;
    }

    $this->setContactCustomLinks($links);

  }

  /**
   * Checks whether the hook should be handled or not.
   *
   * @param string $objectName
   * @param string $op
   *
   * @return bool
   */
  private function shouldHandle($objectName, $op) {
    return $objectName == 'Contact' && $op == 'contact.custom.actions';
  }

  /**
   * This overrides the custom contact actions for individual contacts on
   * custom search pages.
   *
   * @param array $links
   */
  private function setContactCustomLinks(&$links) {
    $links = [
      [
        'name' => 'View',
        'url' => 'civicrm/contact/view',
        'class' => 'no-popup',
        'qs' => 'reset=1&cid=%%id%%',
        'title' => 'View Staff Details',
      ],
      [
        'name' => 'Edit',
        'url' => 'civicrm/contact/add',
        'class' => 'no-popup',
        'qs' => 'reset=1&action=update&cid=%%id%%',
        'title' => 'Edit Staff Details',
      ],
      [
        'name' => 'Record Leave',
        'url' => 'civicrm/contact/view',
        'class' => 'no-popup',
        'qs' => 'reset=1&cid=%%id%%&selectedChild=absence&openModal=leave',
        'title' => 'Record Leave',
      ],
      [
        'name' => 'Record Sickness',
        'url' => 'civicrm/contact/view',
        'class' => 'no-popup',
        'qs' => 'reset=1&cid=%%id%%&selectedChild=absence&openModal=sickness',
        'title' => 'Record Sickness',
      ],
      [
        'name' => 'Record Overtime',
        'url' => 'civicrm/contact/view',
        'qs' => 'reset=1&cid=%%id%%&selectedChild=absence&openModal=toil',
        'title' => 'Record Overtime',
        'class' => 'no-popup',
      ],
      [
        'name' => 'Add Task',
        'url' => 'civicrm/tasksassignments/dashboard',
        'qs' => 'reset=1&cid=%%id%%&openModal=task',
        'title' => 'Add Task',
        'class' => 'no-popup',
      ],
      [
        'name' => 'Add Document',
        'url' => 'civicrm/tasksassignments/dashboard',
        'qs' => 'reset=1&cid=%%id%%&openModal=document',
        'title' => 'Add Document',
        'class' => 'no-popup',
        'bit' => 7002,
      ],
      [
        'name' => 'Add Workflow',
        'url' => 'civicrm/tasksassignments/dashboard',
        'qs' => 'reset=1&cid=%%id%%&openModal=assignment',
        'title' => 'Add Workflow',
        'ref' => 'new-activity',
        'class' => 'no-popup',
      ],
      [
        'name' => 'Delete Staff',
        'url' => 'civicrm/contact/view/delete',
        'qs' => 'reset=1&delete=&cid=%%id%%',
        'title' => 'Delete Staff',
        'class' => 'no-popup',
      ],
      [
        'name' => 'Delete Staff Permanently',
        'url' => 'civicrm/contact/view/delete',
        'qs' => 'reset=1&delete=&cid=%%id%%&skip_undelete=1',
        'title' => 'Delete Staff Permanently',
        'class' => 'no-popup',
      ],
    ];
  }
}
