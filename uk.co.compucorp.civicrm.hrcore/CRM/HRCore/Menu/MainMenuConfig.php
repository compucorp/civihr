<?php

class CRM_HRCore_Menu_MainMenuConfig implements CRM_HRCore_Menu_Config {

  /**
   * Returns menu Items for Main navigation menu Items.
   *
   * @return array
   */
  public function getItems() {
    return include 'Config/Main.php';
  }
}
