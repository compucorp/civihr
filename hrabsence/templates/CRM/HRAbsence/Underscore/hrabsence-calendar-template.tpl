{literal}
<script id="hrabsence-calendar-template" type="text/template">

  <table class="hrabsence-calendar">
    <thead>
      <tr>
        <th>{/literal}{ts}Month{/ts}{literal}</th>
        <% for (var i = 1; i<= 31; i++) { %>
        <th><%= i %></th>
        <% } %>
      </tr>
    </thead>
    <tbody>
      <% _.each(active_period_ids, function(periodId) { %>
        <%
        var month = CRM.HRAbsenceApp.moment(periods[periodId].start_date).date(1);
        var end = CRM.HRAbsenceApp.moment(periods[periodId].end_date);
        for (; !month.isAfter(end); month.add(1, 'months')) {
        %>
        <tr>
          <td><%- month.format('MMM YYYY') %></td>
          <% for (var i = 1; i<= 31; i++) { %>
            <% var date = month.clone().date(i); %>
          <td><%= date.format('dd') %></td>
          <% } %>
        </tr>
        <% } %>
      <% }); %>
    </tbody>
  </table>

  <div class="hrabsence-legend">
  <h4>{/literal}{ts}Legend{/ts}{literal}</h4>
  <% var legendPos =0, legendCount = _.size(CRM.absenceApp.legend); %>
  <table class="hrabsence-legend">
    <% _.each(CRM.absenceApp.legend, function(legendItem) { %>
      <% if (legendPos%2 == 0) { %><tr> <% } %>
      <td class="<%- legendItem.cssClass %>"><%- legendItem.label %></td>
      <% if (legendPos%2 == 1 || legendPos == legendCount-1) { %></tr><% } %>
      <% legendPos++; %>
    <% }); %>
  </table>
  </div>

</script>
{/literal}