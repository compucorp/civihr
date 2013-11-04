<script id="hrjob-general-template" type="text/template">
<form>
  <h3>
    {ts}General{/ts}{literal} <%- (isNewDuplicate) ? '(' + ts('New Copy of "%1"', {1: position}) + ')' : '' %>{/literal} 
    {literal}<% if (!isNew) { %> {/literal}
    <a class='css_right hrjob-revision-link' data-table-name='civicrm_hrjob' href='#' title='{ts}View Revisions{/ts}'>(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-position">{ts}Position{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-position" name="position" class="form-text-big" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-title">{ts}Title{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-title" name="title" class="form-text-big" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-contract_type">{ts}Contract Type{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-contract_type',
        name: 'contract_type',
        options: _.extend({'':''}, FieldOptions.contract_type)
      }) %>
    {/literal}
    {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='hrjob_contract_type'}
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$result.id}" target="_blank"><span class="batch-edit"></span></a>
    {/if}
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
      {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
        <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$result.id}" target="_blank"><span class="batch-edit"></span></a>
      {/if}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-level_type">{ts}Level{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-level_type',
        name: 'level_type',
        options: _.extend({'':''}, FieldOptions.level_type)
      }) %>
    {/literal}
    {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='hrjob_level_type'}
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$result.id}" target="_blank"><span class="batch-edit"></span></a>
    {/if}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-manager_contact_id">{ts}Manager{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-manager_contact_id" name="manager_contact_id" class="crm-contact-selector" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-level_type">{ts}Normal Place of Work{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-level_type',
      name: 'location',
      options: _.extend({'':''}, FieldOptions.location)
      }) %>
    {/literal}
    {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='hrjob_location'}
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$result.id}" target="_blank"><span class="batch-edit"></span></a>
    {/if}
    </div>
  </div>

  <div class="crm-summary-row hrjob-is_primary-row">
    <div class="crm-label">
      <label for="hrjob-is_primary">{ts}Is Primary{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-is_primary" name="is_primary" type="checkbox" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_type">{ts}Contract Duration{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-period_type',
        name: 'period_type',
        options: _.extend({'':''}, FieldOptions.period_type)
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_start_date">{ts}Start Date{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_start_date" name="period_start_date" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_end_date">{ts}End Date{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_end_date" name="period_end_date" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-notice_amount">{ts}Notice Period{/ts}</label>
    </div>
    <div class="crm-content">
      <div>
        <input id="hrjob-notice_amount" name="notice_amount" type="text" />
      </div>
      {literal}
      <%= RenderUtil.select({
        id: 'hrjob-notice_unit',
        name: 'notice_unit',
        options: _.extend({'':''}, FieldOptions.notice_unit)
      }) %>
      {/literal}
    </div>
  </div>

  {literal}<% if (!isNewDuplicate) { %> {/literal}
  <button class="crm-button standard-save">{ts}Save{/ts}</button>
  {literal}<% } else { %>{/literal}
  <button class="crm-button standard-save">{ts}Save New Copy{/ts}</button>
  {literal}<% } %>{/literal}
  <button class="crm-button standard-reset">{ts}Reset{/ts}</button>
</form>
</script>
