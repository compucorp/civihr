<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItem;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as ActionsGroupGroupSeparatorItem;

/**
 * Class CRM_HRContactActionsMenu_Component_GroupTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_GroupTest extends BaseHeadlessTest {

  public function testGetGroupTitle() {
    $groupTitle = 'Test Title';
    $actionsGroup = new ActionsGroup($groupTitle);
    $this->assertEquals($groupTitle, $actionsGroup->getTitle());
  }

  public function testSetAndGetWeight() {
    $weight = 5;
    $actionsGroup = new ActionsGroup('Test Title');
    $actionsGroup->setWeight($weight);
    $this->assertEquals($weight, $actionsGroup->getWeight());
  }

  public function testAddAndGetItems() {
    $actionsGroup = new ActionsGroup('Test Title');
    $items[] = $this->getActionGroupItem();
    $items[] = $this->getActionGroupButtonItem();
    $items[] = $this->getActionGroupSeparatorItem();

    foreach ($items as $item) {
      $actionsGroup->addItem($item);
    }

    $this->assertEquals($items, $actionsGroup->getItems());
  }

  private function getActionGroupItem($type = null) {
    $class = ActionsGroupItem::class;

    if ($type == 'button') {
      $class = ActionsGroupButtonItem::class;
    }

    if ($type == 'separator') {
      $class = ActionsGroupGroupSeparatorItem::class;
    }
    $actionsGroupItem = $this->prophesize($class);

    return $actionsGroupItem->reveal();
  }

  private function getActionGroupButtonItem() {
    return $this->getActionGroupItem('button');
  }

  private function getActionGroupSeparatorItem() {
    return $this->getActionGroupItem('separator');
  }
}
