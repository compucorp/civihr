<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class CRM_HRContactActionsMenu_Component_GroupButtonItem
 *
 * This class implements the ActionsGroupItemInterface
 * and allows a button menu item to be created.
 */
class CRM_HRContactActionsMenu_Component_GroupButtonItem implements ActionsGroupItemInterface {

  /**
   * @var string
   */
  private $label;

  /**
   * @var string
   */
  private $icon = '';

  /**
   * @var string
   */
  private $class;

  /**
   * @var string
   */
  private $url;

  /**
   * @var bool
   */
  private $addBottomMargin = FALSE;

  /**
   * CRM_HRContactActionsMenu_Component_GroupButtonItem constructor.
   *
   * @param string $label
   */
  public function __construct($label) {
    $this->label = $label;
  }

  /**
   * Sets the Button Icon
   *
   * @param string $icon
   *
   * @return CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  public function setIcon($icon) {
    $this->icon = $icon;

    return $this;
  }

  /**
   * Sets the Button class
   *
   * @param string $class
   *
   * @return CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  public function setClass($class) {
    $this->class = $class;

    return $this;
  }

  /**
   * Sets the button url
   *
   * @param string $url
   *
   * @return CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  public function setUrl($url) {
    $this->url = $url;

    return $this;
  }

  /**
   * Indicates that this button should
   * have a bottom margin.
   *
   * @return CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  public function addBottomMargin() {
    $this->addBottomMargin = TRUE;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function render() {
    $buttonMarkup = '
      <a href="%s" class="btn %s">
        <span><i class="fa %s"></i></span> %s
      </a>';

    $buttonMarkup = sprintf(
      $buttonMarkup,
      $this->url,
      $this->class,
      $this->icon,
      $this->label
    );

    return $buttonMarkup;
  }
}
