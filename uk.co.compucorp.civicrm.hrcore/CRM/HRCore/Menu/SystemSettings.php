<?php

class CRM_HRCore_Menu_SystemSettings {

  /**
   * Returns the children menu Items for System settings.
   *
   * @return array
   */
  public static function getItems() {
    $menuItems = [
      [
        'attributes' =>
          [
            'label' => 'Components',
            'url' => 'civicrm/admin/setting/component?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Connections',
            'url' => 'civicrm/a/#/cxn',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Extensions',
            'url' => 'civicrm/admin/extensions?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
            'separator' => '1',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Cleanup Caches and Update Paths',
            'url' => 'civicrm/admin/setting/updateConfigBackend?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'CMS Database Integration',
            'url' => 'civicrm/admin/setting/uf?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Debugging and Error Handling',
            'url' => 'civicrm/admin/setting/debug?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Directories',
            'url' => 'civicrm/admin/setting/path?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Import/Export Mappings',
            'url' => 'civicrm/admin/mapping?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Mapping and Geocoding',
            'url' => 'civicrm/admin/setting/mapping?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Misc (Undelete, PDFs, Limits, Logging, Captcha, etc.)',
            'url' => 'civicrm/admin/setting/misc?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Multi Site Settings',
            'url' => 'civicrm/admin/setting/preferences/multisite?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Option Groups',
            'url' => 'civicrm/admin/options?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Outbound Email (SMTP/Sendmail)',
            'url' => 'civicrm/admin/setting/smtp?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Payment Processors',
            'url' => 'civicrm/admin/paymentProcessor?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Resource URLs',
            'url' => 'civicrm/admin/setting/url?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Safe File Extensions',
            'url' => 'civicrm/admin/options/safe_file_extension?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Scheduled Jobs',
            'url' => 'civicrm/admin/job?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'SMS Providers',
            'url' => 'civicrm/admin/sms/provider?reset=1',
            'permission' => 'access root menu items and configurations',
            'operator' => '',
          ],
      ],
    ];

    return $menuItems;
  }
}
