<?php

class  CRM_HRCore_Hook_Tabset_ActivityTabModifier {
  /**
   * Determines what happens if the hook is handled.
   * Basically, renames the activity tab title on contact
   * summary page to 'Communications'
   *
   * @param string $tabsetName
   * @param array $tabs
   * @param array $context
   */
  public function handle($tabsetName, &$tabs, $context) {
    if (!$this->shouldHandle($tabsetName)) {
      return;
    }

    foreach($tabs as $key => $tab) {
      if($tab['id'] == 'activity') {
        $tabs[$key]['title'] = 'Communications';
      }
    }
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param string $tabsetName
   *
   * @return bool
   */
  private function shouldHandle($tabsetName) {
    if ($tabsetName == 'civicrm/contact/view') {
      return TRUE;
    }

    return FALSE;
  }
}
