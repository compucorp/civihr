<?php

class CRM_HRIdent_Upgrader extends CRM_HRIdent_Upgrader_Base {

  public function upgrade_1113() {
    $this->ctx->log->info('Planning update 1113'); // PEAR Log interface
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
    $gid = array_search('hrident_tab', $groups);
    $params = array(
      'action' => 'submit',
      'profile_id' => $gid,
    );
    $result = civicrm_api3('profile', 'getfields', $params);
    if($result['is_error'] == 0 ) {
      foreach($result['values'] as $key => $value) {
        if(isset($value['html_type']) && $value['html_type'] == "File") {
          CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_field SET is_multi_summary = 1 WHERE civicrm_uf_field.uf_group_id = {$gid} AND civicrm_uf_field.field_name = '{$key}'");
        }
      }
    }
    return TRUE;
  }

  public function upgrade_1200() {
    $this->ctx->log->info('Planning update 1200'); //PEAR Log interface
    $params = array(
      'option_group_id' => 'type_20130502144049',
      'label' => 'National Insurance',
      'value' => 'National Insurance',
      'name' => 'National_Insurance',
    );

    $result = civicrm_api3('OptionValue', 'create', $params);
    if ($result['is_error'] == 0) {
      return TRUE;
    }
    return FALSE;
  }

  public function upgrade_1400() {
    $this->ctx->log->info('Planning update 1400'); //PEAR Log interface
    // create custom field
    $cusGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Identify', 'id', 'name');
    $custGroupParams = array(
      'custom_group_id' => $cusGroupID,
      'name' => "is_government",
      'label' => "Is Government",
      'data_type' => "Boolean",
      'html_type' => "Radio",
      'is_active' => "1",
      'is_view' => "1",
      'column_name' => 'is_government',
    );
    $result = civicrm_api3('CustomField', 'create', $custGroupParams);
    $cusField = array(
      'custom_group_id' => "Identify",
      'name' => "is_government",
      'return' => "id",
    );
    $govRecord_id  = 'custom_'.civicrm_api3('CustomField', 'getvalue', $cusField);

    //create uffield
    $ufGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrident_tab', 'id', 'name');
    $ufFieldParam = array(
      'uf_group_id' => $ufGroupID,
      'field_name' => $govRecord_id,
      'is_active' => "1",
      'label' => "Is Government",
      'field_type' => "Individual",
      'is_view' => "1",
    );
    $result = civicrm_api3('UFField', 'create', $ufFieldParam);
    $groupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Identify', 'id', 'name');
    CRM_Core_DAO::setFieldValue('CRM_Core_DAO_CustomGroup', $groupID, 'is_reserved', '0');

    //HR-355 Change the title of option group to identify type
    $optgroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'type_20130502144049', 'id', 'name');
    CRM_Core_DAO::setFieldValue('CRM_Core_DAO_OptionGroup', $optgroupID, 'title', 'Government ID');

    $sql = "UPDATE civicrm_custom_field SET in_selector = '1' WHERE custom_group_id = {$cusGroupID} AND name IN ('Type','Number','Issue_Date','Expire_Date','Country','State_Province','Evidence_File')";
    CRM_Core_DAO::executeQuery($sql);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET style = 'Tab with table' WHERE id = {$cusGroupID}");

    return TRUE;
  }
  
  public function upgrade_1500(){
      $this->ctx->log->info('Planning update 1500'); //PEAR Log interface
      
      // Make is_goverment field editable
      $sql = "UPDATE civicrm_custom_field SET is_view = '0' WHERE name = 'is_government'";
      CRM_Core_DAO::executeQuery($sql);
      
      return true;
  }

  /**
   * Upgrader to set Government ID : National Insurance
   * to be the default one and changing its weight
   * to be on the top.
   */
  public function upgrade_1501() {
    // hence that type_20130502144049 is
    // the hardcoded name for this option group
    civicrm_api3('OptionValue', 'create', [
      'sequential' => 1,
      'option_group_id' => "type_20130502144049",
      'name' => "National_Insurance",
      'is_default' => 1,
      'weight' => 0,
    ]);
  }
}
