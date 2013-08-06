<script id="hrjob-leave-table-template" type="text/template">
  <h3>{ts}Leave Entitlement{/ts} {if $snippet.table_name}<a class="css_right {$snippet.css_class}" href="#" title="{ts}View Revisions{/ts}">({ts}View Revisions{/ts})</a>{/if}</h3>

  {if $snippet.table_name}
    <div class="dialog-{$snippet.css_class}">
      <div class="revision-content"></div>
    </div>
  {/if}

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
{if $snippet.table_name}{include file="CRM/common/logButton.tpl" onlyScript=true}{/if}
