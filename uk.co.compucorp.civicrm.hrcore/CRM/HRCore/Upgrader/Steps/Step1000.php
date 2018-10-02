<?php

class CRM_HRCore_Upgrader_Step_Step1000 {

  public function apply() {
    (new CRM_HRCore_Setup_DisableConfigureMenu())->apply();

    return true;
  }

}
