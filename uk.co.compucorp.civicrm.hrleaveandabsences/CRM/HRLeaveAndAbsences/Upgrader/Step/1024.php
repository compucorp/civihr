<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1024 {
  
  /**
   * Update leave request templates to system workflow message
   *
   * @return bool
   */
  public function upgrade_1024() {
    $templateName = 'msg_tpl_workflow_leave';
    $result = $this->up1024_createLeaveWorkflowOptionGroup($templateName);
    
    if ($result['count'] > 0) {
      $this->up1024_createLeaveWorkflowOptionValue($result['id']);
    }
    
    return TRUE;
  }
  
  /**
   * Create Leave Message Template Workflow Option Group.
   *
   * @param $templateName
   *
   * @return array
   */
  public function up1024_createLeaveWorkflowOptionGroup($templateName) {
    $workflow = 'Message Template Workflow for Leave';
    
    $result = civicrm_api3('OptionGroup', 'create', [
      'name' => $templateName,
      'title' => $workflow,
      'description' => $workflow,
    ]);
    CRM_Core_PseudoConstant::flush();
    
    return $result;
  }
  
  /**
   * Create Leave Message Template Workflow Option Values.
   *
   * @param $templateName
   */
  public function up1024_createLeaveWorkflowOptionValue($templateName) {
    $optionValues = [
      [
        'option_group_id' => $templateName,
        'label' => 'CiviHR Leave Request Notification',
        'name' => 'civihr_leave_request',
      ],
      [
        'option_group_id' => $templateName,
        'label' => 'CiviHR TOIL Request Notification',
        'name' => 'civihr_toil_request',
      ],
      [
        'option_group_id' => $templateName,
        'label' => 'CiviHR Sickness Record Notification',
        'name' => 'civihr_sickness_record',
      ]
    ];
  
    foreach ($optionValues as $optionValue) {
      $result = civicrm_api3('OptionValue', 'create', $optionValue);
      if ($result['count'] > 0) {
        $this->up1024_updateLeaveMsgTemplate(
          $result['id'],
          $optionValue['label']
        );
      }
    }
  }
  
  /**
   * Update message template workflow id for option group msg_tpl_workflow_leave
   *
   * @param $workflowId
   * @param $msgLabel
   */
  public function up1024_updateLeaveMsgTemplate($workflowId, $msgLabel) {
    civicrm_api3('MessageTemplate', 'get', array(
      'return' => ['id'],
      'msg_title' => $msgLabel,
      'api.MessageTemplate.create' => [
        'workflow_id' => $workflowId,
        'id' => '$value.id'
      ],
    ));
  }
}
