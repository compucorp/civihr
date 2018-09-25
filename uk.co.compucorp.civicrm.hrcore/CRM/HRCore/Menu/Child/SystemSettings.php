<?php

class CRM_HRCore_Menu_Child_SystemSettings {

  public static function getItems() {
    return [
      'Components' =>
        [
          'url' => 'civicrm/admin/setting/component?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Connections' =>
        [
          'url' => 'civicrm/a/#/cxn',
          'permission' => 'access root menu items and configurations',
        ],
      'Extensions' =>
        [
          'url' => 'civicrm/admin/extensions?reset=1',
          'permission' => 'access root menu items and configurations',
          'separator' => '1',
        ],
      'Cleanup Caches and Update Paths' =>
        [
          'url' => 'civicrm/admin/setting/updateConfigBackend?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'CMS Database Integration' =>
        [
          'url' => 'civicrm/admin/setting/uf?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Debugging and Error Handling' =>
        [
          'url' => 'civicrm/admin/setting/debug?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Directories' =>
        [
          'url' => 'civicrm/admin/setting/path?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Import/Export Mappings' =>
        [
          'url' => 'civicrm/admin/mapping?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Mapping and Geocoding' =>
        [
          'url' => 'civicrm/admin/setting/mapping?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Misc (Undelete, PDFs, Limits, Logging, Captcha, etc.)' =>
        [
          'url' => 'civicrm/admin/setting/misc?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Multi Site Settings' =>
        [
          'url' => 'civicrm/admin/setting/preferences/multisite?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Option Groups' =>
        [
          'url' => 'civicrm/admin/options?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Outbound Email (SMTP/Sendmail)' =>
        [
          'url' => 'civicrm/admin/setting/smtp?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Payment Processors' =>
        [
          'url' => 'civicrm/admin/paymentProcessor?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Resource URLs' =>
        [
          'url' => 'civicrm/admin/setting/url?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Safe File Extensions' =>
        [
          'url' => 'civicrm/admin/options/safe_file_extension?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Scheduled Jobs' =>
        [
          'url' => 'civicrm/admin/job?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'SMS Providers' =>
        [
          'url' => 'civicrm/admin/sms/provider?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
    ];
  }
}
