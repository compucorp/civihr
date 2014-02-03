{literal}
<script id="hrabsence-statistics-template" type="text/template">
  <table>
    <thead>
        <th>{/literal}{ts}Period{/ts}{literal}</th>
        <th>{/literal}{ts}Leave Type{/ts}{literal}</th>
        <th>{/literal}{ts}Entitlement{/ts}{literal}</th>
        <th>{/literal}{ts}Requested{/ts}{literal}</th>
        <th>{/literal}{ts}Approved{/ts}{literal}</th>
        <th>{/literal}{ts}Balance{/ts}{literal}</th>
    </thead>
    <tbody>
    <% _.each(stats, function(absence){ %>
        <tr class="hrabsence-list-period-header">
          <td><%- FieldOptions.period_id[absence.period_id] %></td>
          <td><%- FieldOptions.activity_type_id[absence.activity_type_id] %></td>
          <td>
            <% if(entitlements[absencetype[absence.activity_type_id]] && entitlements[absencetype[absence.activity_type_id]][absence.period_id]) {%>
              <%- entitlements[absencetype[absence.activity_type_id]][absence.period_id] %>
            <% } %>
          </td>
          <td><%- (absence.requested)/(8*60) %></td>
          <td><%- absence.approved/(8*60) %></td>
          <td>
            <% if(entitlements[absencetype[absence.activity_type_id]] && entitlements[absencetype[absence.activity_type_id]][absence.period_id]) {%>
              <%- entitlements[absencetype[absence.activity_type_id]][absence.period_id] - (absence.requested/(8*60) + absence.approved/(8*60)) %>
            <% }
            else { %>
              <%- 0-(absence.requested/(8*60) + absence.approved/(8*60)) %>
            <% } %>
          </td>
        </tr>
    <% }); %>
    </tbody>
  </table>
</script>
{/literal}