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
        options: _.extend({'':''}, FieldOptions.hours_type)
      }) %>
    {/literal}
    {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='hrjob_hours_type'}
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href="{crmURL p='civicrm/admin/optionValue' q='reset=1&gid='}{$result.id}" target="_blank"><span class="batch-edit"></span></a>
    {/if}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-type">
    <div class="crm-label">
      <label for="hrjob-hours_amount">{ts}Hours{/ts}</label>
    </div>
    <div class="crm-content">
      <div>
        <input id="hrjob-hours_amount" name="hours_amount" type="text" />
      </div>
      <label for="hrjob-hours_unit">{ts}per{/ts}</label>
      {literal}
      <%= RenderUtil.select({
        id: 'hrjob-hours_unit',
        name: 'hours_unit',
        options: _.extend({'':''}, FieldOptions.hours_unit)
      }) %>
      {/literal}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-type">
    <div class="crm-label">
      <label for="hrjob-hours_fte">{ts}FTE{/ts}</label>&nbsp;{help id='access-fte' file='CRM/HRJob/Page/helptext'}
    </div>
    <div class="crm-content">
      <input id="hrjob-hours_fte" name="hours_fte" type="text" />
    </div>
  </div>

  <%= RenderUtil.standardButtons() %>
  </form>
</script>
