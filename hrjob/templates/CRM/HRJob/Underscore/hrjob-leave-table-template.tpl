<script id="hrjob-leave-table-template" type="text/template">
  <h3>{ts}Leave Entitlement{/ts} {if $snippet.table_name}<a class="css_right {$snippet.css_class}" href="#" title="{ts}View Revisions{/ts}">({ts}View Revisions{/ts})</a>{/if}</h3>

  <table>
    <thead>
    <tr>
      <th>{ts}Leave Type{/ts}</th>
      <th>{ts}Days per Year{/ts}</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
  </table>

  <%= RenderUtil.standardButtons() %>
</script>
