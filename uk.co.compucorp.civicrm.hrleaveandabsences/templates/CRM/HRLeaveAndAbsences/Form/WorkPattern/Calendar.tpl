<div class="work-pattern-calendar no-select2">
  <div class="number-of-weeks form-inline">
    <div class="form-group">
      <label for="number_of_weeks">{ts}No. of Weeks{/ts}: </label>
      <select name="number_of_weeks" id="number_of_weeks" class="crm-form-select no-select2">
        {section name=week start=1 loop="`$max_number_of_weeks+1`"}
          <option value="{$smarty.section.week.index}" {if $smarty.section.week.index eq $number_of_visible_weeks}selected{/if}>{$smarty.section.week.index}</option>
        {/section}
      </select>
    </div>
  </div>
  {section name=week loop=$max_number_of_weeks}
    <div class="work-pattern-week{if $weeks_visibility[$smarty.section.week.index] eq false} hidden-week{/if}" >
      <div class="week-number">Week {"`$smarty.section.week.index+1`"}{$form.weeks[$smarty.section.week.index].is_visible.html}</div>
      <div class="week-hours">Total hours: <span class="number-of-hours">{$weeks_hours[$smarty.section.week.index]}</span></div>
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
            {section name=day loop=7}
              <td>{$form.weeks[$smarty.section.week.index].days[$smarty.section.day.index].type.html}</td>
            {/section}
          </tr>
          <tr>
            <td>{ts}Time from{/ts}</td>
            {section name=day loop=7}
              <td>{$form.weeks[$smarty.section.week.index].days[$smarty.section.day.index].time_from.html}</td>
            {/section}
          </tr>
          <tr>
            <td>{ts}Time to{/ts}</td>
            {section name=day loop=7}
              <td>{$form.weeks[$smarty.section.week.index].days[$smarty.section.day.index].time_to.html}</td>
            {/section}
          </tr>
          <tr>
            <td>{ts}Break (h){/ts}</td>
            {section name=day loop=7}
              <td>{$form.weeks[$smarty.section.week.index].days[$smarty.section.day.index].break.html}</td>
            {/section}
          </tr>
          <tr class="hours-row">
            <td>{ts}Hours{/ts}</td>
            {section name=day loop=7}
              <td>{$form.weeks[$smarty.section.week.index].days[$smarty.section.day.index].number_of_hours.html}</td>
            {/section}
          </tr>
          <tr>
            <td>
              {ts}Leave days{/ts} {help id="id-leave-days"}
              <a
                href="/civicrm/admin/options/hrleaveandabsences_leave_days_amounts?reset=1"
                data-option-edit-path="civicrm/admin/options/hrleaveandabsences_leave_days_amounts"
                class="crm-option-edit-link" title="{ts}Edit Leave days options{/ts}">
                <i class="crm-i fa-wrench"></i>
              </a>
            </td>
            {section name=day loop=7}
              <td>{$form.weeks[$smarty.section.week.index].days[$smarty.section.day.index].leave_days.html}</td>
            {/section}
          </tr>
        </tbody>
      </table>
    </div>
  {/section}
  <script type="text/javascript">
    {literal}
      CRM.$(document).on('hrappready.formworkpattern', function (event, app) {
        var deleteUrl = {/literal}'{$delete_url}'{literal};
        var editPath = 'civicrm/admin/options/hrleaveandabsences_leave_days_amounts';

        (new app.Form.WorkPattern(deleteUrl));
        CRM.$("select.leave-days")
          .attr({
            'data-api-entity': 'WorkDay',
            'data-api-field': 'leave_days',
            'data-option-edit-path': editPath
          });
      });
    {/literal}
  </script>
</div>
