<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as GroupSeparatorItem;
use CRM_HRCore_Service_Manager as ManagerService;
use CRM_HRContactActionsMenu_Component_ParagraphItem as ParagraphItem;
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
    $actionsGroup->addItem($this->getJoiningButton());
    $actionsGroup->addItem($this->getExitingButton());
    $actionsGroup->addItem($this->getOtherButton());
    $actionsGroup->addItem(new GroupSeparatorItem());
    $actionsGroup->addItem($this->getNewTaskButton());
    $actionsGroup->addItem($this->getNewDocumentButton());
    $actionsGroup->addItem(new GroupSeparatorItem());

    $lineManagers = $this->getLineManagers();

    if ($lineManagers) {
      $lineManagersListItem = new LineManagersListItem($this->managerService, $this->contactID);
      $actionsGroup->addItem($lineManagersListItem);
      $actionsGroup->addItem($this->getManageLineManagerButton());
    }
    else {
      $noLineManagerTextItem = new ParagraphItem('You have not selected a Line Manager');
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
  private function getJoiningButton() {
    $caseTypeId = $this->getCaseTypeID('Joining');
    $params = [
      'label' => 'Joining',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-user-plus',
      'url' => $this->getTasksTabUrl(['openModal' => 'assignment', 'caseTypeId' => $caseTypeId]),
    ];
    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow Exiting button
   *
   * @return ActionsGroupButtonItem
   */
  private function getExitingButton() {
    $caseTypeId = $this->getCaseTypeID('Exiting');
    $params = [
      'label' => 'Exiting',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-user-times',
      'url' => $this->getTasksTabUrl(['openModal' => 'assignment', 'caseTypeId' => $caseTypeId])
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow Other button
   *
   * @return ActionsGroupButtonItem
   */
  private function getOtherButton() {
    $params = [
      'label' => 'Other...',
      'class' => 'btn btn-primary-outline',
      'icon' => '',
      'url' => $this->getTasksTabUrl(['openModal' => 'assignment'])
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow New Task button
   *
   * @return ActionsGroupButtonItem
   */
  private function getNewTaskButton() {
    $params = [
      'label' => 'New Task',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-check-square-o',
      'url' => $this->getTasksTabUrl(['openModal' => 'task'])
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Workflow New Document button
   *
   * @return ActionsGroupButtonItem
   */
  private function getNewDocumentButton() {
    $params = [
      'label' => 'New Document',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-id-card-o',
      'url' => $this->getDocumentsTabUrl(['openModal' => 'document'])
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
    $attribute = ['onclick' => "CRM.loadForm('/civicrm/contact/view/rel?cid={$this->contactID}&action=add&relTypeId={$relTypeID}')"];
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
      "reset=1&cid={$this->contactID}&selectedChild=rel"
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
  private function getMenuButton($params, array $attributes = []) {
    $button = new ActionsGroupButtonItem($params['label']);
    $button->setClass($params['class'])
      ->setIcon($params['icon'])
      ->setUrl($params['url']);

    foreach($attributes as $attribute => $value) {
      $button->setAttribute($attribute, $value);
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
    $caseTypes = $this->getWorkflowCaseTypes();

    return isset($caseTypes[$caseTypeName]) ? $caseTypes[$caseTypeName] : '';
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

  /**
   * Returns the URL for the T&A related buttons.
   *
   * @param array $queryParameters
   * @param string $defaultTab
   *
   * @return string
   */
  private function getTasksAndAssignmentsUrl($queryParameters, $defaultTab) {
    $defaultParameters = ['reset' => 1, 'cid' => $this->contactID];
    $queryParameters = array_merge($defaultParameters, $queryParameters);

    $url = CRM_Utils_System::url(
      "civicrm/tasksassignments/dashboard#/{$defaultTab}",
      http_build_query($queryParameters)
    );

    return $url;
  }

  /**
   * Gets the Url for a Document Tab based on the query parameters
   *
   * @param array $queryParameters
   *
   * @return string
   */
  private function getDocumentsTabUrl($queryParameters) {
    return $this->getTasksAndAssignmentsUrl($queryParameters, 'documents');
  }

  /**
   * Gets the Url for a Task Tab based on the query parameters
   *
   * @param array $queryParameters
   *
   * @return string
   */
  private function getTasksTabUrl($queryParameters) {
    return $this->getTasksAndAssignmentsUrl($queryParameters, 'tasks');
  }
}
