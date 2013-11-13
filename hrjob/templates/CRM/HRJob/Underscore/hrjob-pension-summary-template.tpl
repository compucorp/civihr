<script id="hrjob-pension-summary-template" type="text/template">
{literal}
  <%
  var enrolledOptions ={
    '': '',
    '0': '{/literal}{ts}Not Enrolled{/ts}{literal}',
    '1': '{/literal}{ts}Enrolled{/ts}{literal}',
    '2': '{/literal}{ts}Opted out{/ts}{literal}'
  };
  %>

  <%- enrolledOptions[is_enrolled] %>
  <% if (pension_type) { %>
    <br/><strong>{/literal}{ts}Pension Type{/ts}{literal}</strong>:<%- FieldOptions.pension_type[pension_type] %>
  <% } %>
  <% if (er_contrib_pct && er_contrib_pct != 0) { %>
  <br/><strong>{/literal}{ts}Employer Contribution (%){/ts}{literal}</strong>: <span name="er_contrib_pct"/>
  <% } %>
  <% if (ee_contrib_pct && ee_contrib_pct != 0) { %>
  <br/><strong>{/literal}{ts}Employee Contribution (%){/ts}{literal}</strong>: <span name="ee_contrib_pct"/>
  <% } %>
  <% if (ee_contrib_abs) { %>
  <br/><strong>{/literal}{ts}Employee Contribution (absolute amount){/ts}{literal}</strong>: <span name="ee_contrib_abs"/>
  <% } %> 
  
{/literal}
</script>