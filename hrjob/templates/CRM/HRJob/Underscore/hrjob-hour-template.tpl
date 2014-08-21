<script id="hrjob-hour-template" type="text/template">
  <form>
  <h3>
    {ts}Hours{/ts}
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_hour" href="#" title="{ts}View Revisions{/ts}">({ts}View Revisions{/ts})</a>
    {literal}<% } %>{/literal}
  </h3>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-hours_type">{ts}Hours Type{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
        id: 'hrjob-hours_type',
        name: 'hours_type',
        entity: 'HRJobHour'
      }) %>
    {/literal}
    {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_hours_type'}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-type">
    <div class="crm-label">
      <label for="hrjob-hours_amount">{ts}Actual Hours{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-hours_amount" name="hours_amount" type="text" />
      <label for="hrjob-hours_unit">{ts}per{/ts}</label>
      {literal}
      <%= RenderUtil.select({
        id: 'hrjob-hours_unit',
        name: 'hours_unit',
        entity: 'HRJobHour'
      }) %>
      {/literal}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-type">
    <div class="crm-label">
      <label for="hrjob-hours_fte">{ts}FTE{/ts}&nbsp;{help id='hrjob-fte' file='CRM/HRJob/Page/helptext'}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-fte_num" name="fte_num" type="text" /> / <input id="hrjob-fte_denom" name="fte_denom" type="text" />
    </div>
  </div>

  <%= RenderUtil.standardButtons() %>
  </form>
</script>
