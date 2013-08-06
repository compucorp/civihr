<script id="hrjob-hour-template" type="text/template">
  <h3>{ts}Hours{/ts} {if $snippet.table_name}<a class="css_right {$snippet.css_class}" href="#" title="{ts}View Revisions{/ts}">({ts}View Revisions{/ts})</a>{/if}</h3>

  {if $snippet.table_name}
    <div class="dialog-{$snippet.css_class}">
      <div class="revision-content"></div>
    </div>
  {/if}

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
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-hours_amount">{ts}Hours{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-hours_amount" name="hours_amount" type="text" />
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

  <div class="crm-summary-row">
    <div class="crm-label">
      <label>{ts}FTE{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-hours_fte" name="hours_fte" type="text" />
    </div>
  </div>

  <%= RenderUtil.standardButtons() %>
</script>
{if $snippet.table_name}{include file="CRM/common/logButton.tpl" onlyScript=true}{/if}
