<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
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
 * Collection of upgrade steps
 */
class CRM_HRUI_Upgrader extends CRM_HRUI_Upgrader_Base {

  use CRM_HRUI_Upgrader_Steps_4700;
  use CRM_HRUI_Upgrader_Steps_4701;
  use CRM_HRUI_Upgrader_Steps_4702;
  use CRM_HRUI_Upgrader_Steps_4703;
  use CRM_HRUI_Upgrader_Steps_4704;
  use CRM_HRUI_Upgrader_Steps_4705;
  use CRM_HRUI_Upgrader_Steps_4706;

  public function install() {
    $this->runAllUpgraders();
  }

  /**
   * Runs all the upgrader methods when installing the extension
   */
  private function runAllUpgraders() {
    $revisions = $this->getRevisions();

    foreach ($revisions as $revision) {
      $methodName = 'upgrade_' . $revision;

      if (is_callable([$this, $methodName])) {
        $this->{$methodName}();
      }
    }
  }
}
