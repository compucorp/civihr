<div class="form-layout work-pattern-calendar">
  <div class="number-of-weeks">
    <label for="number_of_weeks" class="label">{ts}No. of Weeks{/ts}: </label>
    <select name="number_of_weeks" id="number_of_weeks" class="crm-form-select">
      {section name=i start=1 loop="`$max_number_of_weeks+1`" step=1}
        <option value="{$smarty.section.i.index}" {if $smarty.section.i.index eq $number_of_visible_weeks}selected{/if}>{$smarty.section.i.index}</option>
      {/section}
    </select>
  </div>
  {section name=i start=0 loop=$max_number_of_weeks step=1}
    <div class="work-pattern-week{if $weeks_visibility[$smarty.section.i.index] eq false} hidden-week{/if}" >
      <div class="week-number">Week {"`$smarty.section.i.index+1`"}{$form.weeks[$smarty.section.i.index].is_visible.html}</div>
      <div class="week-hours">Total hours: <span class="number-of-hours">{$weeks_hours[$smarty.section.i.index]}</span></div>
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
        const NON_WORKING_DAY = 1;
        const WORKING_DAY = 2;
        const WEEKEND = 3;

        function getWeekVisibleFlagFieldName(weekIndex) {
          return 'weeks['+weekIndex+'][is_visible]';
        }

        function setWeekVisibleFlag(weekIndex, flagValue) {
          var is_visible_flag_field_name = getWeekVisibleFlagFieldName(weekIndex);
          document.getElementsByName(is_visible_flag_field_name)[0].value = flagValue ? 1 : 0;
        }

        function resetWeekDays(weekIndex) {
          var weekElement = $('.work-pattern-week').eq(weekIndex);
          var weekendCells = weekElement.find('tbody tr td:not(:nth-child(-n+6))');

          weekElement.find('input').val('');
          weekElement.find('.work-day-type').val(1); //se every day as a non working day
          weekElement.find('.leave-days').val(0);
          weekendCells.find('input,.leave-days').attr('disabled', 'disabled');
        }

        function setInitialWeekDaysValues(weekIndex) {
          var weekElement = $('.work-pattern-week').eq(weekIndex);
          var workDayTypeSelects = weekElement.find('.work-day-type');
          var workingDayCells = weekElement.find('tbody tr td:nth-child(-n+6)');

          workDayTypeSelects.slice(0, 5).val(WORKING_DAY); //set first five days as working day
          workDayTypeSelects.slice(5).val(WEEKEND); // set the last two days as weekend
          workingDayCells.find('input, .leave-days').removeAttr('disabled');
        }

        function weekIsVisible(weekIndex) {
          var is_visible_flag_field_name = getWeekVisibleFlagFieldName(weekIndex);
          return document.getElementsByName(is_visible_flag_field_name)[0].value === '1';
        }

        function showWeek(weekIndex) {
          if(!weekIsVisible(weekIndex)) {
            $('.work-pattern-week').eq(weekIndex).removeClass('hidden-week');
            setInitialWeekDaysValues(weekIndex);
            setWeekVisibleFlag(weekIndex, true);
          }
        }

        function hideWeek(weekIndex) {
          if(weekIsVisible(weekIndex)) {
            $('.work-pattern-week').eq(weekIndex).addClass('hidden-week');
            resetWeekDays(weekIndex);
            setWeekVisibleFlag(weekIndex, false);
          }
        }

        function disableNonWorkingDayFields() {
          $('.work-pattern-week').each(function(weekIndex, weekElement) {
            weekElement = $(weekElement);
            var workDayTypeSelects = weekElement.find('.work-day-type');
            workDayTypeSelects.each(function(workDayIndex, element) {
              if(element.value == NON_WORKING_DAY || element.value == WEEKEND) {
                // Since we are looking for cells and not the actual fields,
                // We add 2 to the workDayIndex so we can "ignore" the first
                // table column, which doesn't include any field
                var workDayCells = weekElement.find('table tbody tr td:nth-child(' + (workDayIndex + 2) + ')');
                workDayCells.find('input, .leave-days').attr('disabled', 'disabled');
              }
            });
          });
        }

        function getWorkDayIndexFromFieldId(id) {
          var index = id.replace(/weeks_\d+_days_(\d)_.+/, '$1');
          return parseInt(index);
        }

        function getWorkWeekIndexFromFieldId(id) {
          var index = id.replace(/weeks_(\d+)_days_\d_.+/, '$1');
          return parseInt(index);
        }

        function calculateNumberOfHoursForADay(weekIndex, dayIndex) {
          var fieldsPrefix = 'weeks_' + weekIndex + '_days_' + dayIndex + '_';
          var timeFrom = document.getElementById(fieldsPrefix + 'time_from').value;
          var timeTo = document.getElementById(fieldsPrefix + 'time_to').value;
          var breakHours = document.getElementById(fieldsPrefix + 'break').value;
          var hoursField = document.getElementById(fieldsPrefix + 'number_of_hours');
          var secondsInPeriod = 0;
          var secondsInBreak = 0;
          var numberOfHours = 0;

          timeFrom = Date.parse('2016-01-01 ' + timeFrom);
          timeTo = Date.parse('2016-01-01 ' + timeTo);
          breakHours = parseFloat(breakHours);

          if (!isNaN(timeFrom) && !isNaN(timeTo) && !isNaN(breakHours)) {
            secondsInPeriod = (timeTo - timeFrom) / 1000;
            secondsInBreak = breakHours * 3600;
            numberOfHours = (secondsInPeriod - secondsInBreak) / 3600;
            numberOfHours = numberOfHours < 0 ? 0 : numberOfHours.toFixed(2);
            hoursField.value = numberOfHours;
          }
        }

        function calculateNumberOfHoursForAWeek(weekIndex) {
          var weekSelector ='.work-pattern-week:nth('+weekIndex+')';
          var numberOfHoursElement = $(weekSelector + ' .number-of-hours');
          var hoursFields = $(weekSelector + ' .work-day-hours');
          var totalNumberOfHours = 0;
          hoursFields.each(function(i, element) {
            totalNumberOfHours += element.value ? parseFloat(element.value) : 0;
          });

          numberOfHoursElement.text(totalNumberOfHours);
        }

        function updateHoursForFieldChange(field) {
          var dayIndex = getWorkDayIndexFromFieldId(field.id);
          var weekIndex = getWorkWeekIndexFromFieldId(field.id);
          calculateNumberOfHoursForADay(weekIndex, dayIndex);
          calculateNumberOfHoursForAWeek(weekIndex);
        }

        function setTimeMasks() {
          var inputMask = Inputmask({'mask': '99:99', 'oncomplete': function() {
            updateHoursForFieldChange(this);
          }});
          inputMask.mask(document.querySelectorAll('.work-day-time'));
        }

        function setBreakMasks() {
          var inputMask = Inputmask({'alias': 'decimal', 'rightAlign': false, 'oncomplete': function() {
            updateHoursForFieldChange(this);
          }});
          inputMask.mask(document.querySelectorAll('.work-day-break'));
        }

        function initDeleteButton() {
          $('.crm-button-type-delete').on('click', function(e) {
            e.preventDefault();
            CRM.confirm({
                title: ts('Delete Work Pattern'),
                message: ts('Are you sure you want to delete this Work Pattern?'),
                options: {
                  yes: ts('Yes'),
                  no: ts('No')
                }
              })
              .on('crmConfirm:yes', deleteCallback);
          });
        }

        function disableFormChangesNotification() {
          var form = $('form.CRM_HRLeaveAndAbsences_Form_WorkPattern');
          form.attr('data-warn-changes', 'false');
        }

        function deleteCallback() {
          //Since we're already displayed the delete confirmation,
          //there's no reason to show another notification about
          //changes made to the pattern
          disableFormChangesNotification();

          {/literal}
          window.location = "{$delete_url}";
          {literal}
        }

        $('#number_of_weeks').on('change', function() {
          for(i = 0; i < 5; i++) {
            if(i < this.value) {
              showWeek(i);
            } else {
              hideWeek(i);
            }
          }
        });

        $('.work-day-type').on('change', function() {
          var workDayIndex = getWorkDayIndexFromFieldId(this.id);
          var weekElement = $(this).parents('.work-pattern-week');
          var workDayCells = weekElement.find('table tbody tr td:nth-child(' + (workDayIndex + 2) + ')');

          if(this.value == NON_WORKING_DAY || this.value == WEEKEND) {
            workDayCells.find('input, .leave-days').attr('disabled', 'disabled');
            workDayCells.find('input').val('');
            workDayCells.find('.leave-days').val(0);
          } else {
            workDayCells.find('input, .leave-days').removeAttr('disabled');
          }
        });

        disableNonWorkingDayFields();
        setTimeMasks();
        setBreakMasks();
        initDeleteButton();
      });
    </script>
  {/literal}
</div>
