<?php

use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as ActionsGroupSeparatorItem;

/**
 * Class CRM_HRContactActionsMenu_Component_GroupSeparatorItemTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_GroupSeparatorItemTest extends BaseHeadlessTest {

  public function testRender() {
    $separator = new ActionsGroupSeparatorItem();
    $expectedResult = '<hr>';
    $this->assertEquals($expectedResult, $separator->render());
  }
}