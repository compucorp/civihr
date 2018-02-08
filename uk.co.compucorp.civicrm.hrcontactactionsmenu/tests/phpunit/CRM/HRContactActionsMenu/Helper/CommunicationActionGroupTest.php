<?php

use CRM_HRContactActionsMenu_Helper_CommunicationActionGroup as CommunicationActionGroupHelper;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as GroupButtonItem;

/**
 * Class CRM_HRContactActionsMenu_Helper_CommunicationActionGroupTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Helper_CommunicationActionGroupTest extends BaseHeadlessTest {

  public function testMenuItemsAreAddedCorrectly() {
    $contactID = 5;
    $communicationGroupHelper = new CommunicationActionGroupHelper($contactID);
    $communicationGroup = $communicationGroupHelper->get();
    $communicationGroupItems = $communicationGroup->getItems();
    $this->assertCount(3, $communicationGroupItems);

    //Three buttons are expected, The Send email, Record meeting and
    //create PDF letter buttons.
    $this->assertInstanceOf(GroupButtonItem::class, $communicationGroupItems[0]);
    $this->assertInstanceOf(GroupButtonItem::class, $communicationGroupItems[1]);
    $this->assertInstanceOf(GroupButtonItem::class, $communicationGroupItems[2]);

    //check that the group title is correct
    $this->assertEquals('Communicate:', $communicationGroup->getTitle());
  }
}
