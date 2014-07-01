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
        for (; !month.isAfter(end); end.add(-1, 'months')) {
        %>
        <tr>
          <td><%- end.format('MMM YYYY') %></td>
          <% for (var i = 1; i<= 31; i++) { %>
            <% var date = end.clone().date(i), dateFmt = date.format('YYYY-MM-DD'); %>
            <% if (date.month() != end.month() || !activity_by_date[dateFmt]) { %>
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
            <% var stats = month_stats[end.format('YYYY-MM')]; %>
            <% if (stats) { %>
              <% if (stats.creditTotal) { %><div class="credit"><%= CRM.HRAbsenceApp.formatDuration(stats.creditTotal) %></div><% } %>
              <% if (stats.debitTotal) { %><div class="debit"><%= CRM.HRAbsenceApp.formatDuration(-1 * stats.debitTotal) %></div><% } %>
            <% } %>
          </td>
        </tr>
        <% } %>
      <% }); %>
    </tbody>
  </table>

  <div class="hrabsence-legend">
  <% var legendPos =0, legendCount = _.size(CRM.absenceApp.legend); %>
  <table class="hrabsence-legend">
    <tr>
      <th colspan="<%- _.size(CRM.absenceApp.legend) %>"><h4>{/literal}{ts}Legend{/ts}{literal}</h4></th>
    </tr>
    <tr>
    <% var cellWidth = Math.floor(99 / _.size(CRM.absenceApp.legend)) ; %>
    <% _.each(CRM.absenceApp.legend, function(legendItem) { %>
      <td style="width: <%- cellWidth %>%" class="hrabsence-cal-item <%- legendItem.cssClass %>">
        <%- legendItem.label %>
      </td>
    <% }); %>
    </tr>
  </table>
  </div>

</script>
{/literal}
