<script id="hrjob-pay-summary-template" type="text/template">
  <%- FieldOptions.is_paid[is_paid] %>
  {literal}<% if (is_paid && is_paid != 0) { %>{/literal}
  ({literal}<% if (pay_currency) {  %>{/literal} <%- pay_currency %> {literal}<% } %>{/literal} <span name="pay_amount" data-currency-field="pay_currency" /> {ts}per{/ts} <%- FieldOptions.pay_unit[pay_unit] %>)

  {literal}<% } %>{/literal}
</script>