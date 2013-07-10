<script id="hrjob-intro-template" type="text/template">
  {ts}This is the CiviHR Job tab{/ts}
</script>

<script id="hrjob-tree-template" type="text/template">
  <div class="hrjob-tree-items"></div>
</script>

<script id="hrjob-tree-item-template" type="text/template">
  <dl>
    <dt><a href="#<%= cid %>/hrjob/<%= id %>" class="hrjob-nav" data-hrjob-event="hrjob:summary:show"><%= position %></a></dt>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/general" class="hrjob-nav" data-hrjob-event="hrjob:general:edit">{ts}General{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/health" class="hrjob-nav" data-hrjob-event="hrjob:health:edit">{ts}Healthcare{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/hour" class="hrjob-nav" data-hrjob-event="hrjob:hour:edit">{ts}Hours{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/leave" class="hrjob-nav" data-hrjob-event="hrjob:leave:edit">{ts}Leave{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/pay" class="hrjob-nav" data-hrjob-event="hrjob:pay:edit">{ts}Pay{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/pension" class="hrjob-nav" data-hrjob-event="hrjob:pension:edit">{ts}Pension{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/role" class="hrjob-nav" data-hrjob-event="hrjob:role:edit">{ts}Roles{/ts}</a></dd>
  </dl>
</script>

<script id="hrjob-summary-template" type="text/template">
  <div>
    <span>{ts}Position{/ts}:</span>
    <span><%- position %></span>
  </div>
  <div>
    <span>{ts}Contract Type{/ts}:</span>
    <span><%- contract_type %></span>
  </div>
</script>

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
        <% for (var selectValue in FieldOptions.HRJob.contract_type) { %>
          <option value="<%- selectValue %>" <%= contract_type == selectValue ? 'selected' : ''  %>><%- FieldOptions.HRJob.contract_type[selectValue] %></option>
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
        <% for (var selectValue in FieldOptions.HRJob.seniority) { %>
        <option value="<%- selectValue %>" <%= seniority == selectValue ? 'selected' : ''  %>><%- FieldOptions.HRJob.seniority[selectValue] %></option>
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
        <% for (var selectValue in FieldOptions.HRJob.period_type) { %>
        <option value="<%- selectValue %>" <%= period_type == selectValue ? 'selected' : ''  %>><%- FieldOptions.HRJob.period_type[selectValue] %></option>
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

<script id="hrjob-hour-template" type="text/template">
  TODO: Hours
</script>

<script id="hrjob-pay-template" type="text/template">
  TODO: Pay
</script>

<script id="hrjob-health-template" type="text/template">
  TODO: Health
</script>

<script id="hrjob-leave-template" type="text/template">
  TODO: Leave
</script>

<script id="hrjob-pension-template" type="text/template">
  TODO: Pension
</script>

<script id="hrjob-role-template" type="text/template">
  TODO: Roles
</script>
