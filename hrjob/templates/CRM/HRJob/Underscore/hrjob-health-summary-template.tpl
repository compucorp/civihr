<script id="hrjob-health-summary-template" type="text/template">
  {literal}
  <% if (plan_type) { %>
    <%- FieldOptions.plan_type[plan_type] %>
  <% } %>

  <% if (provider) { %>
    (With <a href="#" class="hrjob-provider" /> )
  <% } %>

  <% if (description) { %>
  <br/><strong>{/literal}{ts}Description{/ts}{literal}</strong>: <span name="description"/>
  <% } %>

  <% if (dependents) { %>
  <br/><strong>{/literal}{ts}Dependents{/ts}{literal}</strong>: <span name="dependents"/>
  <% } %>
  {/literal}
</script>

<script id="hrjob-life-summary-template" type="text/template">
  {literal}
  <% if (plan_type_life_insurance) { %>
    <%- FieldOptions.plan_type[plan_type_life_insurance] %>
  <% } %>

  <% if (provider_life_insurance) { %>
    (With <a href="#" class="hrjob-provider_life_insurance" /> )
  <% } %>

  <% if (description_life_insurance) { %>
    <br/><strong>{/literal}{ts}Description{/ts}{literal}</strong>: <span name="description_life_insurance"/>
  <% } %>

  <% if (dependents_life_insurance) { %>
    <br/><strong>{/literal}{ts}Dependents{/ts}{literal}</strong>: <span name="dependents_life_insurance"/>
  <% } %>
  {/literal}
</script>