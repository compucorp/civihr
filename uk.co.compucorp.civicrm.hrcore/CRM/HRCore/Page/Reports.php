<?php

class CRM_HRCore_Page_Reports extends CRM_Core_Page {

  /**
   * {@inheritdoc}
   */
  public function run() {
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/crm/vendor/iframeResizer.min.js');

    $this->assign('reportName', $this->getReportName());

    return parent::run();
  }

  /**
   * Returns the report name from the request URL
   *
   * The reports url follow this format: /civicrm/reports/my_report_name. This
   * method will return the /my_report_name part.
   *
   * Trailing / will be removed
   *
   * @return string
   */
  private function getReportName() {
    $baseReportsPath = '/civicrm/reports';
    $requestPath = $_SERVER['REQUEST_URI'];
    $reportName = str_replace($baseReportsPath, '', $requestPath);
    $reportName = rtrim($reportName, '/');

    return $reportName;
  }
}
