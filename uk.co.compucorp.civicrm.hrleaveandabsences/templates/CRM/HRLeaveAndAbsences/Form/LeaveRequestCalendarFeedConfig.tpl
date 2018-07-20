<div id="bootstrap-theme">
  <div class="panel panel-default crm-form-block crm-absence_type-form-block crm-leave-and-absences-form-block">
    <div class="panel-heading">
      <h1 class="panel-title">
        {if $action eq 1}{ts}Add a New Calendar Feed{/ts}
        {elseif $action eq 2}{ts}Edit Calendar Feed{/ts}{/if}
      </h1>
    </div>

    <div class="panel-body">
      {if $action neq 8}
        <div class="form-group row">
          <div class="col-sm-3">{$form.title.label}</div>
          <div class="col-sm-9 form-group">{$form.title.html}</div>
        </div>
        <div class="form-group row composed_of_department">
          <div class="col-sm-3">{$form.composed_of_department.label}</div>
          <div class="col-sm-9">{$form.composed_of_department.html}</div>
        </div>
        <div class="form-group row composed_of_location">
          <div class="col-sm-3">{$form.composed_of_location.label}</div>
          <div class="col-sm-9">{$form.composed_of_location.html}</div>
        </div>
        <div class="form-group row">
          <div class="col-sm-3"></div>
          <div class="col-sm-9 pull-left text-muted form-group">
            <span>*</span>
            <span class="composed_of_help_text"></span>
          </div>
        </div>
        <div class="form-group row visible_to_department">
          <div class="col-sm-3">{$form.visible_to_department.label}</div>
          <div class="col-sm-9">{$form.visible_to_department.html}</div>
        </div>
        <div class="form-group row visible_to_location">
          <div class="col-sm-3">{$form.visible_to_location.label}</div>
          <div class="col-sm-9">{$form.visible_to_location.html}</div>
        </div>
        <div class="form-group row">
          <div class="col-sm-3"></div>
          <div class="col-sm-9 pull-left text-muted form-group">
            <span>*</span>
            <span class="visible_to_help_text"></span>
          </div>
        </div>
        <div class="form-group row composed_of_leave_type">
          <div class="col-sm-3">{$form.composed_of_leave_type.label}</div>
          <div class="col-sm-9">{$form.composed_of_leave_type.html}</div>
        </div>
        <div class="form-group row">
          <div class="col-sm-3">{$form.timezone.label}</div>
          <div class="col-sm-9">{$form.timezone.html}</div>
        </div>
        <div class="form-group row">
          <div class="col-sm-3">{$form.is_active.label}</div>
          <div class="col-sm-9">{$form.is_active.html}</div>
        </div>
      </div>
      <script type="text/javascript">
        {literal}
          CRM.$(function ($) {
            var REDIRECT_AFTER_DELETE_URL = '{/literal}{$deleteUrl}{literal}';

            $(document).ready(function () {
              initDeleteButton();
              setComposedOfHelpText();
              setVisibleToHelpText();
              initFilterFieldsHelpText();
            });

            /**
             * Initiates "Delete" button by adding a click event
             * with a confirmation dialog
             */
            function initDeleteButton () {
              $('.crm-button-type-delete').on('click', function (e) {
                e.preventDefault();
                CRM
                  .confirm({
                    title: ts('Delete Calendar Feed'),
                    message: ts('Are you sure you want to delete this Calendar Feed?'),
                    options: {
                      yes: ts('Yes'),
                      no: ts('No')
                    }
                  })
                  .on('crmConfirm:yes', function () {
                    window.location = REDIRECT_AFTER_DELETE_URL;
                  });
              });
            }

            /**
             * Initiates filters help texts
             * by attaching according event handlers to the filter fields
             */
            function initFilterFieldsHelpText () {
              $('.composed_of_department select, .composed_of_location select')
                .on('change', setComposedOfHelpText);

              $('.visible_to_department select, .visible_to_location select')
                .on('change', setVisibleToHelpText);
            }

            /**
             * Sets help text for "Staff to include" group filter
             */
            function setComposedOfHelpText () {
              var helpMessage = {
                'default': 'All staff will be included',
                'department_only': 'Only staff from the selected departments will be included',
                'location_only': 'Only staff from the selected locations will be included',
                'department_and_location': 'Only staff from the selected departments plus the selected locations will be included'
              };

              setHelpText('composed_of', helpMessage);
            }

            /**
             * Sets help text for a group filter depending on its state
             *
             * @param {String} filterName
             * @param {String} helpMessage
             */
            function setHelpText (filterName, helpMessage) {
              var departmentValue = $('.' + filterName + '_department select option:selected').val();
              var locationValue = $('.' + filterName + '_location select option:selected').val();
              var helpText = helpMessage.default;

              if (!departmentValue && locationValue) {
                helpText = helpMessage.location_only;
              }

              if (departmentValue && !locationValue) {
                helpText = helpMessage.department_only;
              }

              if (departmentValue && locationValue) {
                helpText = helpMessage.department_and_location;
              }

              $('.' + filterName + '_help_text').text(helpText);
            }

            /**
             * Sets help text for "Share link with" group filter
             */
            function setVisibleToHelpText () {
              var helpMessage = {
                'default': 'The feed will be shown to all staff',
                'department_only': 'The feed will be shown to the staff from the selected departments only',
                'location_only': 'The feed will be shown to the staff from the selected locations only',
                'department_and_location': 'The feed will be shown to the staff from the selected departments plus the selected locations'
              };

              setHelpText('visible_to', helpMessage);
            }
          });
        {/literal}
      </script>
    {/if}
    <div class="panel-footer clearfix">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
