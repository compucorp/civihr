<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;

/**
 * Class CRM_HRContactActionsMenu_Component_Panel
 */
class CRM_HRContactActionsMenu_Component_Panel {
  /**
   * @var array
   *   Items belonging to the the main panel
   */
  private $mainPanelItems = [];

  /**
   * Adds an actions menu group to the highlighted panel array
   *
   * @var array
   *  Items belonging to the highlighted panel
   */
  private $highlightedPanelItems = [];

  /**
   * Adds an actions menu group to the main panel array
   *
   * @param ActionsGroup $group
   */
  public function addToMain(ActionsGroup $group) {
    $this->mainPanelItems[] = $group;
  }

  /**
   * @param ActionsGroup $group
   */
  public function addToHighlighted(ActionsGroup $group) {
    $this->highlightedPanelItems[] = $group;
  }

  /**
   * Sorts PanelItems ordered by Panel Item weight
   * in ascending order.
   *
   * @param array $panelItems
   *
   * @return array
   */
  private function sortItems($panelItems) {
    usort($panelItems, function($a, $b) {
      if ($a->getWeight() == $b->getWeight()) {
        return 0;
      }

      return ($a->getWeight() < $b->getWeight()) ? -1 : 1;
    });

    return $panelItems;
  }

  /**
   * Returns the Panel Items belonging to the
   * main panel sorted by weight ascending.
   *
   * @return array
   */
  public function getMainItems() {
    return $this->sortItems($this->mainPanelItems);
  }

  /**
   * Returns the panel Items belonging to the
   * highlighted panel sorted by weight ascending.
   *
   * @return array
   */
  public function getHighlightedItems() {
    return $this->sortItems($this->highlightedPanelItems);
  }
}