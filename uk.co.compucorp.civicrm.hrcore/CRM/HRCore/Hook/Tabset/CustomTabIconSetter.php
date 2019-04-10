<?php

class CRM_HRCore_Hook_Tabset_CustomTabIconSetter {
  /**
   * Sets custom icons for multiple different tabs in the
   * contact summary page
   *
   * @param string $tabsetName
   * @param array $tabs
   * @param array $context
   */
  public function handle($tabsetName, &$tabs, $context) {
    if (!$this->shouldHandle($tabsetName)) {
      return;
    }

    $tabIcons = [
      'activity' => 'fa-users',
      'summary' => 'fa-puzzle-piece',
      'rel' => 'fa-sitemap',
      'note' => 'fa-pencil',
      'log' => 'fa-archive'
    ];

    foreach ($tabs as $key => $tab) {
      if (!empty($tabIcons[$tab['id']])) {
        $tabs[$key]['icon'] = 'crm-i ' . $tabIcons[$tab['id']];
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
    if ($tabsetName === 'civicrm/contact/view') {
      return TRUE;
    }

    return FALSE;
  }
}
