<script id="hrjob-health-summary-template" type="text/template">
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
</script>