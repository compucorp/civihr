<script id="hrjob-pay-template" type="text/template">
<form>
  <h3>
    {ts}Pay{/ts}
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_pay" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

  <div class="crm-summary-row">
    <div class="crm-content-payGrade">
      <input name="is_paid" type="radio" value="1" class="payGrade"/>{ts}Paid{/ts}
      <input name="is_paid" type="radio" value="0" class="payGrade"/>{ts}Unpaid{/ts}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-pay_grade">
    <div class="crm-label">
      <label for="hrjob-pay_scale">{ts}Pay Scale{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_scale',
      name: 'pay_scale',
      entity: 'HRJobPay'
      }) %>
    {/literal}
    {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_pay_scale'}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-pay_grade">
    <div class="crm-label">
      <label for="hrjob-pay_amount">{ts}Pay Currency{/ts}</label>
    </div>
    <div class="crm-content">
    {literal}
      <%= RenderUtil.select({
      id: 'hrjob-pay_currency',
      name: 'pay_currency',
      entity: 'HRJobPay'
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
      entity: 'HRJobPay'
      }) %>
    {/literal}
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-pay_grade">
    <div class="crm-label">
      <label for="hrjob-pay_annualized_est">{ts}Annual Pay Estimate{/ts}</label>
    </div>
    <div class="crm-content">
      <div>
        <input id="hrjob-pay_annualized_est" name="pay_annualized_est" type="text" />
        <input id="hrjob-pay_is_auto_est" name="pay_is_auto_est" type="hidden" />
      </div>
      <div>
        <span class="pay_annualized_est_expl"></span>
        {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
        <span class="batch-edit pay_annualized_est_edit"></span>
        {/if}
      </div>
    </div>
  </div>
  <%= RenderUtil.standardButtons() %>
</form>
</script>
