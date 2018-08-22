<?php

trait CRM_HRCore_Upgrader_Steps_1031 {

  /**
   * Removes the Search Menu heading
   *
   * @return bool
   */
  public function upgrade_1031() {
    $this->up1031_removeSearchMenuHeading();

    return TRUE;
  }

  /**
   * Removes the Search Menu Heading in Admin Portal
   */
  private function up1031_removeSearchMenuHeading() {
    civicrm_api3('Navigation', 'get', [
      'label' => 'Search',
      'api.Navigation.delete' => ['id' => '$value.id'],
    ]);
  }

}
