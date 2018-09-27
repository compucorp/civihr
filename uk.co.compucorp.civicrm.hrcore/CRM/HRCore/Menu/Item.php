<?php

class CRM_HRCore_Menu_Item {

  /**
   * @var string
   */
  protected $label = NULL;

  /**
   * @var string
   */
  protected $url = NULL;

  /**
   * @var string
   */
  protected $icon = NULL;

  /**
   * @var string
   */
  protected $permission = NULL;

  /**
   * @var string
   */
  protected $operator = NULL;

  /**
   * @var bool
   */
  protected $separator = FALSE;

  /**
   * @var string
   */
  protected $target = NULL;

  /**
   * @var CRM_HRCore_Menu_Item[]
   */
  protected $children = [];

  /**
   * CRM_HRCore_Menu_Item constructor.
   *
   * @param string $label
   */
  public function __construct($label) {
    $this->label = $label;
  }

  /**
   * Returns the menu label
   *
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Returns the menu URL
   *
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Sets the menu URL
   *
   * @param string $url
   *
   * @return CRM_HRCore_Menu_Item
   */
  public function setUrl($url) {
    $this->url = $url;

    return $this;
  }

  /**
   * Returns the menu Icon class.
   *
   * @return string
   */
  public function getIcon() {
    return $this->icon;
  }

  /**
   * Sets the menu icon class
   *
   * @param string $icon
   *
   * @return CRM_HRCore_Menu_Item
   */
  public function setIcon($icon) {
    $this->icon = $icon;

    return $this;
  }

  /**
   * Returns the menu permissions.
   *
   * @return string
   */
  public function getPermission() {
    return $this->permission;
  }

  /**
   * Sets the menu permissions.
   *
   * @param string $permission
   *
   * @return CRM_HRCore_Menu_Item
   */
  public function setPermission($permission) {
    $this->permission = $permission;

    return $this;
  }

  /**
   * Returns the operator to be used for the
   * menu permission.
   *
   * @return string
   */
  public function getOperator() {
    return $this->operator;
  }

  /**
   * Sets the permission operator for the menu item.
   *
   * @param string $operator
   *
   * @return CRM_HRCore_Menu_Item
   */
  public function setOperator($operator) {
    $this->operator = $operator;

    return $this;
  }

  /**
   * Checks if the menu item has a separator.
   *
   * @return bool
   */
  public function hasSeparator() {
    return $this->separator;
  }

  /**
   * Sets the separator property to TRUE.
   */
  public function addSeparator() {
    $this->separator = TRUE;
  }

  /**
   * Returns the menu item link target
   *
   * @return string
   */
  public function getTarget() {
    return $this->target;
  }

  /**
   * Sets the menu item link target
   *
   * @param string $target
   *
   * @return CRM_HRCore_Menu_Item
   */
  public function setTarget($target) {
    $this->target = $target;

    return $this;
  }

  /**
   * Returns the menu item's children
   *
   * @return CRM_HRCore_Menu_Item[]
   */
  public function getChildren() {
    return $this->children;
  }

  /**
   * Adds a child for the menu item.
   *
   * @param CRM_HRCore_Menu_Item $child
   */
  public function addChild(CRM_HRCore_Menu_Item $child) {
    $this->children[] = $child;
  }
}
