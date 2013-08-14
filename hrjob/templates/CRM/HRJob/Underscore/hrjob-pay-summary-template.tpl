<script id="hrjob-pay-summary-template" type="text/template">
  <h3>{ts}Pay{/ts}</h3>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Pay Grade{/ts}</div>
    <div class="crm-content"><%- FieldOptions.pay_grade[pay_grade] %></div>
  </div>

  {literal}<% if (pay_grade && pay_grade != 'unpaid') { %>{/literal}
  <div class="crm-summary-row hrjob-needs-pay_grade">
    <div class="crm-label">{ts}Pay Rate{/ts}</div>
    <div class="crm-content">
      <span name="pay_amount" /> {ts}per{/ts} <%- FieldOptions.pay_unit[pay_unit] %>
    </div>
  </div>
  {literal}<% } %>{/literal}

</script>