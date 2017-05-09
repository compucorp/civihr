<h3>{ts 1=$totalSelectedContacts}Number of selected contacts: %1{/ts}</h3>

<table id="create-user-record-task-table" class="display">
  <thead>
  <tr class="columnheader">
    <th class="contact_details">{ts}Name{/ts}</th>
    <th>{ts}Send Email{/ts}</th>
  </tr>
  </thead>

  <tbody>
  {foreach from=$value item=contactEmail key=contactID}
    <tr class="{cycle values="odd-row,even-row"}">
      <td class="name">{$contactEmail}</td>
      <td>{$form.send_email.$contactID.html}</td>
    </tr>
  {/foreach}
  </tbody>
</table>

<div class="crm-submit-buttons">{$form.buttons.html}</div>
