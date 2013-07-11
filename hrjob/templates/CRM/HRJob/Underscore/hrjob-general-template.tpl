<script id="hrjob-general-template" type="text/template">

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-position">{ts}Position{/ts}:</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-position" name="position" class="form-text-big" type="text" value="<%- position %>" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-title">{ts}Title{/ts}:</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-title" name="title" class="form-text-big" type="text" value="<%- title %>" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-contract_type">{ts}Contract Type{/ts}:</label>
    </div>
    <div class="crm-content">
    {literal}
      <select id="hrjob-contract_type" name="contract_type">
        <option value=""></option>
        <% for (var selectValue in FieldOptions.contract_type) { %>
        <option value="<%- selectValue %>" <%= contract_type == selectValue ? 'selected' : ''  %>><%- FieldOptions.contract_type[selectValue] %></option>
        <% } %>
      </select>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-seniority">{ts}Seniority{/ts}:</label>
    </div>
    <div class="crm-content">
    {literal}
      <select id="hrjob-seniority" name="seniority">
        <option value=""></option>
        <% for (var selectValue in FieldOptions.seniority) { %>
        <option value="<%- selectValue %>" <%= seniority == selectValue ? 'selected' : ''  %>><%- FieldOptions.seniority[selectValue] %></option>
        <% } %>
      </select>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_type">{ts}Time Period{/ts}:</label>
    </div>
    <div class="crm-content">
    {literal}
      <select id="hrjob-period_type" name="period_type">
        <option value=""></option>
        <% for (var selectValue in FieldOptions.period_type) { %>
        <option value="<%- selectValue %>" <%= period_type == selectValue ? 'selected' : ''  %>><%- FieldOptions.period_type[selectValue] %></option>
        <% } %>
      </select>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_start_date">{ts}Start Date{/ts}:</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_start_date" name="period_start_date" class="form-text-big" type="text" value="<%- period_start_date %>" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-period_end_date">{ts}End Date{/ts}:</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-period_end_date" name="period_end_date" class="form-text-big" type="text" value="<%- period_end_date %>" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-manager_contact_id">{ts}Manager{/ts}:</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-manager_contact_id" name="manager_contact_id" class="form-text-big" type="text" value="<%- manager_contact_id %>" />
    </div>
  </div>
</script>
