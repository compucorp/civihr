<?php

use CRM_HRCore_Menu_Config as MenuConfig;
use CRM_HRCore_Menu_MenuBuilder as MenuBuilder;
use CRM_HRCore_Menu_Item as MenuItem;

/**
 * @group headless
 */
class CRM_HRCore_Menu_MenuBuilderTest extends CRM_HRCore_Test_BaseHeadlessTest {

  private $menuBuilder;

  public function setUp() {
    $this->menuBuilder = new MenuBuilder();
  }

  public function testMenuConfigItemKeyIsSetAsMenuItemLabel() {
    $menuConfigItem = [
      'Staff' => []
    ];

    $menuObject = $this->menuBuilder->getMenuItems($this->getMenuConfigMock($menuConfigItem));
    $children = $menuObject->getChildren();
    $this->assertCount(1, $children);
    //'Staff' which is the Item key is set as the MenuItem label
    $this->assertEquals('Staff', $children[0]->getLabel());
  }

  public function testMenuConfigItemValueWillBeSetAsMenuItemUrlIfItIsAString() {
    $url = 'civcrm/staff';
    $menuConfigItem = [
      'Staff' => $url
    ];

    $menuObject = $this->menuBuilder->getMenuItems($this->getMenuConfigMock($menuConfigItem));
    $children = $menuObject->getChildren();
    $this->assertCount(1, $children);
    $this->assertEquals($url, $children[0]->getUrl());
  }

  public function testOtherMenuItemPropertiesWillBeNullWhenNotProvidedInMenuConfigItemValue() {
    $menuConfigItem = [
      'Staff' => [
        'icon' => 'fa -block'
      ]
    ];

    $menuObject = $this->menuBuilder->getMenuItems($this->getMenuConfigMock($menuConfigItem));
    $children = $menuObject->getChildren();

    $this->assertCount(1, $children);

    // Verify that other menu Item properties are set to NULL except for
    // label and Icon
    $this->verifyMenuItemProperties($children[0], [
      'label' => 'Staff',
      'icon' => 'fa -block'
    ]);
  }

  public function testGetMenuItemsReturnsCorrectlyForDeeplyNestedMenuConfigItems() {
    $menuObject = $this->menuBuilder->getMenuItems($this->getMenuConfigMock($this->getMenuConfigItems()));
    $menuItemObjects = $menuObject->getChildren();

    //Two top menu item levels
    $this->assertCount(2, $menuItemObjects);
    $topLevelMenu1 = $menuItemObjects[0];
    $topLevelMenu2 = $menuItemObjects[1];

    //verify top level menu1 and children properties
    $this->verifyMenuItemProperties($topLevelMenu1, ['label' => 'Staff', 'icon' => 'crm-i fa-users']);
    $topLevelMenu1Children = $topLevelMenu1->getChildren();
    $this->assertCount(1, $topLevelMenu1Children);
    $topLevelMenu1Child1 = $topLevelMenu1Children[0];

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

    $this->verifyMenuItemProperties($secondLevelChild, [
      'label' => 'Custom Records',
      'url' => 'civicrm/sample/bla',
    ]);
  }

  private function getMenuConfigMock($menuConfigItems) {
    $menuConfig = $this->prophesize(MenuConfig::class);
    $menuConfig->getItems()->willReturn($menuConfigItems);

    return $menuConfig->reveal();
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
