<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class CRM_HRContactActionsMenu_Component_GroupSeparatorItem
 *
 * This class implements the ActionsGroupItemInterface
 * and allows a separator menu item to be created.
 */
class CRM_HRContactActionsMenu_Component_GroupSeparatorItem implements ActionsGroupItemInterface {

  /**
   * {@inheritDoc}
   */
  public function render() {
    return '<hr class="crm_contact-actions--dark-gray-blue">';
  }
}
