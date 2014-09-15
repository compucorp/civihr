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
      <input id="hrjob-position" name="position" class="crm-form-text" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-title">{ts}Title{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-title" name="title" class="crm-form-text" type="text" />
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
        entity: 'HRJob'
      }) %>
    {/literal}
    {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_contract_type'}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-location">{ts}Normal Place of Work{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-location',
      name: 'location',
      entity: 'HRJob'
      }) %>
    {/literal}
    {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_location'}
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
      <label for="hrjob-period_start_date">{ts}Contract Start Date{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_start_date" name="period_start_date" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_end_date">{ts}Contract End Date{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_end_date" name="period_end_date" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label>{ts}Contract Duration{/ts}</label>
    </div>
    <div class="crm-content"><span name="duration"></span></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-notice_amount">{ts}Notice Period from Employer{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-notice_amount" name="notice_amount" type="text" />
      {literal}
      <%= RenderUtil.select({
        id: 'hrjob-notice_unit',
        name: 'notice_unit',
        entity: 'HRJob'
      }) %>
      {/literal}
    </div>
  </div>

   <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-notice_amount_employee">{ts}Notice Period from Employee{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-notice_amount_employee" name="notice_amount_employee" type="text" />
      {literal}
      <%= RenderUtil.select({
        id: 'hrjob-notice_unit_employee',
        name: 'notice_unit_employee',
        entity: 'HRJob'
      }) %>
      {/literal}
    </div>
  </div>

 <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-contract_file">{ts}Contract File{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="contract_file" type='file' name='contract_file'/>
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
