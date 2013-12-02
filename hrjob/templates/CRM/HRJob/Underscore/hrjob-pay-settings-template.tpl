<script id="hrjob-pay-settings-template" type="text/template">
<form>
  <div class="crm-summary-row hrjob-needs-hours_per_year">
    <div class="crm-label">
      <label for="hrjob-hours_per_year">{ts}hours per year{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-hours_per_year" name="hours_per_year" type="text" />
    </div>
  </div>
  <div class="crm-summary-row hrjob-needs-days_per_year">
    <div class="crm-label">
      <label for="hrjob-days_per_year">{ts}days per year{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-days_per_year" name="days_per_year" type="text" />
    </div>
  </div>
  <div class="crm-summary-row hrjob-needs-weeks_per_year">
    <div class="crm-label">
      <label for="hrjob-hours_per_year">{ts}weeks per year{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-weeks_per_year" name="weeks_per_year" type="text" />
    </div>
  </div>
  <div class="crm-summary-row hrjob-needs-months_per_year">
    <div class="crm-label">
      <label for="hrjob-hours_per_year">{ts}months per year{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-months_per_year" name="months_per_year" type="text" />
    </div>
  </div>
  <%= RenderUtil.standardButtons() %>
</form>
</script>
