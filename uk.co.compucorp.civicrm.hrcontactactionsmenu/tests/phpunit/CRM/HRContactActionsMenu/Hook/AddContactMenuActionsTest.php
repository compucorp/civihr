<?php

use CRM_HRContactActionsMenu_Hook_AddContactMenuActions as AddContactMenuActionsHook;
use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;

/**
 * Class CRM_HRContactActionsMenu_Hook_AddContactMenuActionsTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Hook_AddContactMenuActionsTest extends BaseHeadlessTest {

  public function testInvokeReturnsAnActionsMenuInstance() {
    $this->assertInstanceOf(ActionsMenu::class, AddContactMenuActionsHook::invoke());
  }
}
