<table class="selector row-highlight">
  <thead class="sticky">
    <tr>
      <td>Name</td>
      <td>Email</td>
    </tr>
  </thead>
  <tbody>
    {foreach from=$contacts item=contact}
      <tr>
        <td>{$contact.display_name}</td>
        <td>{if $contact.email}{$contact.email}{else}-{/if}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
