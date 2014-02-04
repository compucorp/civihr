{literal}
<script id="hrabsence-calendar-template" type="text/template">

  <table class="hrabsence-calendar">
    <thead>
      <tr>
        <th>{/literal}{ts}Month{/ts}{literal}</th>
        <% for (var i = 1; i<= 31; i++) { %>
        <th><%= (i<10) ? '0'+i : i %></th>
        <% } %>
        <th>{/literal}{ts}Total{/ts}{literal}</th>
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
            <% var date = month.clone().date(i), dateFmt = date.format('YYYY-MM-DD'); %>
            <% if (date.month() != month.month() || !activity_by_date[dateFmt]) { %>
              <td data-caldate="<%- dateFmt %>" class="hrabsence-cal-item hrabsence-bg-empty"></td>
            <% } else if (activity_by_date[dateFmt].length == 1) { %>
              <% var actId = activity_by_date[dateFmt][0].get('activity_type_id'); %>
              <td data-caldate="<%- dateFmt %>" class="hrabsence-cal-item <%= CRM.absenceApp.legend[actId].cssClass %>">
                <a href="#" class="hrabsence-open" data-activity="<%= activity_by_date[dateFmt][0].get('id') %>"  title="<%- date.format('ll') %> -- <%- CRM.absenceApp.legend[actId].label %>">
                  <%- date.format('dd') %>
                </a>
              </td>
            <% } else if (activity_by_date[dateFmt].length > 1) { %>
          <td data-caldate="<%- dateFmt %>" class="hrabsence-cal-item <%= CRM.absenceApp.legend['mixed'].cssClass %>" title="<%- CRM.absenceApp.legend['mixed'].label %>">
            <%- date.format('dd') %>
          </td>
            <% } %>
          <% } // for i %>
          <td class="hrabsence-cal-total">
            <% var stats = month_stats[month.format('YYYY-MM')]; %>
            <% if (stats) { %>
              <% if (stats.creditTotal) { %><%= CRM.HRAbsenceApp.formatDuration(stats.creditTotal) %><br/><% } %>
              <% if (stats.debitTotal) { %><%= CRM.HRAbsenceApp.formatDuration(-1 * stats.debitTotal) %><% } %>
            <% } %>
          </td>
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
      <td class="hrabsence-cal-item <%- legendItem.cssClass %>"><%- legendItem.label %></td>
      <% if (legendPos%2 == 1 || legendPos == legendCount-1) { %></tr><% } %>
      <% legendPos++; %>
    <% }); %>
  </table>
  </div>

</script>
{/literal}