<div class="work-pattern-calendar no-select2">
  <div class="number-of-weeks form-inline">
    <div class="form-group">
      <label for="number_of_weeks">{ts}No. of Weeks{/ts}: </label>
      <select name="number_of_weeks" id="number_of_weeks">
        {section name=i start=1 loop="`$max_number_of_weeks+1`" step=1}
          <option value="{$smarty.section.i.index}" {if $smarty.section.i.index eq $number_of_visible_weeks}selected{/if}>{$smarty.section.i.index}</option>
        {/section}
      </select>
    </div>
  </div>
  {section name=i start=0 loop=$max_number_of_weeks step=1}
    <div class="work-pattern-week{if $weeks_visibility[$smarty.section.i.index] eq false} hidden-week{/if}" >
      <div class="week-number">Week {"`$smarty.section.i.index+1`"}{$form.weeks[$smarty.section.i.index].is_visible.html}</div>
      <div class="week-hours">Total hours: <span class="number-of-hours">{$weeks_hours[$smarty.section.i.index]}</span></div>
      <table class="week-days table table-condensed table-responsive">
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
            <td>{$form.weeks[$smarty.section.i.index].days.0.number_of_hours.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.1.number_of_hours.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.2.number_of_hours.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.3.number_of_hours.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.4.number_of_hours.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.5.number_of_hours.html}</td>
            <td>{$form.weeks[$smarty.section.i.index].days.6.number_of_hours.html}</td>
          </tr>
          <tr>
            <td>{ts}Leave days{/ts} {help id="id-leave-days"}</td>
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
  {literal}
    <script type="text/javascript">
      CRM.$(function($) {
        var form = new CRM.HRLeaveAndAbsencesApp.Form.WorkPattern({/literal}'{$delete_url}'{literal});
      });
    </script>
  {/literal}
</div>
