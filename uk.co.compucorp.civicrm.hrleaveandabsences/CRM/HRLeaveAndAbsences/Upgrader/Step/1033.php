<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1033 {

  /**
   * Migrates message templates from managed entities to upgraders
   * Email message templates were previously setup with managed entities
   * as user-driven message templates. Changing the templates to system workflow
   * cannot be handled by managed entities due to the complexities. It requires
   * linking each template to an option group and creating a backup template
   * for each.
   *
   * @return bool
   */
  public function upgrade_1033() {
    $this->up1033_removeReferenceToTemplateEntities();
    $this->up1033_fixTemplates();

    return TRUE;
  }

  /**
   * Removes references to managed email template entities
   */
  private function up1033_removeReferenceToTemplateEntities() {
    $query = 'DELETE FROM civicrm_managed WHERE `entity_type` = "MessageTemplate"
AND module = "uk.co.compucorp.civicrm.hrleaveandabsences"';

    CRM_Core_DAO::executeQuery($query);
  }

  /**
   * Initiates checks for each message template as system workflow template
   */
  private function up1033_fixTemplates() {
    $optionGroup = 'msg_tpl_workflow_leave';
    $optionValues = [
      'civihr_leave_request_notification',
      'civihr_toil_request_notification',
      'civihr_sickness_record_notification'
    ];

    foreach ($optionValues as $optionValue) {
      $result = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => $optionGroup,
        'name' => $optionValue
      ]);
      $this->up1033_checkTemplate(array_shift($result['values']));
    }
  }

  /**
   * Checks if template exist as system workflow and initiate fixes if not
   *
   * @param array $optionValue
   */
  private function up1033_checkTemplate($optionValue) {
    $templateResult = civicrm_api3('MessageTemplate', 'get', [
      'return' => ['id', 'msg_title', 'msg_subject', 'msg_text', 'msg_html'],
      'msg_title' => $optionValue['label'],
    ]);
    if ($templateResult['count'] === 0) {
      $this->up1033_setupSystemWorkflowTemplate($optionValue);
    }

    if ($templateResult['count'] === 1) {
      $this->up1033_moveTemplatesToSystemWorkflow(array_shift($templateResult['values']), $optionValue);
    }
  }

  /**
   * Migrates existing message templates to system workflow
   *
   * @param array $messageTemplate
   * @param array $optionValue
   */
  private function up1033_moveTemplatesToSystemWorkflow($messageTemplate, $optionValue) {
    civicrm_api3('MessageTemplate', 'create', [
      'workflow_id' => $optionValue['id'],
      'is_default' => 1,
      'is_reserved' => 0,
      'id' => $messageTemplate['id'],
    ]);

    $this->up1033_duplicateTemplateAsReserved($messageTemplate, $optionValue['id']);
  }

  /**
   * Sets up email message template as system workflow template
   *
   * @param array $optionValue
   */
  private function up1033_setupSystemWorkflowTemplate($optionValue) {
    $emailTemplates = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrleaveandabsences')
      . '/message_templates/EmailTemplate.php';
    $templates = include $emailTemplates;
    $templateIndex = array_search($optionValue['label'], array_column($templates, 'msg_title'));

    if ($templateIndex >= 0) {
      $template = $templates[$templateIndex];
      $template['workflow_id'] = $optionValue['id'];
      civicrm_api3('MessageTemplate', 'create', $template);

      $this->up1033_duplicateTemplateAsReserved($template, $optionValue['id']);
    }
  }

  /**
   * Duplicates template for backup
   *
   * @param array $template
   * @param int $workflowId
   */
  public function up1033_duplicateTemplateAsReserved($template, $workflowId) {
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
