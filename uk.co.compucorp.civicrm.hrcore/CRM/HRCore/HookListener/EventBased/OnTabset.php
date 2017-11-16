<?php

class CRM_HRCore_HookListener_EventBased_OnTabset extends CRM_HRCore_HookListener_BaseListener {

  public function handle($tabsetName, &$tabs, $contactID) {
    $tabsToRemove = array();

    if ($this->isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments')) {
      $tabsToRemove[] = 'case';
    }

    $this->alterTabs($tabs, $tabsToRemove);
  }

  /**
   * 1) we alter the weights for these tabs here
   * since these tabs are not created by hook_civicrm_tab
   * and the only way to alter their weights is here
   * by taking advantage of &$tabs variable.
   * 2) we set assignments tab to 30 since it should appear
   * after appraisals tab directly which have the weight of 20.
   * 3) the weight increased by 10 between every tab
   * to give a large space for other tabs to be inserted
   * between any two without altering other tabs weights.
   * 4) we remove a tab if present in the $tabsToRemove list
   *
   * @param array $tabs
   * @param array $tabsToRemove
   */
  private function alterTabs(&$tabs, $tabsToRemove) {
    foreach ($tabs as $i => $tab) {
      if (in_array($tab['id'], $tabsToRemove)) {
        unset($tabs[$i]);
        continue;
      }

      switch($tab['title'])  {
        case 'Assignments':
          $tabs[$i]['weight'] = 30;
          break;
        case 'Emergency Contacts':
          $tabs[$i]['weight'] = 80;
          break;
        case 'Relationships':
          $tabs[$i]['weight'] = 90;
          $tabs[$i]['title'] = 'Managers';
          break;
        case 'Bank Details':
          $tabs[$i]['weight'] = 100;
          break;
        case 'Career History':
          $tabs[$i]['weight'] = 110;
          break;
        case 'Medical & Disability':
          $tabs[$i]['weight'] = 120;
          break;
        case 'Qualifications':
          $tabs[$i]['weight'] = 130;
          break;
        case 'Notes':
          $tabs[$i]['weight'] = 140;
          break;
        case 'Groups':
          $tabs[$i]['weight'] = 150;
          break;
        case 'Change Log':
          $tabs[$i]['weight'] = 160;
          break;
      }
    }
  }
}
