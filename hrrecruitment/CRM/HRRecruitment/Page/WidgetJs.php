<?php
/*
  +--------------------------------------------------------------------+
  | CiviHR version 1.3                                                 |
  +--------------------------------------------------------------------+
  | Copyright CiviCRM LLC (c) 2004-2013                                |
  +--------------------------------------------------------------------+
  | This file is a part of CiviCRM.                                    |
  |                                                                    |
  | CiviCRM is free software; you can copy, modify, and distribute it  |
  | under the terms of the GNU Affero General Public License           |
  | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
  |                                                                    |
  | CiviCRM is distributed in the hope that it will be useful, but     |
  | WITHOUT ANY WARRANTY; without even the implied warranty of         |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
  | See the GNU Affero General Public License for more details.        |
  |                                                                    |
  | You should have received a copy of the GNU Affero General Public   |
  | License and the CiviCRM Licensing Exception along                  |
  | with this program; if not, contact CiviCRM LLC                     |
  | at info[AT]civicrm[DOT]org. If you have questions about the        |
  | GNU Affero General Public License or the licensing of CiviCRM,     |
  | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
  +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * Widget for displaying list of vacancies
 */
class CRM_HRRecruitment_Page_WidgetJs extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('Public Vacancy List'));
    global $civicrm_root;
    global $base_url;

    //fetch listing
    $row = self::vacancyListDisplay();
    $output = '';
    $output .= '(function() {';
    $output .= file_get_contents("{$civicrm_root}packages/backbone/lodash.compat.min.js");

    //Add js file
    $templateDir = array("{$civicrm_root}packages/jquery/", "{$civicrm_root}packages/jquery/jquery-ui/js/");
    foreach ($templateDir as $templateDir) {
      $files = (array) glob($templateDir . '*.min.js', GLOB_BRACE);
      foreach ($files as $file) {
        $output .= file_get_contents($file);
      }
    }

    //Add css files
    $scan = scandir("{$civicrm_root}packages/jquery/jquery-ui/css/theme/");
    foreach ($scan as $file) {
    if (!is_dir("{$civicrm_root}packages/jquery/jquery-ui/css/theme/{$file}")) {
      $output .= 	"var link = document.createElement('link');
        link.rel  = 'stylesheet';
        link.type = 'text/css';
        link.href = '{$base_url}/sites/all/modules/civicrm/packages/jquery/jquery-ui/css/theme/{$file}';
        link.media = 'all';
        document.getElementsByTagName('head')[0].appendChild(link);";
      }
    }

    //Add template
    $output .= "var $ = jQuery.noConflict();var c_ = _.noConflict();c_.templateSettings.variable = 'rc';";
    $output .= CRM_Core_Smarty::singleton()->fetch('CRM/HRRecruitment/Page/WidgetJs.tpl');
    $output .= "document.write('<div id=\'vacancyPublicList\'></div>');
      var div = document.getElementById('vacancyPublicList');
      div.innerHTML = listTemplate($row);
      document.write('<div id=\'vacancyListDialog\'></div>');";

    //Dialog box to display vacancy information
    $output .= '$(".hr-job-position-link").bind("click",function(e){
      e.preventDefault();
      var infourl = $(this).attr("href") + "&callback=?";
      $.getJSON(infourl).done(function(result) {
        c_.templateSettings.variable = \'rc\';
        $("#vacancyListDialog").html(infoTemplate(result));
        $("#vacancyListDialog").dialog({
          modal: true,
          width: 600,
          title: result.position,
          buttons: [
            {
              text: result.applyButton,
              click: function () { window.location = result.apply; }
            },
            {
              text: result.close,
              click: function () { $(this).dialog("close"); }
            }
          ]
        });
      });
    });';

    $output .= '})();';
    header('Content-type: text/javascript');
    echo $output;
    exit();
  }

  public static function vacancyListDisplay() {
    global $base_url;
    $vacancies = civicrm_api3('HRVacancy','get', array('is_template'=> 0, 'status_id'=> 'Open'));
    foreach ($vacancies['values'] as $vacancyKey => $vacancyVal) {
      $row[$vacancyVal['id']]['id'] = $vacancyVal['id'];
      $row[$vacancyVal['id']]['position'] = ts($vacancyVal['position']);
      $row[$vacancyVal['id']]['positionLink'] = "{$base_url}/civicrm/vacancy/info?id={$vacancyVal['id']}";
      $row[$vacancyVal['id']]['location'] = ts($vacancyVal['location']);
      $row[$vacancyVal['id']]['salary'] = $vacancyVal['salary'];
      $row[$vacancyVal['id']]['startDate'] = CRM_Utils_Date::customFormat($vacancyVal['start_date']);
      $row[$vacancyVal['id']]['endDate'] = CRM_Utils_Date::customFormat($vacancyVal['end_date']);
      $row[$vacancyVal['id']]['apply'] = "{$base_url}/civicrm/vacancy/apply?id={$vacancyVal['id']}";
    }
    if (!empty($_GET['callback'])) {
      echo $_GET['callback'] .'('. json_encode($row) .')';
    }
    else {
      return json_encode($row);
    }
    CRM_Utils_System::civiExit();
  }

  public static function vacancyInfo($vacancyId = NULL) {
    if (!$_GET['id'] && !$vacancyId) {
      return;
    }
    global $base_url;
    $vacancyId = $_GET['id'] ? $_GET['id'] : $vacancyId;
    $vacancy = civicrm_api3('HRVacancy','get', array('id'=>$vacancyId));
    foreach ($vacancy['values'] as $vacancyKey => $vacancyVal) {
      CRM_Utils_System::setTitle(ts("{$vacancyVal['position']}"));
      $row['position'] = ts($vacancyVal['position']);
      $row['id'] = $vacancyVal['id'];
      $row['salary'] = $vacancyVal['salary'];
      $row['location'] = ts($vacancyVal['location']);
      $row['description'] = ts($vacancyVal['description']);
      $row['benefits'] = ts($vacancyVal['benefits']);
      $row['requirements'] = ts($vacancyVal['requirements']);
      $row['apply'] = "{$base_url}/civicrm/vacancy/apply?id={$vacancyVal['id']}";
    }
    $row['applyButton'] = ts("Apply Now");
    $row['close'] = ts("Close");

    if (!empty($_GET['callback'])) {
      echo $_GET['callback'] .'('. json_encode($row) .')';
    }
    else {
      return json_encode($row);
    }
    CRM_Utils_System::civiExit();
  }
}
