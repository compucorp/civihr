<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_Panel as ActionsPanel;

/**
 * Class CRM_HRContactActionsMenu_Component_PanelTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_PanelTest extends BaseHeadlessTest {

  public function testAddingAndGettingMainPanelItems() {
    $weights = [5, 10, 3];
    $actionGroups = [];
    $panel = new ActionsPanel();

    //add action groups of varying weight to the main panel
    foreach ($weights as $weight) {
      $title = 'Group '. $weight;
      $actionGroups[$weight] = $this->getActionGroupMock($title, $weight);
      $panel->addToMain($actionGroups[$weight]);
    }

    //sort the action group by the weight key in ascending.
    //since we are expecting the results to be sorted by weight ascending
    ksort($actionGroups);
    //convert to zero indexed array.
    $actionGroups = array_values($actionGroups);

    $this->assertEquals($actionGroups, $panel->getMainItems());
  }

  public function testAddingAndGettingHighlightedPanelItems() {
    $weights = [5, 10, 3];
    $actionGroups = [];
    $panel = new ActionsPanel();

    //add action groups of varying weight to the highlightedpanel
    foreach ($weights as $weight) {
      $title = 'Group '. $weight;
      $actionGroups[$weight] = $this->getActionGroupMock($title, $weight);
      $panel->addToHighlighted($actionGroups[$weight]);
    }

    //sort the action group by the weight key in ascending.
    //since we are expecting the results to be sorted by weight ascending
    ksort($actionGroups);
    //convert to zero indexed array.
    $actionGroups = array_values($actionGroups);

    $this->assertEquals($actionGroups, $panel->getHighlightedItems());
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