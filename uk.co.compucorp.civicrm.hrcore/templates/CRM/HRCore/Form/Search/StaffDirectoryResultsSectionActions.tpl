<div class="panel-body">
  <div class="row form-group">
    <div class="col-lg-2">
      {ts}Select Records{/ts}:
    </div>
    <div class="col-lg-2">
      {assign var="checked" value=$selectedContactIds|@count}
      <div class="radio-inline">
        {$form.radio_ts.ts_all.html}
        <label for="{$ts_all_id}">{ts count=$pager->_totalItems plural='All %count records'}The found record{/ts}</label>
      </div>
    </div>
    <div class="col-lg-4">
      {if $pager->_totalItems > 1}
        <div class="radio-inline">
          {$form.radio_ts.ts_sel.html}
          <label for="{$ts_sel_id}">{ts 1="<span>$checked</span>"}%1 Selected records only{/ts}</label>
        </div>
      {/if}
    </div>
    <div class="col-lg-2">
      {* Hide export button in 'Add Members to Group' context. *}
      {if $context NEQ 'amtg'}
         {$form.task.html}
      {/if}
      {if $action eq 512}
        {$form._qf_Advanced_next_action.html}
      {elseif $action eq 8192}
        {$form._qf_Builder_next_action.html}&nbsp;&nbsp;
      {elseif $action eq 16384}
        {$form._qf_Custom_next_action.html}&nbsp;&nbsp;
      {else}
        {$form._qf_Basic_next_action.html}
      {/if}
    </div>
    <div class="col-lg-2">
      <a href="#" class="crm-selection-reset">
        <button class="btn btn-default btn-sm">{ts}Reset all selections{/ts}</button>
      </a>
    </div>
  </div>
</div>
