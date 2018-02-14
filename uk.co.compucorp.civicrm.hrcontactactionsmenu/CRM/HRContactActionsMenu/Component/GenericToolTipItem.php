<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

abstract class CRM_HRContactActionsMenu_Component_GenericTooltipItem implements ActionsGroupItemInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $toolTipText = $this->getTooltipText();

    $toolTipText = str_replace(["\n", "\r"], '', $toolTipText);

    return '<span class="fa fa-question-circle" 
      onclick="CRM.alert(\'' . $toolTipText . '\',\'\', \'info\')"></span>';
  }

  abstract protected function getTooltipText();
}
