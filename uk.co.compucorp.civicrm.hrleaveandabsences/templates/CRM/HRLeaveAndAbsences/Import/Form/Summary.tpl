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
{* Leave Request Import Wizard - Step 4 (summary of import results AFTER actual data loading) *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
<div id="bootstrap-theme" class="crm-leave-and-balance-import crm-activity-import-summary-form-block">
  <div class="panel">
    <div class="panel-default">
      <div class="panel-header">
        {* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
        {include file="CRM/common/WizardHeader.tpl"}

        <div class="action-buttons">
          {include file="CRM/common/formButtons.tpl" location="top"}
        </div>
      </div>

      <div class="panel-body">
        <p class="alert alert-success">
          {ts}<strong>Import has completed successfully.</strong>
          The information below summarizes the results.{/ts}
        </p>

        {if $unMatchCount }
          <div class="alert alert-danger">
            <p>{ts count=$unMatchCount plural='CiviCRM has detected mismatched leave IDs. These records have not been Updated.'}CiviCRM has detected mismatched leave ID. This record have not been updated.{/ts}</p>
            <p>{ts 1=$downloadMismatchRecordsUrl}You can <a href='%1'>Download Mismatched Leave records</a>. You may then correct them, and import the new file with the corrected data.{/ts}</p>
          </div>
        {/if}

        {if $invalidRowCount }
          <div class="alert alert-danger">
            <p>{ts count=$invalidRowCount plural='CiviCRM has detected invalid data and/or formatting errors in %count records. These records have not been imported.'}CiviCRM has detected invalid data and/or formatting errors in one record. This record have not been imported.{/ts}</p>
            <p>{ts 1=$downloadErrorRecordsUrl}You can <a href='%1'>Download Errors</a>. You may then correct them, and import the new file with the corrected data.{/ts}</p>
          </div>
        {/if}

        {if $conflictRowCount}
          <div class="alert alert-danger">
            <p>{ts count=$conflictRowCount plural='CiviCRM has detected %count records with conflicting transaction IDs within this data file or relative to existing leave records. These records have not been imported.'}CiviCRM has detected one record with conflicting transaction ID within this data file or relative to existing leave records. This record have not been imported.{/ts}</p>
            <p>{ts 1=$downloadConflictRecordsUrl}You can <a href='%1'>Download Conflicts</a>. You may then review these records to determine if they are actually conflicts, and correct the transaction IDs for those that are not.{/ts}</p>
          </div>
        {/if}

        {if $duplicateRowCount}
          <div {if $dupeError}class="alert alert-danger"{/if}>
            <p>{ts count=$duplicateRowCount plural='CiviCRM has detected %count records which are duplicates of existing CiviCRM leave records.'}CiviCRM has detected one record which is a duplicate of existing CiviCRM leave record.{/ts} {$dupeActionString}</p>
            <p>{ts 1=$downloadDuplicateRecordsUrl}You can <a href='%1'>Download Duplicates</a>. You may then review these records to determine if they are actually duplicates, and correct the transaction IDs for those that are not.{/ts}</p>
          </div>
        {/if}

        {* Summary of Import Results (record counts) *}
        <table id="summary-counts" class="report table">
          <tr>
            <td>{ts}Total Rows{/ts}</td>
            <td>{$totalRowCount}</td>
            <td>{ts}Total rows (leave records) in uploaded file.{/ts}</td>
          </tr>

          {if $invalidRowCount }
            <tr class="danger">
              <td>{ts}Invalid Rows (skipped){/ts}</td>
              <td>{$invalidRowCount}</td>
              <td>
                {ts}Rows with invalid data in one or more fields. These rows will be skipped (not imported).{/ts}
                {if $invalidRowCount}
                  <p><a href="{$downloadErrorRecordsUrl}">{ts}Download Errors{/ts}</a></p>
                {/if}
              </td>
            </tr>
          {/if}

          {if $unMatchCount }
            <tr class="danger">
              <td>{ts}Mismatched Rows (skipped){/ts}</td>
              <td>{$unMatchCount}</td>
              <td>
                {ts}Rows with mismatched leave IDs (NOT updated).{/ts}
                {if $unMatchCount}
                  <p><a href="{$downloadMismatchRecordsUrl}">{ts}Download Mismatched Leave records{/ts}</a></p>
                {/if}
              </td>
            </tr>
          {/if}

          {if $conflictRowCount}
            <tr class="danger">
              <td>{ts}Conflicting Rows (skipped){/ts}</td>
              <td>{$conflictRowCount}</td>
              <td>
                {ts}Rows with conflicting transaction IDs (NOT imported).{/ts}
                {if $conflictRowCount}
                  <p><a href="{$downloadConflictRecordsUrl}">{ts}Download Conflicts{/ts}</a></p>
                {/if}
              </td>
            </tr>
          {/if}

          {if $duplicateRowCount}
            <tr class="danger">
              <td class="label">{ts}Duplicate Rows{/ts}</td>
              <td class="data">{$duplicateRowCount}</td>
              <td class="explanation">
                {ts}Rows which are duplicates of existing Leave records.{/ts} {$dupeActionString}
                {if $duplicateRowCount}
                  <p><a href="{$downloadDuplicateRecordsUrl}">{ts}Download Duplicates{/ts}</a></p>
                {/if}
              </td>
            </tr>
          {/if}

          <tr>
            <td>{ts}Records Imported{/ts}</td>
            <td>{$validRowCount}</td>
            <td>{ts}Rows imported successfully.{/ts}</td>
          </tr>
        </table>
      </div>

      <div class="panel-footer">
       <div class="row">
         <div class="action-buttons pull-right">
           {include file="CRM/common/formButtons.tpl" location="bottom"}
         </div>
       </div>
      </div>
    </div>
  </div>
</div>
