<script id="hrjob-general-template" type="text/template">

  <h3>{ts}General{/ts}{literal} <%- (isNewDuplicate) ? '(' + ts('New Copy of "%1"', {1: position}) + ')' : '' %>{/literal}</h3>

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

  <h3>{ts}Time Period{/ts}</h3>

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
      <input id="hrjob-period_start_date" name="period_start_date" class="form-text-big" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_end_date">{ts}End Date{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_end_date" name="period_end_date" class="form-text-big" type="text" />
    </div>
  </div>


  <h3>{ts}Funding{/ts}</h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-is_tied_to_funding">{ts}Tied to Funding{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-is_tied_to_funding" name="is_tied_to_funding" type="checkbox" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-funding_notes">{ts}Funding Notes{/ts}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-funding_notes" name="funding_notes"></textarea>
    </div>
  </div>

  {literal}<% if (!isNewDuplicate) { %> {/literal}
  <button class="standard-save">{ts}Save{/ts}</button>
  {literal}<% } else { %>{/literal}
  <button class="standard-save">{ts}Save New Copy{/ts}</button>
  {literal}<% } %>{/literal}
  <button class="standard-reset">{ts}Reset{/ts}</button>
</script>
