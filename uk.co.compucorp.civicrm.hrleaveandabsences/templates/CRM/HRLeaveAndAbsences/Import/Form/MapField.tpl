{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
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
*}
<div id="bootstrap-theme" class="crm_wizard crm-form-block crm-leave-and-balance-import crm-activity-import-mapfield-form-block">
  {include file="CRM/HRUI/common/WizardHeader.tpl"}
  <div class="panel panel-default crm_wizard__body">
    <div class="panel-body">
      <div class="alert alert-info">
        <p>{ts}Review the values shown below from the first 2 rows of your import file and select the matching CiviCRM database fields from the drop-down lists in the right-hand column. Select '- do not import -' for any columns in the import file that you want ignored.{/ts}</p>
        {if $savedMapping}
          <p>{ts}Click 'Load Saved Field Mapping' if data has been previously imported from the same source. You can then select the saved import mapping setup and load it automatically.{/ts}<p>
        {/if}
        <p>{ts}If you think you may be importing additional data from the same data source, check 'Save this field mapping' at the bottom of the page before continuing. The saved mapping can then be easily reused the next time data is imported.{/ts}</p>
      </div>
      {* Table for mapping data to CRM fields *}
      {include file="CRM/HRLeaveAndAbsences/Import/Form/MapTable.tpl}
    </div>
  </div>
  <div class="panel panel-default crm_wizard__footer">
    <div class="panel-body">
      <div class="row">
        <div class="action-buttons pull-right">
          {include file="CRM/common/formButtons.tpl" location="bottom"}
        </div>
      </div>
    </div>
  </div>
</div>
{$initHideBoxes}
{literal}
  <script type="text/javascript" >
    if (document.getElementsByName("saveMapping")[0].checked) {
      document.getElementsByName("updateMapping")[0].checked = true;
      document.getElementsByName("saveMapping")[0].checked = false;
    }
  </script>
{/literal}
