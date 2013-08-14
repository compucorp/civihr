<script id="hrjob-pay-summary-template" type="text/template">
  <div class="crm-summary-row">
    <div class="crm-label">{ts}Pay{/ts}</div>
    <div class="crm-content">
      <%- FieldOptions.pay_grade[pay_grade] %>
      {literal}<% if (pay_grade && pay_grade != 'unpaid') { %>{/literal}
        (<span name="pay_amount"/> {ts}per{/ts} <%- FieldOptions.pay_unit[pay_unit] %>)
      {literal}<% } %>{/literal}
    </div>
  </div>
</script>