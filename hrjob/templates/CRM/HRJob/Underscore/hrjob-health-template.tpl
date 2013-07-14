<script id="hrjob-health-template" type="text/template">
  <h3>{ts}Healthcare{/ts}</h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-provider">{ts}Provider{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-provider',
        name: 'provider',
        selected: provider,
        options: _.extend({'':''}, FieldOptions.provider)
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-plan_type">{ts}Plan Type{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-plan_type',
        name: 'plan_type',
        selected: plan_type,
        options: _.extend({'':''}, FieldOptions.plan_type)
      }) %>
    {/literal}
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
      <label for="hrjob-dependents">{ts}Dependents{/ts}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-dependents" name="dependents"></textarea>
    </div>
  </div>
</script>
