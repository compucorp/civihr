<div class="panel-body">
  <div class="row">
    <div class="col-lg-9 col-sm-8">
      <h1 class="media-heading">
        {if $pager->_totalItems > 0}
          {ts count=$pager->_totalItems plural='%count Staff Members'}%count Staff Member{/ts}
        {else}
          {ts}No Staff Members found{/ts}
        {/if}
      </h1>
    </div>
    <div class="col-lg-3 col-sm-4">
      <div class="input-group">
        {$form.name.html}
        <div class="input-group-btn">
          <input class="btn btn-sm btn-primary" name="_qf_Custom_refresh" value="Search" type="submit"/>
        </div>
      </div>
      <div class="text-right">
        <a href="advanced">Advanced search</a>
      </div>
    </div>
  </div>
</div>
