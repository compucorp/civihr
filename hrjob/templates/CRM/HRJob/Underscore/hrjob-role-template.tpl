<script id="hrjob-role-template" type="text/template">
  <td>
    <span class="hrjob-role-toggle">
      <span class="on-closed">[+]</span>
      <span class="on-open">[-]</span>
    </span>
  </td>
  <td>
    <strong class="hrjob-role-toggle bindto-out" data-bindto="title"></strong>
    <div class="toggle-role-form">
    {* FIXME: Extract to a new view/template *}
      <form>
        <table>
          <tbody>
          <tr>
            <td>{ts}Title{/ts}</td>
            <td colspan="3"><input class="hrjob-role-title bindto-in" name="title" value="<%- title %>"/></td>
          </tr>
          <tr>
            <td>{ts}Description{/ts}</td>
            <td colspan="3">
              <textarea class="hrjob-role-description" name="description"><%- description %></textarea>
            </td>
          </tr>
          <tr>
            <td>{ts}Department{/ts}</td>
            <td><input class="hrjob-role-department" name="department" value="<%- department %>"/></td>
            <td>{ts}Hours/Week{/ts}</td>
            <td><input class="hrjob-role-hours bindto-in" name="hours" value="<%- hours %>"/></td>
          </tr>
          </tbody>
        </table>
      </form>
    </div>
  </td>
  <td>
    <strong class="bindto-out" data-bindto="hours"></strong>
  </td>
  <td></td>
</script>
