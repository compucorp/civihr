<script id="hrjob-role-template" type="text/template">
  <form>
    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-title">{ts}Title{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-title" name="title"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-description">{ts}Description{/ts}</label>
      </div>
      <div class="crm-content">
        <textarea id="hrjob-description" name="description"></textarea>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-hours">{ts}Hours{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-hours" name="hours"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-cost_center">{ts}Cost Center{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-cost_center" name="cost_center"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-department">{ts}Department{/ts}</label>
      </div>
      <div class="crm-content">
      {literal}
        <%= RenderUtil.select({
        id: 'hrjob-department',
        name: 'department',
        options: _.extend({'':''}, FieldOptions.department)
        }) %>
      {/literal}
      {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='hrjob_department'}
      {foreach from=$result.values item=OptionGroup}
        {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
          <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$OptionGroup.id}" target="_blank"><span class="batch-edit"></span></a>
        {/if}
      {/foreach}
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-functional_area">{ts}Functional Area{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-functional_area" name="functional_area"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-location">{ts}Location{/ts}</label>
      </div>
      <div class="crm-content">
      {literal}
        <%= RenderUtil.select({
        id: 'hrjob-location',
        name: 'location',
        options: _.extend({'':''}, FieldOptions.location)
        }) %>
      {/literal}
      {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='hrjob_location'}
      {foreach from=$result.values item=OptionGroup}
        {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
          <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$OptionGroup.id}" target="_blank"><span class="batch-edit"></span></a>
        {/if}
      {/foreach}
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-manager_contact_id">{ts}Manager{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-manager_contact_id" name="manager_contact_id" class="crm-contact-selector" />
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-organization">{ts}Organization{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-organization" name="organization"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-region">{ts}Region{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-region" name="region"/>
      </div>
    </div>

  </form>
</script>