<?php

class CRM_HRProfile_Page_HRProfile extends CRM_Profile_Page_Listings {
  function run() {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrprofile', 'css/hrprofile.css');
    parent::run();
  }

  function getTemplateFileName() {
    $profID = CRM_Utils_Request::retrieve('gid', 'String', $this);

    $this->_params['contact_type'] = 'Individual';

    $selector = new CRM_Profile_Selector_Listings($this->_params, $this->_customFields, $profID, $this->_map, FALSE, 0);
    $extraWhereClause = NULL;
    $grpParams = array('name'=>'HRJob_Summary');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomGroup', $grpParams, $cGrp);
    $fdParams = array('name'=>'Final_Termination_Date');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $fdParams, $fdField);
    $idParams = array('name'=>'Initial_Join_Date');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $idParams, $idField);

    $extraWhereClause = " (({$cGrp['table_name']}.{$fdField['column_name']} >= CURDATE() OR {$cGrp['table_name']}.{$fdField['column_name']} IS NULL) AND
      ({$cGrp['table_name']}.{$idField['column_name']} IS NOT NULL AND {$cGrp['table_name']}.{$idField['column_name']} <= CURDATE()))";

    $column = $columnHeaders = $selector->getColumnHeaders();
    $rows = $selector->getRows(4, 0, 0,NULL, NULL, $extraWhereClause);

    CRM_Utils_Hook::searchColumns('profile', $columnHeaders, $rows, $this);
    $this->assign('aaData',json_encode($rows));

    /* to bring column names in [
			{ "sTitle": "Engine" },
			{ "sTitle": "Browser" },] format*/
    $colunmH = "[";
    foreach($column as $k=>$v) {
      if(!empty($v['name'])) {
        $name = '{"sTitle":"'.$v['name'].'"}';
      }
      else {
        $name = '{"bSortable": false}';
      }
      $colunmH .= $name.",";
    }
    $colunmH .= "]";
    $this->assign('aaColumn', $colunmH);
    return 'CRM/HRProfile/Page/HRProfile.tpl';
  }
}
