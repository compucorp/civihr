<div class="help">
  <p>&nbsp;{ts}WARNING: Please note that any currently stored annual entitlement allowance for the selected staff member(s) will be overwritten by this process{/ts}</p>
</div>

{* These hidden fields are used when we submit the form to export the CSV file *}
{* The id is used so we know from which period we are exporting the CSV *}
{* The cid is used so can export calculations only for the specified contracts *}
<input type="hidden" name="export_csv" id="export_csv" value="0">
<input type="hidden" name="id" id="period_id" value="{$period->id}">
{foreach from=$contractsIDs item=id}
  <input type="hidden" name="cid[]" value="{$id}">
{/foreach}

<div class="entitlement-calculation-filters row">
  <div class="col-sm-4">
  </div>
  <div class="col-sm-4">
    <div class="override-filters">
      <input type="radio" id="override_filter_overridden" name="override-filter" class="override-filter" value="1">
      <label for="override_filter_overridden">Overridden</label>

      <input type="radio" id="override_filter_not_overridden" name="override-filter" class="override-filter" value="2">
      <label for="override_filter_not_overridden">Not Overridden</label>

      <input type="radio" id="override_filter_both" name="override-filter" class="override-filter" value="3" checked="checked">
      <label for="override_filter_both">Both</label>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="absence-type-filter">
      <select name="absence-type-filter" id="absence_type_filter" class="crm-select2" multiple="multiple" data-placeholder="{ts}Leave Type{/ts}">
        {foreach from=$enabledAbsenceTypes item=absenceType}
          <option value="{$absenceType->id}">{$absenceType->title}</option>
        {/foreach}
      </select>
    </div>
    <a href="{crmURL q="id=`$period->id`&csv=1&reset=1"}" class="export-csv-action">{ts}Export to CSV{/ts}</a>
  </div>
</div>
<table class="entitlement-calculation-list">
  <thead>
  <tr>
    <th>{ts}Employee ID{/ts}</th>
    <th>{ts}Employee name{/ts}</th>
    <th>{ts}Leave type{/ts}</th>
    <th>{ts}Prev. yr entitlement{/ts}</th>
    <th>{ts}Days taken{/ts}</th>
    <th>{ts}Remaining{/ts}</th>
    <th>{ts}Brought Forward from previous period{/ts}</th>
    <th>{ts}Current Contractual Entitlement{/ts}</th>
    <th>{ts}New Period Pro rata{/ts}</th>
    <th class="proposed-entitlement-header">
      <div class="title">{ts}New Proposed Period Entitlement{/ts}</div>
      <div class="actions">
        <button type="button" class="add-one-day" title="{ts}Add an extra day to all contacts listed{/ts}">
          <i class="fa fa-plus-square"></i>
        </button>
        <button type="button" class="copy-to-all" title="{ts}Copy the new proposed entitlement from top row to all others{/ts}">
          <i class="fa fa-copy"></i>
        </button>
      </div>
    </th>
    <th>{ts}Comment{/ts}</th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$calculations item=calculation}
    {assign var=absenceType value=$calculation->getAbsenceType()}
    {assign var=absenceTypeID value=$absenceType->id}
    {assign var=contract value=$calculation->getContract()}
    <tr data-calculation-details="{$calculation}" data-absence-type="{$absenceTypeID}">
      <td>{$contract.contact_id}</td>
      <td>{$contract.contact_display_name}</td>
      <td><span class="absence-type" style="background-color: {$absenceType->color};">{$absenceType->title}</span></td>
      <td>{$calculation->getPreviousPeriodProposedEntitlement()}</td>
      <td>{$calculation->getNumberOfLeavesTakenOnThePreviousPeriod()}</td>
      <td>{$calculation->getNumberOfDaysRemainingInThePreviousPeriod()}</td>
      <td>{$calculation->getBroughtForward()}</td>
      <td>{$calculation->getContractualEntitlement()}</td>
      <td>{$calculation->getProRata()}</td>
      <td class="proposed-entitlement">
          <span class="proposed-value">{$calculation->getProposedEntitlement()}</span>
          {$form.proposed_entitlement[$contract.id][$absenceTypeID].html}
          <button type="button" class="borderless-button"><i class="fa fa-pencil"></i></button>
          <label for="override_checkbox_{$contract.id}_{$absenceTypeID}">
            <input id="override_checkbox_{$contract.id}_{$absenceTypeID}"
                   type="checkbox"
                   class="override-checkbox"
                   {if $calculation->isCurrentPeriodEntitlementOverridden()}checked{/if}> Override
          </label>
      </td>
      <td class="comment">
        {$form.comment[$contract.id][$absenceTypeID].html}
        <button type="button" class="borderless-button add-comment"><i class="fa fa-share-square-o"></i></button>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
<div class="crm-submit-buttons">
  <a href="javascript:history.back()" class="button"><span>{ts}Back{/ts}</span></a>
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
<div id="add-comment-dialog" title="{ts}Add/Edit comment{/ts}">
  <p>{ts}You can leave a comment as a record of your calculation for the leave entitlement for this period. Comments are then shown as tooltips on the leave entitlement on the contact record for administrators to refer back to.{/ts}</p>
  <textarea name="calculation_comment" class="calculation_comment" cols="30" rows="10"></textarea>
</div>
<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    new CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements();
  });
  {/literal}
</script>
