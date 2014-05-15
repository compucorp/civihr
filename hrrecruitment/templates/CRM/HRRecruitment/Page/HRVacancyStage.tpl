{*
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
*}
<div class="crm-clearfix hr-pipeline-tab">
  <div class="hr-pipeline-case-contacts">
    <table class="row-highlight">
      <thead>
        <tr>
          <th><input type="checkbox" class="select-rows" /></th>
          <th>{ts}Applicant{/ts}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$contacts item="contact"}
          <tr data-case_id="{$contact.case_id}" data-cid="{$contact.contact_id}" {cycle values="odd-row,even-row"}">
            <td><input type="checkbox" class="select-row" value="{$contact.case_id}" /></td>
            <td><a class="hr-pipeline-contact-link" href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$contact.contact_id`"}">{$contact.sort_name}</a></td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>
  <div class="hr-pipeline-case-view-panel">
    <div class="hr-pipeline-case-actions" style="opacity: .5">
      {if $administerper}
        <a class="button hr-activity-button" href="#civicrm/activity/email/add" data-atype="{$emailActivity}" data-context="activity"><div class="icon ui-icon-mail-closed"></div>{ts}Email{/ts}</a>
        <a class="button hr-activity-button" href="#civicrm/case/activity" data-atype="{$commentActivity}"><div class="icon ui-icon-comment"></div>{ts}Comment{/ts}</a>
        {if $evaluateper}
          <a class="button hr-eval-button" href="#civicrm/case/activity" data-atype="{$evaluationActivity}"><div class="icon ui-icon-note"></div>{ts}Evaluation{/ts}</a>
        {/if}

        <select class="crm-select2 crm-form-select crm-action-menu hr-activity-menu action-icon-plus">
        <option value="">{ts}Add activity{/ts}</option>
        {foreach from=$activities key="id" item="title"}
          <option value="{$id}">{$title}</option>
        {/foreach}
        </select>
        <select class="crm-select2 crm-form-select crm-action-menu hr-case-status-menu action-icon-play" data-atype="{$changeCaseStatusActivity}">
        <option value="">{ts}Status{/ts}</option>
        {foreach from=$caseStatus item="status"}
          <option value="{$status.id}" {if $statusId eq $status.id}class="bold" disabled="disabled"{/if}>{$status.title}</option>
        {/foreach}
        </select>
      {/if}
    </div>
    <div class="hr-pipeline-case-details">
      <p class="hr-applicant-selection-msg">{ts}0 applicants selected{/ts}</p>
    </div>
  </div>
</div>
