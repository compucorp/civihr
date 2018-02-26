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
}
