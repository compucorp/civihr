<script id="hrjob-hour-summary-template" type="text/template">
  <%- FieldOptions.hours_type[hours_type] %>
  {literal}<% if (hours_amount) { %>{/literal}
    (<span name="hours_amount"/> {ts}per{/ts} <%- FieldOptions.hours_unit[hours_unit] %>)
  {literal}<% } %>{/literal}
  {literal}<% if (hours_fte) { %>{/literal}
    (<span name="fte_num"/>/<span name="fte_denom"/> FTE)
  {literal}<% } %>{/literal}
</script>
