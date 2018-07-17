<div id="bootstrap-theme">
  <div class="panel panel-default crm-form-block crm-absence_type-form-block crm-leave-and-absences-form-block">
    <div class="panel-heading">
      <h1 class="panel-title">
        {if $action eq 1}{ts}New Public Holiday{/ts}
        {elseif $action eq 2}{ts}Edit Public Holiday{/ts}{/if}
      </h1>
    </div>
    {if $action neq 8}
    <div class="panel-body">
      <div class="col-sm-8">
        <h3>{ts}Basic Details{/ts}</h3>
        <div class="form-group row">
          <div class="col-sm-6">{$form.title.label}</div>
          <div class="col-sm-6">{$form.title.html}</div>
        </div>
        <div class="form-group row">
            <div class="col-sm-6">{$form.date.label}</div>
            <div class="col-sm-6">{$form.date.html}</div>
        </div>
        <div class="form-group row">
            <div class="col-sm-6">{$form.is_active.label}</div>
            <div class="col-sm-6">{$form.is_active.html}</div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      {literal}
        CRM.$(function($) {
          var REDIRECT_AFTER_DELETE_URL = {/literal}'{$deleteUrl}'{literal};

          $(document).ready(function() {
            initDeleteButton();
          });

          function initDeleteButton() {
            $('.crm-button-type-delete').on('click', function(e) {
              e.preventDefault();
              CRM.confirm({
                title: ts('Delete Public Holiday'),
                message: ts('Are you sure you want to delete this Public Holiday?'),
                options: {
                  yes: ts('Yes'),
                  no: ts('No')
                }
              })
              .on('crmConfirm:yes', REDIRECT_AFTER_DELETE_URL);
            });
          }

          function deleteCallback() {
            window.location = deleteUrl;
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
