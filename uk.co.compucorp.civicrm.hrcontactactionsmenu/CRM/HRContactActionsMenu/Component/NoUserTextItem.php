<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class CRM_HRContactActionsMenu_ComponentNoUserTextItem
 */
class CRM_HRContactActionsMenu_Component_NoUserTextItem implements ActionsGroupItemInterface {

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    return '<p>There is no user for this staff member</p>';
  }
}
