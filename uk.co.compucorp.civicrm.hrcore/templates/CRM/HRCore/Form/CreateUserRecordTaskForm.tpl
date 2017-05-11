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
  <br/>
{/if}

{if !empty($emailConflictContact) }
  <p>
    {ts}
    Email conflicts can be caused by trying to create two new users with the
    same email, or by trying to create a new user with an email that is already
    in use.
    {/ts}
  </p>
  <p>
    {ts 1=$emailConflictContact|@count}%1 contact(s) have email conflicts:{/ts}
  </p>
  {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$emailConflictContact}
  <br/>
{/if}

<br/>

<div class="crm-block crm-form-block" style="padding: 20px 10px">
  <div class="checkbox">
    {$form.sendEmail.label}
    {$form.sendEmail.html}
    <br/>
    <h4 class = "description">
        {ts}Invitation emails will be sent if this box is checked{/ts}
      </h4>
  </div>
</div>

<div class="spacer"></div>

<div class="crm-block crm-form-block">
  <div class="crm-inline-button crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl"}
  </div>
</div>
