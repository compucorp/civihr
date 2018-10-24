{include file="CRM/common/pager.tpl" location="top"}
<a href="#" class="crm-selection-reset crm-hover-button"><i class="crm-i fa-times-circle-o"></i> {ts}Reset all selections{/ts}</a>

<table summary="{ts}Search results listings.{/ts}" class="selector row-highlight">
  <thead class="sticky">
  <tr>
    <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
    {if $context eq 'smog'}
      <th scope="col">
        {ts}Status{/ts}
      </th>
    {/if}
    {foreach from=$columnHeaders item=header}
      <th scope="col">
        {if $header.sort}
          {assign var='key' value=$header.sort}
          {$sort->_response.$key.link}
        {else}
          {$header.name}
        {/if}
      </th>
    {/foreach}
    <th scope="col">
      {ts}Actions{/ts}
    </th>
  </tr>
  </thead>

  {counter start=0 skip=1 print=false}

  {foreach from=$rows item=row}
    <tr id="rowid{$row.contact_id}" class="{cycle values='odd-row,even-row'}">
      {assign var=cbName value=$row.checkbox}
      <td>{$form.$cbName.html}</td>
      {if $context eq 'smog'}
        {if $row.status eq 'Pending'}<td class="status-pending"}>
        {elseif $row.status eq 'Removed'}<td class="status-removed">
        {else}<td>{/if}
        {$row.status}</td>
      {/if}
      {foreach from=$columnHeaders item=value key=column}
        {assign var='columnName' value=$value.sort}
        {if $columnName neq 'action'}
          <td class="crm-{$columnName} crm-{$columnName}_{$row.columnName}">{$row.$columnName} </td>
        {/if}

      {/foreach}

      <td style='width:125px;'>{$row.action|replace:'xx':$row.contact_id}</td>
    </tr>
  {/foreach}

</table>

<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    // Clear any old selection that may be lingering in quickform
    $("input.select-row, input.select-rows", 'form.crm-search-form').prop('checked', false).closest('tr').removeClass('crm-row-selected');
    // Retrieve stored checkboxes
    var cids = {/literal}{$selectedContactIds|@json_encode}{literal};
    if (cids.length > 0) {
      $('#mark_x_' + cids.join(',#mark_x_') + ',input[name=radio_ts][value=ts_sel]').prop('checked', true);
    }
  });
  {/literal}
</script>
{include file="CRM/common/pager.tpl" location="bottom"}
