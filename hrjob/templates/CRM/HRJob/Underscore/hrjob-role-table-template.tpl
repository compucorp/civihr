<script id="hrjob-role-table-template" type="text/template">
  <h3> 
    {ts}Roles{/ts}
    {* **
      Because roles can be added/deleted at a whim, the "isNew" heuristic doesn't really
      tell us if there is history for the roles. (If user deletes the last role, then
      isNew==true, but there are past revisions for the deleted items that we want to see.
      Easier to just show link unconditionally on this section.
     ** *}
    {* literal}<% if (!isNew) { %> {/literal *}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_role" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {* literal}<% } %>{/literal *}
  </h3>
  <form>

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
  </form>

</script>
