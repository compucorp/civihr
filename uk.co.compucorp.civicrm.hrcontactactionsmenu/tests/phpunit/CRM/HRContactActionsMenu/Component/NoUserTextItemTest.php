<?php

use CRM_HRContactActionsMenu_Component_NoUserTextItem as NoUserTextItem;
/**
 * Class CRM_HRContactActionsMenu_Component_NoUserTextItemTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_NoUserTextItemTest extends BaseHeadlessTest {

  public function testRender() {
    $expectedResult = '<p>There is no user for this staff member</p>';
    $noUserTextItem = new NoUserTextItem();
    $this->assertEquals($expectedResult, $noUserTextItem->render());
  }
}
