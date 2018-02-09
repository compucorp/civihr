<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItem;

/**
 * Class CRM_HRContactActionsMenu_Component_Group
 *
 * This class allows adding menu items objects which
 * could be buttons, separators or objects extending
 * the GroupItem interface.
 */
class CRM_HRContactActionsMenu_Component_Group {
  /**
   * @var string
   */
  private $groupTitle;

  /**
   * @var int
   */
  private $weight = 0;

  /**
   * @var array
   */
  private $items = [];

  /**
   * @var array
   */
  private $permissions = [];

  /**
   * CRM_HRContactActionsMenu_Component_Group constructor.
   *
   * @param string $groupTitle
   */
  public function __construct($groupTitle) {
    $this->groupTitle = $groupTitle;
  }

  /**
   * Returns the name of this Action Group
   *
   * @return mixed
   */
  public function getTitle() {
    return $this->groupTitle;
  }

  /**
   * Adds an Actions Group Item.
   *
   * @param ActionsGroupItem $item
   */
  public function addItem(ActionsGroupItem $item) {
    $this->items[] = $item;
  }

  /**
   * Sets the weight of the Action Menu Group
   *
   * @param int $weight
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

  /**
   * Returns the weight of the Action Menu Group
   *
   * @return int
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * Returns all the items for this Action Menu Group.
   *
   * @return array
   */
  public function getItems() {
    return $this->items;
  }

  /**
   * Sets the permissions required to view this action group on
   * the Contact actions menu.
   *
   * @param array $permissions
   */
  public function setPermissions(array $permissions = []) {
    $this->permissions = $permissions;
  }

  /**
   * Gets the permissions required to view this action group.
   *
   * @return array
   */
  public function getPermissions() {
    return $this->permissions;
  }
}
