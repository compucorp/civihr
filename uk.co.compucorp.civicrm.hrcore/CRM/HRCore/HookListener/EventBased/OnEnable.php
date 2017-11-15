<?php

class CRM_HRCore_HookListener_EventBased_OnEnable extends CRM_HRCore_HookListener_BaseListener {

  public function handle() {
    $this->setActiveFields(FALSE);
    $this->wordReplacement(FALSE);
    $this->menuSetActive(1);
  }
}
