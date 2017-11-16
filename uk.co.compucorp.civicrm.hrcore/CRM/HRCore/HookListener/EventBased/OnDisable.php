<?php

class CRM_HRCore_HookListener_EventBased_OnDisable extends CRM_HRCore_HookListener_BaseListener {

  public function handle() {
    $this->setActiveFields(TRUE);
    $this->wordReplacement(TRUE);
    $this->menuSetActive(0);
  }
}
