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
   * @var array
   */
  private $attributes = [];

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
   * Stores attribute and value in the attributes array.
   *
   * @param string $attribute
   * @param string $value
   */
  public function setAttribute($attribute, $value) {
    $this->attributes[$attribute] = $value;
  }

  /**
   * {@inheritDoc}
   */
  public function render() {
    $buttonAttributes = '';
    if ($this->attributes) {
      foreach($this->attributes as $attribute => $value) {
        $buttonAttributes .= $attribute . '= "' . $value . '" ';
      }
    }

    $buttonMarkup = '
      <div class="crm_contact-actions__action">
        <a href="%s" class="btn %s" ' . $buttonAttributes . '>
          <i class="fa %s"></i> %s
        </a>
      </div>';

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
