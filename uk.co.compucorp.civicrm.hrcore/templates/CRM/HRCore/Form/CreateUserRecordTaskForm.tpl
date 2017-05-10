<p>
  {ts 1=$totalSelectedContacts 2=$contactsForCreation|@count}
    From your selection of %1 contacts, %2 new user accounts are valid for creation.
  {/ts}
</p>

{if !empty($contactsWithoutEmail) }
  <p>
    {ts}A work e-mail is required to create the account.{/ts}
    {ts 1=$contactsWithoutEmail|@count}%1 contact(s) do not have a work email set:{/ts}
  </p>
  {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$contactsWithoutEmail}
  <br/>
{/if}

{if !empty($contactsWithAccount) }
  <p>
    {ts 1=$contactsWithAccount|@count}%1 contact(s) already have an account:{/ts}
  </p>
  {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$contactsWithAccount}
{/if}

<div class="crm-block crm-form-block">
  <div class="crm-inline-button crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl"}
  </div>
</div>
