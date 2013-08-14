<script id="hrjob-hour-summary-template" type="text/template">

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Hours{/ts}</div>
    <div class="crm-content">
      <%- FieldOptions.hours_type[hours_type] %>
      {literal}<% if (hours_amount) { %>{/literal}
        (<span name="hours_amount"/> {ts}per{/ts} <%- FieldOptions.hours_unit[hours_unit] %>)
      {literal}<% } %>{/literal}
      {literal}<% if (hours_fte) { %>{/literal}
        (<span name="hours_fte"/> FTE)
      {literal}<% } %>{/literal}
    </div>
  </div>

</script>