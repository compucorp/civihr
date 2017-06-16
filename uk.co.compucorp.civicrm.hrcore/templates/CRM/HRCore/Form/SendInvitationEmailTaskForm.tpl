<div class = 'crm-container'>

  <h3>
    {ts 1=$totalSelectedContacts 2=$contactsForSending|@count}
      From your selection of %1 contacts, %2 users can receive mails.
    {/ts}
  </h3>

  <br/>

  {if !empty($contactsWithoutEmail) }
    <p>
      {ts}An email is required to send the invitation.{/ts}
    </p>
    <p>
      {ts 1=$contactsWithoutEmail|@count}%1 contacts do not have an email set:{/ts}
    </p>
    {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$contactsWithoutEmail}
    <br/>
  {/if}

  {if !empty($contactsWithoutAccount) }
    <p>
      {ts}
        An account is required before you can send the invitation mail.
        You can create an account using the "Create User Record" action.
      {/ts}
    </p>
    <p>
      {ts 1=$contactsWithoutAccount|@count}%1 contacts do not have an account yet:{/ts}
    </p>
    {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$contactsWithoutAccount}
    <br/>
  {/if}

  <div class="spacer"></div>

  <div class="crm-block crm-form-block">
    <div class="crm-inline-button crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl"}
    </div>
  </div>

</div>
