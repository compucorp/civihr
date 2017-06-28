<div class = 'crm-container'>

  <h3>
    {ts 1=$totalSelectedContacts 2=$contactsForCreation|@count}
      From your selection of %1 contacts, %2 new user accounts are valid for creation.
    {/ts}
  </h3>

  {if !empty($contactsForCreation) }
    <p>
      {ts}Accounts for these contacts will be created:{/ts}
    </p>
    {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$contactsForCreation}
    <br/>
  {/if}

  {if !empty($invalidEmailContacts) }
    <p>
      {ts}
        Some contacts have invalid emails. Contacts must have a primary e-mail,
        and it cannot contain punctuation except for periods, hyphens,
        apostrophes and underscores.
      {/ts}
    </p>
    <p>
      {ts 1=$invalidEmailContacts|@count}%1 contact(s) have invalid emails:{/ts}
    </p>
    {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$invalidEmailContacts}
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
      {$form.sendEmail.html}
      {$form.sendEmail.label}
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

</div>
