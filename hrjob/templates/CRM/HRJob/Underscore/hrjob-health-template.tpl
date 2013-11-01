<script id="hrjob-health-template" type="text/template">
<form>
  <h3>
    {ts}Health Insurance{/ts} 
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_health" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-provider">{ts}Provider{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-provider" name="provider" class="crm-contact-selector" urlParam = "org=1&contact_sub_type=Health_Insurance_Provider" type="text" />
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
  <h3>
    {ts}Life Insurance{/ts} 
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_health" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-provider_life_insurance">{ts}Provider{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-provider_life_insurance" name="provider_life_insurance" class="crm-contact-selector" urlParam = "org=1&contact_sub_type=Life_Insurance_Provider" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-plan_type_life_insurance">{ts}Plan Type{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-plan_type_life_insurance',
        name: 'plan_type_life_insurance',
        options: _.extend({'':''}, FieldOptions.plan_type_life_insurance)
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-description_life_insurance">{ts}Description{/ts}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-description_life_insurance" name="description_life_insurance"></textarea>
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-dependents_life_insurance">{ts}Dependents{/ts}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-dependents_life_insurance" name="dependents_life_insurance"></textarea>
    </div>
  </div>
  <%= RenderUtil.standardButtons() %>
</form>
</script>
