<?php

class CRM_HRCore_Menu_Child_CustomizeData {

  /**
   * Returns menu Items for Custom Data and Screens Menu.
   *
   * @return array
   */
  public static function getItems() {
    return [
      'Custom Fields' =>
        [
          'url' => 'civicrm/admin/custom/group?reset=1',
          'permission' => 'administer CiviCRM',
        ],
      'Profiles' =>
        [
          'url' => 'civicrm/admin/uf/group?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Tags (Categories)' =>
        [
          'url' => 'civicrm/tag',
          'permission' => 'access root menu items and configurations',
        ],
      'Activity Types' =>
        [
          'url' => 'civicrm/admin/options/activity_type?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Relationship Types' =>
        [
          'url' => 'civicrm/admin/reltype?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Contact Types' =>
        [
          'url' => 'civicrm/admin/options/subtype?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Dropdown Options' =>
        [
          'permission' => 'access root menu items and configurations',
          'children' =>
            [
              'Gender Options' =>
                [
                  'url' => 'civicrm/admin/options/gender?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Individual Prefixes (Ms, Mr...)' =>
                [
                  'url' => 'civicrm/admin/options/individual_prefix?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Individual Suffixes (Jr, Sr...)' =>
                [
                  'url' => 'civicrm/admin/options/individual_suffix?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Instant Messenger Services' =>
                [
                  'url' => 'civicrm/admin/options/instant_messenger_service?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Location Types (Home, Work...)' =>
                [
                  'url' => 'civicrm/admin/locationType?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Mobile Phone Providers' =>
                [
                  'url' => 'civicrm/admin/options/mobile_provider?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Phone Types' =>
                [
                  'url' => 'civicrm/admin/options/phone_type?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Website Types' =>
                [
                  'url' => 'civicrm/admin/options/website_type?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Hours Types' =>
                [
                  'url' => 'civicrm/hour/editoption',
                  'permission' => 'access root menu items and configurations',
                ],
              'Job Contract Pay Scale' =>
                [
                  'url' => 'civicrm/pay_scale',
                  'permission' => 'access root menu items and configurations',
                ],
              'Job Contract Hours/Location' =>
                [
                  'url' => 'civicrm/hours_location',
                  'permission' => 'access root menu items and configurations',
                ],
              'Contract Type' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_contract_type?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Normal place of work' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_location?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Pay cycle' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_pay_cycle?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Benefits' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_benefit_name?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Benefit type' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_benefit_type?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Deductions' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_deduction_name?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Deduction type' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_deduction_type?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Reason for change' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_revision_change_reason?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
              'Reason for Job Contract end' =>
                [
                  'url' => 'civicrm/admin/options/hrjc_contract_end_reason?reset=1',
                  'permission' => 'access root menu items and configurations',
                ],
            ],
        ],
      'Display Preferences' =>
        [
          'url' => 'civicrm/admin/setting/preferences/display?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Search Preferences' =>
        [
          'url' => 'civicrm/admin/setting/search?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Date Preferences' =>
        [
          'url' => 'civicrm/admin/setting/preferences/date?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Navigation Menu' =>
        [
          'url' => 'civicrm/admin/menu?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Word Replacements' =>
        [
          'url' => 'civicrm/admin/options/wordreplacements?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
      'Manage Custom Searches' =>
        [
          'url' => 'civicrm/admin/options/custom_search?reset=1',
          'permission' => 'access root menu items and configurations',
        ],
    ];
  }
}
