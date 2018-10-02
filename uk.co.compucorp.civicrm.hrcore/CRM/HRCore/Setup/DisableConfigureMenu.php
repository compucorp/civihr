<?php

class CRM_HRCore_Setup_DisableConfigureMenu {

  public function apply() {
    civicrm_api3('Navigation', 'get', [
      'name' => 'configure',
      'api.Navigation.create' => ['id' => '$value.id']
    ]);
  }

}
