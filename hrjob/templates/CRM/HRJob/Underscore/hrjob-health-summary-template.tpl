<script id="hrjob-health-summary-template" type="text/template">
  <div class="crm-summary-row">
    <div class="crm-label">{ts}Healthcare{/ts}</div>
    <div class="crm-content">
      {literal}
      <% if (plan_type) { %>
        <%- FieldOptions.plan_type[plan_type] %>
      <% } %>

      <% if (provider) { %>
        (With <%- FieldOptions.provider[provider] %>)
      <% } %>

      <% if (description) { %>
      <br/><strong>{/literal}{ts}Description{/ts}{literal}</strong>: <span name="description"/>
      <% } %>

      <% if (dependents) { %>
      <br/><strong>{/literal}{ts}Dependents{/ts}{literal}</strong>: <span name="dependents"/>
      <% } %>
      {/literal}
    </div>
  </div>
</script>