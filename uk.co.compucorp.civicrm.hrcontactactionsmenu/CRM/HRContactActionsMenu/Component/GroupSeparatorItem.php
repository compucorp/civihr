<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

class CRM_HRContactActionsMenu_Component_GroupSeparatorItem implements ActionsGroupItemInterface {

  /**
   * {@inheritDoc}
   */
  public function render() {
    return '<hr>';
  }
}