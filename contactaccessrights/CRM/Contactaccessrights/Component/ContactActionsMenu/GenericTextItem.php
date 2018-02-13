<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class GenericTextItem
 */
class CRM_Contactaccessrights_Component_ContactActionsMenu_GenericTextItem implements ActionsGroupItemInterface {

  /**
   * @var string
   */
  private $text;

  /**
   * GenericTextItem constructor.
   *
   * @param string $text;
   */
  public function __construct($text) {
    $this->text = $text;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    return "<p>{$this->text}</p>";
  }
}
