<script id="hrjob-pension-template" type="text/template">
<form>
  <h3>
    {ts}Pension{/ts}
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_pension" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-is_enrolled">{ts}Is Enrolled{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-is_enrolled',
        name: 'is_enrolled',
        options: {
          '': '',
          '0': '{/literal}{ts}No{/ts}{literal}',
          '1': '{/literal}{ts}Yes{/ts}{literal}'
        }
      }) %>
    {/literal}
    </div>
  </div>
  
  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-provider_life_insurance">{ts}Provider{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-pension_type',
        name: 'pension_type',
        options: _.extend({'':''}, FieldOptions.pension_type)
      }) %>
    {/literal}
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href="{crmURL p='civicrm/admin/options' q='reset=1'}" target="_blank"><span class="batch-edit"></span></a>
    {/if}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-er_contrib_pct">{ts}Employer Contribution (%){/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-er_contrib_pct" name="er_contrib_pct" class="form-text-big" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-ee_contrib_pct">{ts}Employee Contribution (%){/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-ee_contrib_pct" name="ee_contrib_pct" class="form-text-big" type="text" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-ee_contrib_abs">{ts}Employee Contribution (absolute amount){/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-ee_contrib_abs" name="ee_contrib_abs" class="form-text-big" type="text" />
    </div>
  </div>
 
  <%= RenderUtil.standardButtons() %>
</form>
</script>
