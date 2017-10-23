<?php

class CRM_HRCore_Page_UserMenu extends CRM_Core_Page {

  /**
   * {@inheritdoc}
   */
  public function run() {
    $userMenu = new CRM_HRCore_UserMenuMarkup();
    $this->assign('userMenuMarkup', $userMenu->getMarkup());

    return parent::run();
  }
}
