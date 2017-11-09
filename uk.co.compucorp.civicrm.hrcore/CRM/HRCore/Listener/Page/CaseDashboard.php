<?php

class CRM_HRCore_Listener_Page_CaseDashboard extends CRM_HRCore_Listener_AbstractListener {

  protected $objectClass = 'CRM_Case_Page_DashBoard';
  protected $pageCSSIdentifier = '.page-civicrm-case';

  public function onAlterContent(&$content) {
    if (!$this->canHandle()) {
      return;
    }

    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        $('{$this->pageCSSIdentifier} table.report tr th strong').each(function () {
          var app = $(this).text();
          if (app == 'Application') {
            $(this).parent('th').parent('tr').remove();
          }
        });
      });
    </script>";
  }
}
