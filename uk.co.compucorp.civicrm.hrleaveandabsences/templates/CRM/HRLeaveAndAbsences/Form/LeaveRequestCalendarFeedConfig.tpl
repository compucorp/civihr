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
          <div class="col-sm-9">{$form.title.html}</div>
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
      {literal}
        <script type="text/javascript">
          CRM.$(function($) {
            $(document).ready(function() {
              initDeleteButton();
            });

            function initDeleteButton() {
              $('.crm-button-type-delete').on('click', function(e) {
                e.preventDefault();
                CRM.confirm({
                  title: ts('Delete Calendar Feed'),
                  message: ts('Are you sure you want to delete this Calendar Feed?'),
                  options: {
                    yes: ts('Yes'),
                    no: ts('No')
                  }
                }).on('crmConfirm:yes', deleteCallback);
              });
            }

            function deleteCallback() {
              {/literal}
              window.location = "{$deleteUrl}";
              {literal}
            }
          });
        </script>
      {/literal}
    {/if}
    <div class="panel-footer clearfix">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
