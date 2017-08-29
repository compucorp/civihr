<div id="bootstrap-theme">

  <h3>
    {ts 1=$totalSelectedContacts}
      You have selected %1 contacts
    {/ts}
  </h3>

  {if !empty($contactsForCreation) }
    <h4>
      {ts 1=$contactsForCreation|@count}
        %1 contacts will have user accounts(s) created
      {/ts}
    </h4>
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
      {ts 1=$contactsWithAccount|@count}
        %1 contact(s) already have an account:
      {/ts}
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
      {ts 1=$emailConflictContact|@count}
        %1 contact(s) have email conflicts:
      {/ts}
    </p>
    {include file="CRM/HRCore/Common/ContactTable.tpl" contacts=$emailConflictContact}
    <br/>
  {/if}

  <br/>


  <div class="panel">

    {if !empty($form.roles)}
    <div class="panel-body">
      <label>User Roles</label>
      <p class = "description">
        {ts}
          Select any of the following user roles to add to the new user accounts:
        {/ts}
      </p>
      <div>
        {foreach from=$form.roles item=role}
          {$role.html}
          {$role.label}
        {/foreach}
      </div>
    </div>
    {/if}

    <div class="panel-body">
      {$form.sendEmail.html}
      {$form.sendEmail.label}
      <br/>
      <p class = "description">
        {ts 1='/civicrm/tasksassignments/dashboard#/tasks'}
          By selecting this option, a welcome email containing a link to the
          staff onboarding wizard will be sent to all staff who already have a
          user account and those who meet the criteria of creating a user
          account. It is recommended to <a href="%1">create onboarding tasks</a>
          and documents for the selected staff before this action.
        {/ts}
      </p>
    </div>

  </div>

  <div class="spacer"></div>

  <div class="panel">
    <div class="panel-body">
      <div class="crm-inline-button crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl"}
      </div>
    </div>
  </div>

</div>
