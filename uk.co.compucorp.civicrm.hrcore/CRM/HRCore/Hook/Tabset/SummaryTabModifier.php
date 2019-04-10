<?php

class CRM_HRCore_Hook_Tabset_SummaryTabModifier {
  /**
   * Applies modifications to the Summary tab of the
   * Contact Summary page.
   *
   * @param string $tabsetName
   * @param array $tabs
   * @param array $context
   */
  public function handle($tabsetName, &$tabs, $context) {
    if (!$this->shouldHandle($tabsetName, $context)) {
      return;
    }

    foreach ($tabs as $key => $tab) {
      if ($tab['id'] === 'summary') {
        $tabs[$key]['title'] = 'Personal Details';
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
  private function shouldHandle($tabsetName, $context) {
    if ($tabsetName === 'civicrm/contact/view' && $this->contactIsIndividual($context)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Checks whether the contact in the given $context is an
   * Individual or not
   *
   * @param $context
   *
   * @return bool
   */
  private function contactIsIndividual($context) {
    if (empty($context['contact_id'])) {
      return FALSE;
    }

    try {
      $result = civicrm_api3('Contact', 'getSingle', [
        'id' => $context['contact_id'],
        'contact_type' => 'Individual'
      ]);

      // Since we're using getsigle, the API will return an error if there is no
      // matching contact
      return empty($result['is_error']);
    } catch(\Exception $e) {
      return FALSE;
    }
  }
}
