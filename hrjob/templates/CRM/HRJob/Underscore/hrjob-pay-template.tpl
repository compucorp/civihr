<script id="hrjob-pay-template" type="text/template">
  <h3>{ts}Pay{/ts} {if $snippet.table_name}<a class="css_right {$snippet.css_class}" href="#" title="{ts}View Revisions{/ts}">({ts}View Revisions{/ts})</a>{/if}</h3>

  {if $snippet.table_name}
    <div class="dialog-{$snippet.css_class}">
      <div class="revision-content"></div>
    </div>
  {/if}

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-pay_grade">{ts}Pay Grade{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_grade',
      name: 'pay_grade',
      options: _.extend({'':''}, FieldOptions.pay_grade)
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-pay_amount">{ts}Pay Rate{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-pay_amount" name="pay_amount" type="text" />
      <label for="hrjob-pay_unit">{ts}per{/ts}</label>
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_unit',
      name: 'pay_unit',
      options: _.extend({'':''}, FieldOptions.pay_unit)
      }) %>
    {/literal}
    </div>
  </div>

  <%= RenderUtil.standardButtons() %>
</script>
{if $snippet.table_name}{include file="CRM/common/logButton.tpl" onlyScript=true}{/if}
