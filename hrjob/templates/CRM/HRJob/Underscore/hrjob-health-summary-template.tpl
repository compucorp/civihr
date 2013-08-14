<script id="hrjob-health-summary-template" type="text/template">
  <h3>{ts}Healthcare{/ts}</h3>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Provider{/ts}</div>
    <div class="crm-content"><%- FieldOptions.provider[provider] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Plan Type{/ts}</div>
    <div class="crm-content"><%- FieldOptions.plan_type[plan_type] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Description{/ts}</div>
    <div class="crm-content"><span name="description"/></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Dependents{/ts}</div>
    <div class="crm-content"><span name="dependents"/></div>
  </div>
</script>