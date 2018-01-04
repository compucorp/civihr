<?php

use CRM_HRContactActionsMenu_Hook_AddContactMenuActions as AddContactMenuActionsHook;
use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;
use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use Civi\Test\HookInterface as HookInterface;

/**
 * Class CRM_HRContactActionsMenu_Hook_AddContactMenuActionsTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Hook_AddContactMenuActionsTest extends BaseHeadlessTest implements HookInterface {

  private $groupTitle;

  public function hook_addContactMenuActions(ActionsMenu $menu) {
    $this->groupTitle = 'Test Group';
    $testGroup = new ActionsGroup($this->groupTitle);
    $menu->addToMainPanel($testGroup);
  }

  public function testInvokeInvokesTheAddContactMenuActionsHook() {
    $menu = AddContactMenuActionsHook::invoke();
    $this->assertInstanceOf(ActionsMenu::class, $menu);
    $groups = $menu->getMainPanelItems();
    $this->assertCount(1, $groups);
    $this->assertEquals($this->groupTitle, $groups[0]->getTitle());
  }
}
