<script id="hrjob-pension-template" type="text/template">
  <h3>{ts}Pension{/ts}</h3>

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
      <label for="hrjob-contrib_pct">{ts}Contribution (%){/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-contrib_pct" name="contrib_pct" class="form-text-big" type="text" />
    </div>
  </div>

  <%= RenderUtil.standardButtons() %>
</script>
