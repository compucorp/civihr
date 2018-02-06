<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as GroupSeparatorItem;
use CRM_HRCore_Service_Manager as ManagerService;
use CRM_HRCore_Component_ContactActionsMenu_NoSelectedLineManagerTextItem as NoSelectedLineManagerTextItem;
use CRM_HRCore_Component_ContactActionsMenu_LineManagersListItem as LineManagersListItem;

/**
 * Class CRM_HRCore_Helper_ContactActionsMenu_WorkflowActionGroup
 */
class CRM_HRCore_Helper_ContactActionsMenu_WorkflowActionGroup {

  /**
   * @var ManagerService
   */
  private $managerService;

  /**
   * @var array
   */
  private $contactID;

  /**
   * @var array
   */
  private $workflowCaseTypes;

  /**
   * CRM_HRCore_Helper_ContactActionsMenu_WorkflowActionGroup constructor.
   *
   * @param ManagerService $managerService
   * @param int $contactID
   */
  public function __construct(ManagerService $managerService, $contactID) {
    $this->managerService = $managerService;
    $this->contactID = $contactID;
  }

  /**
   * Gets Workflow Menu Group with menu items already
   * added.
   */
  public function get() {
    $actionsGroup = new ActionsGroup('Workflows:');
    $actionsGroup->addItem($this->getWorkflowJoiningButton());
    $actionsGroup->addItem($this->getWorkflowExitingButton());
    $actionsGroup->addItem($this->getWorkflowOtherButton());
    $actionsGroup->addItem(new GroupSeparatorItem());
    $actionsGroup->addItem($this->getWorkflowNewTaskButton());
    $actionsGroup->addItem($this->getWorkflowNewDocumentButton());
    $actionsGroup->addItem(new GroupSeparatorItem());

    $lineManagers = $this->getLineManagers();

    if ($lineManagers) {
      $lineManagersListItem = new LineManagersListItem($this->managerService, $this->contactID);
      $actionsGroup->addItem($lineManagersListItem);
      $actionsGroup->addItem($this->getManageLineManagerButton());
    }
    else {
      $noLineManagerTextItem = new NoSelectedLineManagerTextItem();
      $actionsGroup->addItem($noLineManagerTextItem);
      $actionsGroup->addItem($this->getAddLineManagerButton());
    }

    return $actionsGroup;
  }

  /**
   * Gets the Workflow Joining button
   *
   * @return ActionsGroupButtonItem
   */
  private function getWorkflowJoiningButton() {
    $caseTypeId = $this->getCaseTypeID('Joining');
    $url = CRM_Utils_System::url(
      'civicrm/tasksassignments/dashboard#/tasks',
      "reset=1&cid=$this->contactID&openModal=assignment&caseTypeId=$caseTypeId"
    );
    $params = [
      'label' => 'Joining',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-user-plus',
      'url' => $url
    ];
    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow Exiting button
   *
   * @return ActionsGroupButtonItem
   */
  private function getWorkflowExitingButton() {
    $caseTypeId = $this->getCaseTypeID('Exiting');
    $url = CRM_Utils_System::url(
      'civicrm/tasksassignments/dashboard#/tasks',
      "reset=1&cid=$this->contactID&openModal=assignment&caseTypeId=$caseTypeId"
    );
    $params = [
      'label' => 'Exiting',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-user-times',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow Other button
   *
   * @return ActionsGroupButtonItem
   */
  private function getWorkflowOtherButton() {
    $url = CRM_Utils_System::url(
      'civicrm/tasksassignments/dashboard#/tasks',
      "reset=1&cid=$this->contactID&openModal=assignment"
    );
    $params = [
      'label' => 'Other...',
      'class' => 'btn btn-primary-outline',
      'icon' => '',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow New Task button
   *
   * @return ActionsGroupButtonItem
   */
  private function getWorkflowNewTaskButton() {
    $url = CRM_Utils_System::url(
      'civicrm/tasksassignments/dashboard#/tasks',
      "reset=1&cid=$this->contactID&openModal=task"
    );
    $params = [
      'label' => 'New Task',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-check-square-o',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow New Document button
   *
   * @return ActionsGroupButtonItem
   */
  private function getWorkflowNewDocumentButton() {
    $url = CRM_Utils_System::url(
      'civicrm/tasksassignments/dashboard#/documents',
      "reset=1&cid=$this->contactID&openModal=document"
    );
    $params = [
      'label' => 'New Document',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-id-card-o',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Add A Line Manager button.
   *
   * @return ActionsGroupButtonItem
   */
  public function getAddLineManagerButton() {
    $relTypeID = $this->getLineManagerRelationshipTypeSelectId();
    $attribute = ['onclick' => "CRM.loadForm('/civicrm/contact/view/rel?cid=$this->contactID&action=add&relTypeId=$relTypeID')"];
    $params = [
      'label' => 'Add A Line Manager',
      'class' => 'btn btn-secondary-outline',
      'icon' => '',
      'url' => '#'
    ];

    return $this->getMenuButton($params, $attribute);
  }

  /**
   * Gets the Manage Line Manager button
   *
   * @return ActionsGroupButtonItem
   */
  public function getManageLineManagerButton() {
    $url = CRM_Utils_System::url(
      'civicrm/contact/view',
      "reset=1&cid=$this->contactID&selectedChild=rel"
    );
    $params = [
      'label' => 'Manage Line Manager',
      'class' => 'btn btn-secondary',
      'icon' => '',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Returns an instance of an ActionsGroupButtonItem
   *
   * @param array $params
   * @param array $attributes
   *
   * @return ActionsGroupButtonItem
   */
  private function getMenuButton($params, $attributes = []) {
    $button = new ActionsGroupButtonItem($params['label']);
    $button->setClass($params['class'])
      ->setIcon($params['icon'])
      ->setUrl($params['url']);

    if ($attributes) {
      foreach($attributes as $attribute => $value) {
        $button->setAttribute($attribute, $value);
      }
    }

    return $button;
  }

  /**
   * Get the WorkFlows Case Types data
   * Namely for the Joining and Exiting Case type.
   */
  private function getWorkflowCaseTypes() {
    if(!$this->workflowCaseTypes) {
      $result =  civicrm_api3('CaseType', 'get', [
        'return' => ['id', 'name'],
        'title' => ['IN' => ['Joining', 'Exiting']],
      ]);

      $this->workflowCaseTypes = array_column($result['values'], 'id', 'name');
    }

    return $this->workflowCaseTypes;
  }

  /**
   * Returns the Case Type ID for the given Case Type name
   *
   * @param string $caseTypeName
   *
   * @return string
   */
  private function getCaseTypeID($caseTypeName) {
    return isset($this->getworkflowCaseTypes()[$caseTypeName]) ? $this->getworkflowCaseTypes()[$caseTypeName] : '';
  }

  /**
   * Gets Line managers for a contact.
   *
   * @return array
   */
  private function getLineManagers() {
    return $this->managerService->getLineManagersFor($this->contactID);
  }

  /**
   * Returns the relationship type Id for the `Line manager is`
   * relationship used to default to the relationship type
   * in the relationship type select field on the Add relationship modal.
   *
   * @return string
   */
  private function getLineManagerRelationshipTypeSelectId() {
    $result = civicrm_api3('RelationshipType', 'getsingle', [
      'name_a_b' => 'Line Manager Is'
    ]);

    return !empty($result['id']) ? $result['id'] . '_a_b' : '';
  }
}
