<script id="hrjob-pay-template" type="text/template">
  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-pay_grade">{ts}Pay Grade{/ts}:</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_grade',
      name: 'pay_grade',
      selected: pay_grade,
      options: _.extend({'':''}, FieldOptions.pay_grade)
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-pay_amount">{ts}Pay Rate{/ts}:</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-pay_amount" name="pay_amount" type="text" value="<%- pay_amount %>" />
      <label for="hrjob-pay_unit">{ts}per{/ts}</label>
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_unit',
      name: 'pay_unit',
      selected: pay_unit,
      options: _.extend({'':''}, FieldOptions.pay_unit)
      }) %>
    {/literal}
    </div>
  </div>
</script>
