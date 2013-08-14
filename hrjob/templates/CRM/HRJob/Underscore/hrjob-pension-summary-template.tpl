<script id="hrjob-pension-summary-template" type="text/template">
{literal}
<%
var enrolledOptions ={
  '': '',
  '0': '{/literal}{ts}Not Enrolled{/ts}{literal}',
  '1': '{/literal}{ts}Enrolled{/ts}{literal}'
};
%>
{/literal}

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Pension{/ts}</div>
    <div class="crm-content">
      <%- enrolledOptions[is_enrolled] %>
      {literal}<% if (contrib_pct && contrib_pct != 0) { %>{/literal}
      <br /><strong>{ts}Contribution (%){/ts}</strong>: <span name="contrib_pct"/>
      {literal}<% } %>{/literal}
    </div>
  </div>

</script>