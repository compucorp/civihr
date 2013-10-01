<script id="hrjob-pay-template" type="text/template">
<form>
  <h3>
    {ts}Pay{/ts}
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_pay" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

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

  <div class="crm-summary-row hrjob-needs-pay_grade">
    <div class="crm-label">
      <label for="hrjob-pay_amount">{ts}Currency{/ts}</label>{}
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_currency',
      name: 'pay_currency',
      options: _.extend({'':''}, FieldOptions.pay_currency)
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-pay_grade">
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
</form>
</script>
