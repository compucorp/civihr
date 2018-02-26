<?php

use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_Helper_ContactActionsMenu_LeaveActionGroup as LeaveActionGroupHelper;
use CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_LeaveApproversListItem as LeaveApproversListItem;
use CRM_HRContactActionsMenu_Component_ParagraphItem as ParagraphItem;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as GroupButtonItem;
use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as GroupSeparatorItem;

/**
 * Class CRM_HRLeaveAndAbsences_Helper_ContactActionsMenu_LeaveActionGroupTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Helper_ContactActionsMenu_LeaveActionGroupTest extends BaseHeadlessTest {
  public function testMenuItemsAreCorrectlyAddedWhenContactHasNoLeaveApprover() {
    $contactID = 2;
    $leaveManagerService = $this->prophesize(LeaveManagerService::class);
    $leaveManagerService->getLeaveApproversForContact($contactID)->willReturn([]);
    $leaveActionGroupHelper = new LeaveActionGroupHelper($leaveManagerService->reveal(), $contactID);
    $leaveActionGroup = $leaveActionGroupHelper->get();

    //When user has no leave approver, eight items are expected,
    //Record Leave, Record Sickness, Record Overtime, Separator Item,
    //View Entitlements buttons, Separator Item,
    //No Leave Approver Text Item and Add Leave Approver button
    $leaveActionGroupItems = $leaveActionGroup->getItems();
    $this->assertCount(8, $leaveActionGroupItems);
    $this->assertDefaultLeaveGroupItems($leaveActionGroupItems);
    $this->assertInstanceOf(ParagraphItem::class, $leaveActionGroupItems[6]);
    $this->assertInstanceOf(GroupButtonItem::class, $leaveActionGroupItems[7]);

    //check that the group title is correct
    $this->assertEquals('Leave:', $leaveActionGroup->getTitle());
  }

  public function testMenuItemsAreCorrectlyAddedWhenContactHasALeaveApprover() {
    $contactID = 2;
    $leaveManagerService = $this->prophesize(LeaveManagerService::class);
    $leaveManagerService->getLeaveApproversForContact($contactID)->willReturn([1 => 'Test Leave Approver']);
    $leaveActionGroupHelper = new LeaveActionGroupHelper($leaveManagerService->reveal(), $contactID);
    $leaveActionGroup = $leaveActionGroupHelper->get();

    //When user has leave approver, eight items are expected,
    //Record Leave, Record Sickness, Record Overtime, Separator Item,
    //View Entitlements button, Separator Item,
    //Leave Approvers List and Manage Leave Approver button.
    $leaveActionGroupItems = $leaveActionGroup->getItems();
    $this->assertCount(8, $leaveActionGroupItems);
    $this->assertDefaultLeaveGroupItems($leaveActionGroupItems);
    $this->assertInstanceOf(LeaveApproversListItem::class, $leaveActionGroupItems[6]);
    $this->assertInstanceOf(GroupButtonItem::class, $leaveActionGroupItems[7]);

    //check that the group title is correct
    $this->assertEquals('Leave:', $leaveActionGroup->getTitle());
  }

  private function assertDefaultLeaveGroupItems($leaveActionGroupItems) {
    $this->assertInstanceOf(GroupButtonItem::class, $leaveActionGroupItems[0]);
    $this->assertInstanceOf(GroupButtonItem::class, $leaveActionGroupItems[1]);
    $this->assertInstanceOf(GroupButtonItem::class, $leaveActionGroupItems[2]);
    $this->assertInstanceOf(GroupSeparatorItem::class, $leaveActionGroupItems[3]);
    $this->assertInstanceOf(GroupButtonItem::class, $leaveActionGroupItems[4]);
    $this->assertInstanceOf(GroupSeparatorItem::class, $leaveActionGroupItems[5]);
  }
}
