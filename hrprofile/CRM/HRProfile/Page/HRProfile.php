<?php

class CRM_HRProfile_Page_HRProfile extends CRM_Profile_Page_Listings {
  function getTemplateFileName() {
    $profID = CRM_Utils_Request::retrieve('gid', 'String', $this);
    $selector = new CRM_Profile_Selector_Listings($this->_params, $this->_customFields, $profID,
                                                  $this->_map, FALSE, 0
                                                  );
    $column = $columnHeaders = $selector->getColumnHeaders();
    $rows = $selector->getRows(4,0,0,NULL);
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
