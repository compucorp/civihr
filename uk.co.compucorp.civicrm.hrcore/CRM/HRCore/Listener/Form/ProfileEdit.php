<?php

class CRM_HRCore_Listener_Form_ProfileEdit extends CRM_HRCore_Listener_AbstractListener {

  protected $objectClass = 'CRM_Profile_Form_Edit';

  public function onAlterContent(&$content) {
    if (!$this->canHandle()) {
      return;
    }

    $smarty = CRM_Core_Smarty::singleton();

    if ($smarty->_tpl_vars['context'] == 'dialog' && $smarty->_tpl_vars['ufGroupName'] == 'new_individual') {
      $content .="<script type=\"text/javascript\">
        CRM.$(function($) {
          $('.ui-dialog').css({'top':'10%'});
        });
      </script>";
    }
  }
}
