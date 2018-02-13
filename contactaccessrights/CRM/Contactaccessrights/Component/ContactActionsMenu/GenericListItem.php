<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

abstract class CRM_Contactaccessrights_Component_ContactActionsMenu_GenericListItem implements ActionsGroupItemInterface {

  /**
   * @var array
   */
  private $list;

  /**
   * @var string
   */
  private $label;

  /**
   * UserACLGroupsListItem constructor.
   *
   * @param array $list
   * @param string $label
   */
  public function __construct(array $list, $label) {
    $this->list = $list;
    $this->label = $label;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    $list = implode(', ', $this->list);

    $listMarkup = '
      <div class="crm_contact-actions__user-info">
        <dl class="dl-horizontal dl-horizontal-inline">
          <dt>' . $this->label . ':</dt>
          <dd>%s</dd>
        </dl>
      </div>';

    return sprintf(
      $listMarkup,
      $list
    );
  }
}
