<div class="form-layout work-pattern-calendar">
  <div class="number-of-weeks">
    <label for="number_of_weeks" class="label">{ts}No. of Weeks{/ts}: </label>
    <select name="number_of_weeks" id="number_of_weeks" class="crm-form-select">
      {section name=i start=1 loop="`$max_number_of_weeks+1`" step=1}
        <option value="{$smarty.section.i.index}">{$smarty.section.i.index}</option>
      {/section}
    </select>
  </div>
  {section name=i start=0 loop=$max_number_of_weeks step=1}
    <div class="work-pattern-week">
      <div class="week-number">Week {"`$smarty.section.i.index+1`"}</div>
      <div class="week-hours">Total hours: <span class="number-of-hours">37.5</span></div>
      <table class="week-days">
        <thead>
          <tr>
            <td></td>
            <td>{ts}Monday{/ts}</td>
            <td>{ts}Tuesday{/ts}</td>
            <td>{ts}Wednesday{/ts}</td>
            <td>{ts}Thursday{/ts}</td>
            <td>{ts}Friday{/ts}</td>
            <td>{ts}Saturday{/ts}</td>
            <td>{ts}Sunday{/ts}</td>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{ts}Working Day?{/ts}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.0.type.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.1.type.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.2.type.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.3.type.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.4.type.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.5.type.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.6.type.html}</td>
          </tr>
          <tr>
            <td>{ts}Time from{/ts}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.0.time_from.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.1.time_from.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.2.time_from.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.3.time_from.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.4.time_from.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.5.time_from.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.6.time_from.html}</td>
          </tr>
          <tr>
            <td>{ts}Time to{/ts}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.0.time_to.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.1.time_to.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.2.time_to.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.3.time_to.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.4.time_to.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.5.time_to.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.6.time_to.html}</td>
          </tr>
          <tr>
            <td>{ts}Break (h){/ts}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.0.break.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.1.break.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.2.break.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.3.break.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.4.break.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.5.break.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.6.break.html}</td>
          </tr>
          <tr class="hours-row">
            <td>{ts}Hours{/ts}</td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.0.number_of_hours.value}"></td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.1.number_of_hours.value}"></td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.2.number_of_hours.value}"></td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.3.number_of_hours.value}"></td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.4.number_of_hours.value}"></td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.5.number_of_hours.value}"></td>
            <td><input type="text" class="crm-form-text" readonly value="{$form.weeks[$smarty.section.i.index].days.6.number_of_hours.value}"></td>
          </tr>
          <tr>
            <td>{ts}Leave days{/ts}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.0.leave_days.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.1.leave_days.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.2.leave_days.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.3.leave_days.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.4.leave_days.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.5.leave_days.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.6.leave_days.html}</td>
          </tr>
        </tbody>
      </table>
    </div>
  {/section}
</div>
