<?php

trait CRM_HRCore_Test_Helpers_DomainConfigurationTrait {

  /**
   * @param $fromEmail
   * @param $name
   */
  private function setDomainFromAddress($fromEmail, $name) {
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'from_email_address',
      'name' => sprintf('"%s" <%s>', $name, $fromEmail),
      'is_default' => 1,
    ]);
  }

}
