<?php

use CRM_Contactaccessrights_Component_ContactActionsMenu_GenericTooltipItem as GenericTooltipItem;

class CRM_Contactaccessrights_Component_ContactActionsMenu_NoStaffToolTipItem extends GenericTooltipItem {

  /**
   * Returns the text for the Tooltip.
   *
   * @return string
   */
  protected function getToolTipText() {
    return 'This user cannot see any staff as they are neither a CiviHR admin 
      nor have they been assigned access to a region or location. 
      This can be done by clicking the manage regional access button.';
  }
}
