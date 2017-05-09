<h3>{ts 1=$totalSelectedContacts}%1 new user accounts will be created.{/ts}</h3>
<p class="warning">
  {ts}A work e-mail is required to create the account.{/ts}
  {ts 1=$numWithoutEmail}%1 contact(s) do not have a work email set.{/ts}
  {* todo show list of contacts missing work email *}
</p>

<div class="crm-submit-buttons">{$form.buttons.html}</div>
