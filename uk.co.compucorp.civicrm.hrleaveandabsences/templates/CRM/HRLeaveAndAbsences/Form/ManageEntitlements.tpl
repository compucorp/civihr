<div class="help">
  <p>&nbsp;{ts}WARNING: Please note that any currently stored annual entitlement allowance for the selected staff member(s) will be overwritten by this process{/ts}</p>
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
    <th>{ts}New Proposed Period Entitlement{/ts}</th>
    <th>{ts}Comment{/ts}</th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$calculations item=calculation}
    {assign var=absenceType value=$calculation->getAbsenceType()}
    {assign var=absenceTypeID value=$absenceType->id}
    {assign var=contract value=$calculation->getContract()}
    <tr>
      <td>{$contract.contact_id}</td>
      <td>{$contract.contact_display_name}</td>
      <td><span class="absence-type" style="background-color: {$absenceType->color};">{$absenceType->title}</span></td>
      <td>{$calculation->getPreviousPeriodProposedEntitlement()}</td>
      <td>{$calculation->getNumberOfLeavesTakenOnThePreviousPeriod()}</td>
      <td>{$calculation->getNumberOfDaysRemainingInThePreviousPeriod()}</td>
      <td>{$calculation->getBroughtForward()}</td>
      <td>{$calculation->getContractualEntitlement()}</td>
      <td>{$calculation->getProRata()}</td>
      <td>
        <div class="proposed-entitlement">
          <span class="proposed-value">{$calculation->getProposedEntitlement()}</span>
          {$form.proposed_entitlement[$contract.id][$absenceTypeID].html}
          <button type="button"><i class="fa fa-pencil"></i></button>
          <label for=""><input type="checkbox"> Override</label>
        </div>
      </td>
      <td></td>
    </tr>
  {/foreach}
  </tbody>
</table>
<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    new CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements();
  });
  {/literal}
</script>
<div class="action-link">
  <a href="javascript:history.back()" class="button"><span>{ts}Back{/ts}</span></a>
</div>
