<script id="hrjob-role-table-template" type="text/template">
  <h3>{ts}Roles{/ts} {if $snippet.table_name}<a class="css_right {$snippet.css_class}" href="#" title="{ts}View Revisions{/ts}">({ts}View Revisions{/ts})</a>{/if}</h3>

  <table class="hrjob-role-table">
    <thead>
    <tr>
      <th style="width: 2em;"></th>
      <th>{ts}Role Description{/ts}</th>
      <th style="width: 5em;">{ts}Hours{/ts}</th>
      <th style="width: 5em;">{ts}Action{/ts}</th>
    </tr>
    </thead>
    <tbody>
      <tr class="hrjob-role-final">
        <td></td>
        <td><a href="#" class="hrjob-role-add">{ts}Add role{/ts}</a></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <%= RenderUtil.standardButtons() %>
</script>
{if $snippet.table_name}{include file="CRM/common/logButton.tpl" onlyScript=true onajax=true}{/if}
