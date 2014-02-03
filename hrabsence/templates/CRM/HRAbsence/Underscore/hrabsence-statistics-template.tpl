{literal}
<script id="hrabsence-statistics-template" type="text/template">
  <table>
    <thead>
      <tr class="hrabsence-statistics-header" >
        <th>{/literal}{ts}Period{/ts}{literal}</th>
        <th>{/literal}{ts}Leave Type{/ts}{literal}</th>
        <th>{/literal}{ts}Entitlement{/ts}{literal}</th>
        <th>{/literal}{ts}Requested{/ts}{literal}</th>
        <th>{/literal}{ts}Approved{/ts}{literal}</th>
        <th>{/literal}{ts}Balance{/ts}{literal}</th>
      </tr>
    </thead>
    <tbody>
    <% _.each(stats, function(absence){ %>
        <tr class="hrabsence-list-item" data-statistics-id="<%- absence.period_id %>-<%- absence.activity_type_id %>">
          <td class="hrabsence-statistics-period-desc"><%- FieldOptions.period_id[absence.period_id] %><% console.log(absence) %></td>
          <td class="hrabsence-statistics-leave-type"><%- FieldOptions.activity_type_id[absence.activity_type_id] %></td>
          <% if(entitlements[absencetype[absence.activity_type_id]] && entitlements[absencetype[absence.activity_type_id]][absence.period_id]) {%>
            <td class="hrabsence-statistics-entitlement"><%- entitlements[absencetype[absence.activity_type_id]][absence.period_id] %></td>
          <% } else { %> 
            <td class="hrabsence-statistics-entitlement"></td>
          <% } %>
          <td class="hrabsence-statistics-request"><%- (absence.requested)/(8*60) %></td>
          <td class="hrabsence-statistics-approve"><%- absence.approved/(8*60) %></td>
          <% if(entitlements[absencetype[absence.activity_type_id]] && entitlements[absencetype[absence.activity_type_id]][absence.period_id]) {%>
            <td class="hrabsence-statistics-bal"><%- entitlements[absencetype[absence.activity_type_id]][absence.period_id] - (absence.requested/(8*60) + absence.approved/(8*60)) %></td>
          <% } else { %>
            <td class="hrabsence-statistics-bal"><%- 0-(absence.requested/(8*60) + absence.approved/(8*60)) %></td>
          <% } %>
        </tr>
    <% }); %>
    </tbody>
  </table>
</script>
{/literal}