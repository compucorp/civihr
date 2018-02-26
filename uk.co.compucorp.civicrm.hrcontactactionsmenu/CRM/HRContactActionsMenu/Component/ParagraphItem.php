<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class CRM_HRContactActionsMenu_Component_ParagraphItem
 */
class CRM_HRContactActionsMenu_Component_ParagraphItem implements ActionsGroupItemInterface {

  /**
   * @var string
   */
  private $text;

  /**
   * ParagraphItem constructor.
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
