<?php

use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Service_Manager as ManagerService;
use CRM_HRCore_Helper_ContactActionsMenu_WorkflowActionGroup as WorkflowActionGroupHelper;
use CRM_HRCore_Component_ContactActionsMenu_NoSelectedLineManagerTextItem as NoSelectedLineManagerTextItem;
use CRM_HRCore_Component_ContactActionsMenu_LineManagersListItem as LineManagersListItem;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as GroupButtonItem;
use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as GroupSeparatorItem;

/**
 * Class CRM_HRCore_Helper_ContactActionsMenu_WorkflowActionGroupTest
 *
 * @group headless
 */
class CRM_HRCore_Helper_ContactActionsMenu_WorkflowActionGroupTest extends BaseHeadlessTest {

  public function testMenuItemsAreCorrectlyAddedWhenContactHasNoLineManager() {
    $contactID = 2;
    $managerService = $this->prophesize(ManagerService::class);
    $managerService->getLineManagersFor($contactID)->willReturn([]);
    $workflowActionGroupHelper = new WorkflowActionGroupHelper($managerService->reveal(), $contactID);
    $workflowActionGroup = $workflowActionGroupHelper->get();

    //When user has no line manager, nine items are expected,
    //Joining, Exiting, Other, New Task, New Document buttons, No Line Manager Text Item
    //and Add Line Manager button with two separator items.
    $workflowActionGroupItems = $workflowActionGroup->getItems();
    $this->assertCount(9, $workflowActionGroupItems);
    $this->assertDefaultWorkflowItems($workflowActionGroupItems);
    $this->assertInstanceOf(NoSelectedLineManagerTextItem::class, $workflowActionGroupItems[7]);
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[8]);

    //check that the group title is correct
    $this->assertEquals('Workflows:', $workflowActionGroup->getTitle());
  }

  public function testMenuItemsAreCorrectlyAddedWhenContactHasALineManager() {
    $contactID = 2;
    $managerService = $this->prophesize(ManagerService::class);
    $managerService->getLineManagersFor($contactID)->willReturn([5 => 'Test Manager']);
    $workflowActionGroupHelper = new WorkflowActionGroupHelper($managerService->reveal(), $contactID);
    $workflowActionGroup = $workflowActionGroupHelper->get();

    //When user has no line manager, nine items are expected,
    //Joining, Exiting, Other, New Task, New Document buttons, Line managers list
    //and Manage Line Manager button with two separator items
    $workflowActionGroupItems = $workflowActionGroup->getItems();
    $this->assertCount(9, $workflowActionGroupItems);
    $this->assertDefaultWorkflowItems($workflowActionGroupItems);
    $this->assertInstanceOf(LineManagersListItem::class, $workflowActionGroupItems[7]);
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[8]);

    //check that the group title is correct
    $this->assertEquals('Workflows:', $workflowActionGroup->getTitle());
  }

  private function assertDefaultWorkflowItems($workflowActionGroupItems) {
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[0]);
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[1]);
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[2]);
    $this->assertInstanceOf(GroupSeparatorItem::class, $workflowActionGroupItems[3]);
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[4]);
    $this->assertInstanceOf(GroupButtonItem::class, $workflowActionGroupItems[5]);
    $this->assertInstanceOf(GroupSeparatorItem::class, $workflowActionGroupItems[6]);
  }
}
