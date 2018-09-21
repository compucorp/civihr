<?php

use CRM_HRCore_Menu_Config as MenuConfig;
use CRM_HRCore_Helper_Menu_CiviHr as CiviHrMenuHelper;
use CRM_HRCore_Menu_Item as MenuItem;

/**
 * @group headless
 */
class CRM_HRCore_Helper_Menu_CiviHrTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testGetMenuItems() {
    $menuConfig = $this->prophesize(MenuConfig::class);
    $menuConfig->getItems()->willReturn($this->getMenuConfigItems());
    $civiHrMenuHelper = new CiviHrMenuHelper();
    $menuItemObjects = $civiHrMenuHelper->getMenuItems($menuConfig->reveal());

    //Two top menu item levels
    $this->assertCount(2, $menuItemObjects);
    $topLevelMenu1 = $menuItemObjects[0];
    $topLevelMenu2 = $menuItemObjects[1];

    //verify top level menu1 and children properties
    $this->verifyMenuItemProperties($topLevelMenu1, ['label' => 'Staff', 'icon' => 'crm-i fa-users']);
    $topLevelMenu1Children = $topLevelMenu1->getChildren();
    $this->assertCount(1, $topLevelMenu1Children);
    $topLevelMenu1Child1 = $topLevelMenu1Children[0];

    //verify that it points to the right parent.
    $this->assertEquals($topLevelMenu1, $topLevelMenu1Child1->getParent());

    $this->verifyMenuItemProperties($topLevelMenu1Child1, [
      'label' => 'New Individual',
      'permission' => 'administer CiviCRM',
      'url' => 'civicrm/new_individual',
      'separator' => TRUE
    ]);

    //verify top level menu2 and children properties
    $this->verifyMenuItemProperties($topLevelMenu2, ['label' => 'New Organization']);
    $topLevelMenu2Children = $topLevelMenu2->getChildren();
    $this->assertCount(2, $topLevelMenu2Children);
    $topLevelMenu2Child1 = $topLevelMenu2Children[0];
    $topLevelMenu2Child2 = $topLevelMenu2Children[1];

    //verify that they point to the right parent.
    $this->assertEquals($topLevelMenu2, $topLevelMenu2Child1->getParent());
    $this->assertEquals($topLevelMenu2, $topLevelMenu2Child2->getParent());

    $this->verifyMenuItemProperties($topLevelMenu2Child1, [
      'label' => 'New Life Insurance Provider',
      'url' => 'civicrm/provider',
    ]);

    $this->verifyMenuItemProperties($topLevelMenu2Child2, [
      'label' => 'Health Provider',
    ]);

    //topLevelMenu2Child2 has a child
    $secondLevelChildren = $topLevelMenu2Child2->getChildren();
    $this->assertCount(1, $secondLevelChildren);
    $secondLevelChild = $secondLevelChildren[0];

    //verify parent
    $this->assertEquals($topLevelMenu2Child2, $secondLevelChild->getParent());

    $this->verifyMenuItemProperties($secondLevelChild, [
      'label' => 'Custom Records',
      'url' => 'civicrm/sample/bla',
    ]);
  }

  private function verifyMenuItemProperties(MenuItem $menuItem, $properties) {
    $defaults = [
      'label' => NULL,
      'url' => NULL,
      'permission' => NULL,
      'operator' => NULL,
      'icon' => NULL,
      'separator' => FALSE,
    ];

    $properties = array_merge($defaults, $properties);

    foreach($properties as $property => $value) {

      if($property == 'separator') {
        $this->assertEquals($value, $menuItem->hasSeparator());
      }
      else{
        $getPropertyFunction = 'get' . ucfirst($property);
        $this->assertEquals($value, $menuItem->$getPropertyFunction());
      }

    }
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
}
