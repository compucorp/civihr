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
{* Leave Request Import Wizard - Step 3 (preview import results prior to actual data loading) *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
<div id="bootstrap-theme" class="crm_wizard crm-form-block crm-leave-and-balance-import crm-activity-import-preview-form-block">
  {include file="CRM/HRUI/common/WizardHeader.tpl"}
  <div class="panel panel-default crm_wizard__body">
    <div class="panel-body">
      <p class="alert alert-info">
        {ts}
          The information below previews the results of importing your data in CiviCRM.
          Review the totals to ensure that they represent your expected results.
        {/ts}
      </p>
      {if $invalidRowCount}
        <p class="alert alert-danger">
          {ts 1=$invalidRowCount 2=$downloadErrorRecordsUrl}
            CiviCRM has detected invalid data or formatting errors in %1 records.
            If you continue, these records will be skipped.
            OR, you can download a file with just these problem records -
            <a href='%2'>Download Errors</a>.
            Then correct them in the original import file,
            cancel this import and begin again at step 1.
          {/ts}
        </p>
      {/if}
      {if $conflictRowCount}
        <p class="alert alert-danger">
          {ts 1=$conflictRowCount 2=$downloadConflictRecordsUrl}
            CiviCRM has detected %1 records with conflicting transaction ids within this data file.
            If you continue, these records will be skipped.
            OR, you can download a file with just these problem records -
            <a href='%2'>Download Conflicts</a>.
            Then correct them in the original import file,
            cancel this import and begin again at step 1.
          {/ts}
        </p>
      {/if}
      <p>{ts}Click 'Import Now' if you are ready to proceed.{/ts}</p>
      {* Summary Preview (record counts) *}
      <table id="preview-counts" class="report table">
        <tr>
          <td>{ts}Total Rows{/ts}</td>
          <td>{$totalRowCount}</td>
          <td>{ts}Total rows (leave records) in uploaded file.{/ts}</td>
        </tr>
        {if $invalidRowCount}
          <tr class="danger">
            <td>{ts}Rows with Errors{/ts}</td>
            <td>{$invalidRowCount}</td>
            <td>
              {ts}Rows with invalid data in one or more fields.
              These rows will be skipped (not imported).{/ts}

              {if $invalidRowCount}
                <p>
                  <a href="{$downloadErrorRecordsUrl}">&raquo; {ts}Download Errors{/ts}</a>
                <p>
              {/if}
            </td>
          </tr>
        {/if}
        {if $conflictRowCount}
          <tr class="danger">
            <td>{ts}Conflicting Rows{/ts}</td>
            <td>{$conflictRowCount}</td>
            <td>
              {ts}Rows with conflicting transaction ids within this file.
              These rows will be skipped (not imported).{/ts}
              {if $conflictRowCount}
                <p>
                  <a href="{$downloadConflictRecordsUrl}">
                    {ts}Download Conflicts{/ts}
                  </a>
                </p>
              {/if}
            </td>
          </tr>
        {/if}
        <tr>
          <td>{ts}Valid Rows{/ts}</td>
          <td>{$validRowCount}</td>
          <td>{ts}Total rows to be imported.{/ts}</td>
        </tr>
      </table>
      {* Table for mapping preview *}
      {include file="CRM/HRLeaveAndAbsences/Import/Form/MapTable.tpl}
    </div>
  </div>
  <div class="panel panel-default crm_wizard__footer">
    <div class="panel-footer clearfix">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
