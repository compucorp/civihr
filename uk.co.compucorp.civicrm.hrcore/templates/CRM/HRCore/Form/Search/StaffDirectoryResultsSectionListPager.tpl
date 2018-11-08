{if $pager and $pager->_response}
  <div class="panel-body">
    <div class="row form-horizontal">
      <div class="col-sm-5">
        {if $pager->_response.numPages > 1}
          <div>
            <span>
              {$pager->_response.first}
              {$pager->_response.back}
              {$pager->_response.next}
              {$pager->_response.last}
              <label class="control-label">{$pager->_response.status}</label>
            </span>
          </div>
        {/if}
      </div>
      <div class="col-sm-3">
        {if $pager->_response.numPages > 1}
          {if $location eq 'top'}
            {$pager->_response.titleTop}
          {else}
            {$pager->_response.titleBottom}
          {/if}
        {/if}
      </div>
      <div class="col-sm-4 text-right">
        {if $location eq 'bottom' and $pager->_totalItems > 25}
        <label for="{$form.formClass}-rows-per-page-select">{ts}Rows per page:{/ts}</label>
        <input class="crm-rows-per-page-select" id="{$form.formClass}-rows-per-page-select" type="text" size="2" value="{$pager->_perPage}"/>
        {/if}
      </div>


      {if $location neq 'top'}
        <script type="text/javascript">
          {literal}
          CRM.$(function($) {
            {/literal}
            var
              $form = $({if empty($form.formClass)}'#crm-main-content-wrapper'{else}'form.{$form.formClass}'{/if}),
              numPages = {$pager->_response.numPages},
              currentPage = {$pager->_response.currentPage},
              perPageCount = {$pager->_perPage},
              currentLocation = {$pager->_response.currentLocation|json_encode},
              spinning = null,
              refreshing = false;
            {literal}
            function refresh(url) {
              if (!refreshing) {
                refreshing = true;
                var options = url ? {url: url} : {};
                $form.off('.crm-pager').closest('.crm-ajax-container, #crm-main-content-wrapper').crmSnippet(options).crmSnippet('refresh');
              }
            }
            function page(num) {
              num = parseInt(num, 10);
              if (isNaN(num) || num < 1 || num > numPages || num === currentPage) {
                return;
              }
              refresh(currentLocation.replace(/crmPID=\d+/, 'crmPID=' + num));
            }
            function changeCount(num) {
              num = parseInt(num, 10);
              if (isNaN(num) || num < 1 || num === perPageCount) {
                return;
              }
              refresh(currentLocation.replace(/&crmRowCount=\d+/, '') + '&crmRowCount=' + num);
            }
            function preventSubmit(e) {
              if (e.keyCode == 13) {
                e.preventDefault();
                $(this).trigger('change');
                return false;
              }
            }
            $('input[name^=crmPID]', $form)
              .spinner({
                min: 1,
                max: numPages
              })
              .on('change', function() {
                page($(this).spinner('value'));
              })
              .on('keyup keydown keypress', preventSubmit);
            $('input.crm-rows-per-page-select', $form)
              .spinner({
                min: 25,
                step: 25
              })
              .on('change', function() {
                changeCount($(this).spinner('value'));
              })
              .on('keyup keydown keypress', preventSubmit);
            $form
              .on('click.crm-pager', 'a.ui-spinner-button', function(e) {
                var $el = $(this);
                // Update after a short delay to allow multiple clicks
                spinning !== null && window.clearTimeout(spinning);
                spinning = window.setTimeout(function() {
                  if ($el.is('.crm-pager a')) {
                    page($el.siblings('input[name^=crmPID]').spinner('value'));
                  } else {
                    changeCount($el.siblings('input.crm-rows-per-page-select').spinner('value'));
                  }
                }, 200);
              })
              // Handle sorting, paging and alpha filtering links
              .on('click.crm-pager', 'a.crm-pager-link, #alpha-filter a, th a.sorting, th a.sorting_desc, th a.sorting_asc', function(e) {
                refresh($(this).attr('href'));
                e.preventDefault();
              });
          });
          {/literal}
        </script>
      {/if}
    </div>
  </div>
{/if}
