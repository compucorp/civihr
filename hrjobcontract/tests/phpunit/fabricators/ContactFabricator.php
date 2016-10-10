<?php

  class ContactFabricator {

    private static $default = [
      'contact_type' => 'Individual',
      'first_name' => 'John',
      'last_name' => 'Doe',
      'sequential' => 1
    ];

    public static function fabricate($params) {
      $params = array_merge(self::$default, $params);
      $params['display_name'] = "{$params['first_name']} {$params['last_name']}";

      return civicrm_api3('Contact', 'create', $params)['values'][0];
    }
  }
