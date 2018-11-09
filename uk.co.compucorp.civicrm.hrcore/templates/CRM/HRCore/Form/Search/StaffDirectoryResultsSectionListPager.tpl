{if $pager and $pager->_response && $pager->_response.numPages > 1}
  <div class="panel-body staff-directory__results-list_pager">
    <div class="row form-horizontal">
      <div class="col-sm-5">
        <div>
          <span>
            {$pager->_response.first}
            {$pager->_response.back}
            {$pager->_response.next}
            {$pager->_response.last}
            <label class="control-label">{$pager->_response.status}</label>
          </span>
        </div>
      </div>
      <div class="col-sm-3">
        {if $location eq 'top'}
          {$pager->_response.titleTop}
        {else}
          {$pager->_response.titleBottom}
        {/if}
      </div>
      <div class="col-sm-4 text-right">
        {if $location eq 'bottom' and $pager->_totalItems > 25}
          <label for="{$form.formClass}-rows-per-page-select">{ts}Rows per page:{/ts}</label>
          <input class="crm-rows-per-page-select" id="{$form.formClass}-rows-per-page-select" type="text" size="2" value="{$pager->_perPage}"/>
        {/if}
      </div>
    </div>
  </div>
{/if}
