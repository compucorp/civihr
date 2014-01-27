{literal}
<script id="hrabsence-calendar-template" type="text/template">
  TODO: Calendar view for <span class="activity-count"></span> item(s).

  <% var legendPos =0, legendCount = _.size(CRM.absenceApp.legend); %>
  <table class="hrabsence-legend">
    <% _.each(CRM.absenceApp.legend, function(legendItem) { %>
      <% if (legendPos%2 == 0) { %><tr> <% } %>
      <td class="<%- legendItem.cssClass %>">&nbsp;</td>
      <td><%- legendItem.label %></td>
      <% if (legendPos%2 == 1 || legendPos == legendCount-1) { %></tr><% } %>
      <% legendPos++; %>
    <% }); %>
  </table>

</script>
{/literal}