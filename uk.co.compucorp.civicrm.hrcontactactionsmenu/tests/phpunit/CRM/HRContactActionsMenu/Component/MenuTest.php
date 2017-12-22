<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;

/**
 * Class CRM_HRContactActionsMenu_Component_MenuTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_MenuTest extends BaseHeadlessTest {

  public function testAddingAndGettingMainPanelItems() {
    $weights = [5, 10, 3];
    $actionGroups = [];
    $menu = new ActionsMenu();

    //add action groups of varying weight to the main panel of the menu
    foreach ($weights as $weight) {
      $title = 'Group '. $weight;
      $actionGroups[$weight] = $this->getActionGroupMock($title, $weight);
      $menu->addToMainPanel($actionGroups[$weight]);
    }

    //sort the action group by the weight key in ascending.
    //since we are expecting the results to be sorted by weight ascending
    ksort($actionGroups);
    //convert to zero indexed array.
    $actionGroups = array_values($actionGroups);

    $this->assertEquals($actionGroups, $menu->getMainPanelItems());
  }

  public function testAddingAndGettingHighlightedPanelItems() {
    $weights = [5, 10, 3];
    $actionGroups = [];
    $menu = new ActionsMenu();

    //add action groups of varying weight to the highlighted panel of the menu
    foreach ($weights as $weight) {
      $title = 'Group '. $weight;
      $actionGroups[$weight] = $this->getActionGroupMock($title, $weight);
      $menu->addToHighlightedPanel($actionGroups[$weight]);
    }

    //sort the action group by the weight key in ascending.
    //since we are expecting the results to be sorted by weight ascending
    ksort($actionGroups);
    //convert to zero indexed array.
    $actionGroups = array_values($actionGroups);

    $this->assertEquals($actionGroups, $menu->getHighlightedPanelItems());
  }

  private function getActionGroupMock($title, $weight) {
    $actionsGroup = $this->getMockBuilder(ActionsGroup::class)
      ->setConstructorArgs([$title])
      ->setMethods(['getWeight'])
      ->getMock();

    $actionsGroup->expects($this->any())
      ->method('getWeight')
      ->will($this->returnValue($weight));

    return $actionsGroup;
  }
}
