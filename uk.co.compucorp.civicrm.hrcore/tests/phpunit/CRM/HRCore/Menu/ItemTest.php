<?php

use CRM_HRCore_Menu_Item as MenuItem;

/**
 * @group headless
 */
class CRM_HRCore_Menu_ItemTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testGetLabel() {
    $label = 'Test Label';
    $menuItem = new MenuItem($label);
    $this->assertEquals($label, $menuItem->getLabel());
  }

  public function testSetAndGetUrl() {
    $url = 'civicrm/bla';
    $menuItem = new MenuItem('Test Label');
    $menuItem->setUrl($url);
    $this->assertEquals($url, $menuItem->getUrl());
  }

  public function testSetAndGetIcon() {
    $icon = 'fa icon-briefcase';
    $menuItem = new MenuItem('Test Label');
    $menuItem->setIcon($icon);
    $this->assertEquals($icon, $menuItem->getIcon());
  }

  public function testSetAndGetPermission() {
    $permission = 'administer CiviHR';
    $menuItem = new MenuItem('Test Label');
    $menuItem->setPermission($permission);
    $this->assertEquals($permission, $menuItem->getPermission());
  }

  public function testSetAndGetOperator() {
    $operator = 'AND';
    $menuItem = new MenuItem('Test Label');
    $menuItem->setOperator($operator);
    $this->assertEquals($operator, $menuItem->getOperator());
  }

  public function testAddSeparator() {
    $menuItem = new MenuItem('Test Label');
    $this->assertFalse($menuItem->hasSeparator());

    $menuItem->addSeparator();
    $this->assertTrue($menuItem->hasSeparator());
  }

  public function testAddAndGetChildren() {
    $menuItem = new MenuItem('Parent');
    $childMenuItem1 = new MenuItem('Child 1');
    $childMenuItem2 = new MenuItem('Child 2');
    $menuItem->addChild($childMenuItem1);
    $menuItem->addChild($childMenuItem2);

    $children = $menuItem->getChildren();
    $expectedChildren = [$childMenuItem1, $childMenuItem2];
    $this->assertEquals($expectedChildren, $children);
  }

  public function testSetAndGetTarget() {
    $target = '_blank';
    $menuItem = new MenuItem('Test Label');
    $menuItem->setTarget($target);
    $this->assertEquals($target, $menuItem->getTarget());
  }
}
