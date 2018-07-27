<table class="table table-hover">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$contacts item=contact}
      <tr>
        <td>{$contact.display_name|escape}</td>
        <td>{if $contact.email}{$contact.email|escape}{else}-{/if}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
