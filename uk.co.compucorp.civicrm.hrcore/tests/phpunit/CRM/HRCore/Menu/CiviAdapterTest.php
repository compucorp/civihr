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
class CRM_HRCore_Helper_Menu_CiviAdapterTest extends BaseHeadlessTest {

  public function testGetNavigationTreeReturnsMenuItemsInFormatCiviExpects() {
    $menuConfig = $this->prophesize(MenuConfig::class);
    $menuConfig->getItems()->willReturn($this->getMenuConfigItems());
    $menuBuilder = new MenuBuilder;
    $civiHRMenuItems = $menuBuilder->getMenuItems($menuConfig->reveal());
    $civiNavigationItems = CiviAdapter::getNavigationTree($civiHRMenuItems);
    $this->assertEquals($this->getExpectedMenuItems(), $civiNavigationItems);
  }

  public function testGetNavigationTreeThrowsAnExceptionWhenItemsProvidedAreNotMenuItemObjects() {
    $civiHRMenuItems = [
      [
        'name' => 'Sample Name',
        'label' => 'Sample Label',
        'icon' => 'test'
      ]
    ];
    $this->setExpectedException(
      RuntimeException::class,
      'Menu Item should be an instance of '.  CRM_HRCore_Menu_Item::class
    );

    CiviAdapter::getNavigationTree($civiHRMenuItems);
  }

  private function getMenuConfigItems() {
    return [
      'Staff' => [
        'icon'=> 'crm-i fa-users',
        'children' => [
          'New Individual' => [
            'permission' => 'administer CiviCRM',
            'url' => 'civicrm/new_individual',
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

  private function getExpectedMenuItems() {
    return [
      [
        'attributes' => [
          'label' => 'Staff',
          'name' => 'Staff',
          'url' => NULL,
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
