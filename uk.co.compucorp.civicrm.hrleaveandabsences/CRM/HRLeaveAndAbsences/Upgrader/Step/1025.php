<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1025 {
  /**
   * Update leave request templates to system workflow message
   *
   * @return bool
   */
  public function upgrade_1025() {
    $templateName = 'msg_tpl_workflow_leave';
    $result = $this->up1025_createLeaveWorkflowOptionGroup($templateName);

    if ($result['count'] > 0) {
      $this->up1025_createLeaveWorkflowOptionValue($templateName);
    }
    
    return TRUE;
  }
  
  /**
   * Create Leave Message Template Workflow Option Group.
   *
   * @param string $templateName
   *
   * @return array
   */
  public function up1025_createLeaveWorkflowOptionGroup($templateName) {
    $workflow = 'Message Template Workflow for Leave';
    
    $result = civicrm_api3('OptionGroup', 'create', [
      'name' => $templateName,
      'title' => $workflow,
      'description' => $workflow,
      'is_locked' => 1,
      'is_reserved' => 1
    ]);
    
    // Flush constant because the option group id is cached and will not be
    // available for linking newly created option group value
    CRM_Core_PseudoConstant::flush();
    
    return $result;
  }
  
  /**
   * Create Leave Message Template Workflow Option Values.
   *
   * @param string $templateName
   */
  public function up1025_createLeaveWorkflowOptionValue($templateName) {
    $optionValues = [
      [
        'option_group_id' => $templateName,
        'label' => 'CiviHR Leave Request Notification',
        'name' => 'civihr_leave_request_notification',
      ],
      [
        'option_group_id' => $templateName,
        'label' => 'CiviHR TOIL Request Notification',
        'name' => 'civihr_toil_request_notification',
      ],
      [
        'option_group_id' => $templateName,
        'label' => 'CiviHR Sickness Record Notification',
        'name' => 'civihr_sickness_record_notification',
      ]
    ];
  
    foreach ($optionValues as $optionValue) {
      $result = civicrm_api3('OptionValue', 'create', $optionValue);
      if ($result['count'] > 0) {
        $this->up1025_updateLeaveMsgTemplate(
          $result['id'],
          $optionValue['label']
        );
      }
    }
  }
  
  /**
   * Update message template workflow id for option group msg_tpl_workflow_leave
   *
   * @param int $workflowId
   * @param string $msgLabel
   */
  public function up1025_updateLeaveMsgTemplate($workflowId, $msgLabel) {
    $result = civicrm_api3('MessageTemplate', 'get', [
      'sequential' => 1,
      'return' => ['id', 'msg_title', 'msg_subject', 'msg_text', 'msg_html'],
      'msg_title' => $msgLabel,
    ]);
    if ($result['count'] == 1) {
      // update existing record as default with new workflow id
      civicrm_api3('MessageTemplate', 'create', [
        'workflow_id' => $workflowId,
        'is_default' => 1,
        'is_reserved' => 0,
        'id' => $result['id'],
      ]);
      
      // create the reserved version of template
      $this->duplicateTemplateAsReserved($result['values'][0], $workflowId);
    } else {
      foreach ($result['values'] as $value) {
        civicrm_api3('MessageTemplate', 'create', [
          'workflow_id' => $workflowId,
          'id' => $value['id'],
        ]);
      }
    }
  }
  
  /**
   * Duplicate an existing template and set is_reserved true
   *
   * @param array $template
   * @param int $workflowId
   */
  public function duplicateTemplateAsReserved($template, $workflowId) {
    $newTemplate = [
      'msg_title' => $template['msg_title'],
      'msg_subject' => $template['msg_subject'],
      'msg_text' => $template['msg_text'],
      'msg_html' => $template['msg_html'],
      'workflow_id' => $workflowId,
      'is_default' => 0,
      'is_reserved' => 1
    ];
  
    civicrm_api3('MessageTemplate', 'create', $newTemplate);
  }
}
