<?php

/**
 * Interface CRM_HRContactActionsMenu_Component_GroupItem
 *
 * This interface class is implemented by the GroupButtonItem
 * and GroupSeparatorItem classes and also by any other class
 * that wants to add menu item objects to the Action Group class.
 */
interface CRM_HRContactActionsMenu_Component_GroupItem {

  /**
   * Function to render content for this Action Group menu item.
   *
   * @return mixed
   */
  public function render();
}
