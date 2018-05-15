<?php

class CRM_HRCore_Test_Fabricator_CustomGroup {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);

    $result = civicrm_api3(
      'CustomGroup',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  private static function getDefaultParams() {
    static $count = 0;
    $count++;

    return [
      'name' => 'test_custom_group_' . $count,
      'title' => 'Test Custom Group ' . $count,
      'extends' => 'Individual'
    ];
  }

}
