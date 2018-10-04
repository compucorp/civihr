<?php

use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Menu_Config as MenuConfig;
use CRM_HRCore_Menu_MenuBuilder as MenuBuilder;
use CRM_HRCore_Menu_CiviAdapter as CiviAdapter;

/**
 * Class CRM_HRCore_Helper_Menu_ParserTest
 *
 * @group headless
 */
class CRM_HRCore_Menu_CiviAdapterTest extends BaseHeadlessTest {

  public function testGetNavigationTreeReturnsMenuItemsInFormatCiviExpects() {
    $menuConfig = $this->prophesize(MenuConfig::class);
    $menuConfig->getItems()->willReturn($this->getMenuConfigItems());
    $menuBuilder = new MenuBuilder;
    $menuObject = $menuBuilder->getMenuItems($menuConfig->reveal());
    $civiAdapter = new CiviAdapter();
    $civiNavigationItems = $civiAdapter->getNavigationTree($menuObject);
    $this->assertEquals($this->getExpectedMenuItems(), $civiNavigationItems);
  }

  private function getMenuConfigItems() {
    return [
      'Staff' => [
        'icon'=> 'crm-i fa-users',
        'children' => [
          'New Individual' => [
            'permission' => 'administer CiviCRM',
            'url' => 'civicrm/new_individual',
            'target' => '_blank',
            'separator' => 1
          ],
        ],
      ],
      'New Organization' => [
        'children' => [
          'New Life Insurance Provider' => 'civicrm/provider',
          'Health Provider' => [
            'children' => [
              'Custom Records' => 'civicrm/sample/bla',
            ]
          ],
        ],
      ]
    ];
  }

  /**
   * The weight is expected to be in incremental order (+1) for each navigation level
   * starting at 1.
   * Top level navigation menu items are expected to have a parentID of 0 while inner level
   * navigation items/children will have their parentID to be equal to the navID of their
   * parent.
   * The navID increases sequentially for each menu item by 1, starting from the first
   * top level menu down to its children, and then on to the next top level menu down to its
   * children and so on.
   *
   * @return array
   */
  private function getExpectedMenuItems() {
    return [
      [
        'attributes' => [
          'label' => 'Staff',
          'name' => 'Staff',
          'url' => NULL,
          'target' => NULL,
          'icon' => 'crm-i fa-users',
          'weight' => 1,
          'permission' => NULL,
          'operator' => NULL,
          'separator' => NULL,
          'navID' => 1,
          'parentID' => 0,
          'active' => 1,
        ],
        'child' => [
          [
            'attributes' => [
              'label' => 'New Individual',
              'name' => 'New Individual',
              'url' => 'civicrm/new_individual',
              'target' => '_blank',
              'icon' => NULL,
              'weight' => 1,
              'permission' => 'administer CiviCRM',
              'operator' => NULL,
              'separator' => 1,
              'navID' => 2,
              'parentID' => 1,
              'active' => 1,
            ],
          ],
        ],
      ],
      [
        'attributes' => [
          'label' => 'New Organization',
          'name' => 'New Organization',
          'url' => NULL,
          'target' => NULL,
          'icon' => NULL,
          'weight' => 2,
          'permission' => NULL,
          'operator' => NULL,
          'separator' => NULL,
          'navID' => 3,
          'parentID' => 0,
          'active' => 1,
        ],
        'child' => [
          [
            'attributes' => [
              'label' => 'New Life Insurance Provider',
              'name' => 'New Life Insurance Provider',
              'url' => 'civicrm/provider',
              'target' => NULL,
              'icon' => NULL,
              'weight' => 1,
              'permission' => NULL,
              'operator' => NULL,
              'separator' => NULL,
              'navID' => 4,
              'parentID' => 3,
              'active' => 1,
            ],
          ],
          [
            'attributes' => [
              'label' => 'Health Provider',
              'name' => 'Health Provider',
              'url' => NULL,
              'target' => NULL,
              'icon' => NULL,
              'weight' => 2,
              'permission' => NULL,
              'operator' => NULL,
              'separator' => NULL,
              'navID' => 5,
              'parentID' => 3,
              'active' => 1,
            ],
            'child' => [
              [
                'attributes' => [
                  'label' => 'Custom Records',
                  'name' => 'Custom Records',
                  'url' => 'civicrm/sample/bla',
                  'target' => NULL,
                  'icon' => NULL,
                  'weight' => 1,
                  'permission' => NULL,
                  'operator' => NULL,
                  'separator' => NULL,
                  'navID' => 6,
                  'parentID' => 5,
                  'active' => 1,
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }
}
