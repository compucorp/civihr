<?php

class CRM_HRCore_Page_UserMenu extends CRM_Core_Page {

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->assign('userMenuMarkup', (new CRM_HRCore_UserMenuMarkup())->getMarkup());

    return parent::run();
  }
}
