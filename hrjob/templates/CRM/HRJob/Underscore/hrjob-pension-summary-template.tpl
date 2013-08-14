<script id="hrjob-pension-summary-template" type="text/template">
{literal}
  <%
  var enrolledOptions ={
    '': '',
    '0': '{/literal}{ts}Not Enrolled{/ts}{literal}',
    '1': '{/literal}{ts}Enrolled{/ts}{literal}'
  };
  %>

  <%- enrolledOptions[is_enrolled] %>
  <% if (contrib_pct && contrib_pct != 0) { %>
  <br/><strong>{/literal}{ts}Contribution (%){/ts}{literal}</strong>: <span name="contrib_pct"/>
  <% } %>
{/literal}
</script>