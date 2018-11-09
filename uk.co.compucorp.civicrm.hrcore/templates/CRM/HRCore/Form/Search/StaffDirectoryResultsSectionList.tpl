{include file="CRM/HRCore/Form/Search/StaffDirectoryResultsSectionListPager.tpl" location="top"}

<div class="staff-directory__results-list table-responsive">
  <table summary="{ts}Search results listings.{/ts}" class="table table-clean selector row-highlight">
    <tr>
      <th scope="col" title="Select All Rows">
        <div class="checkbox-inline"><label></label>{$form.toggleSelect.html}</div>
      </th>
      {if $context eq 'smog'}
        <th scope="col">
          {ts}Status{/ts}
        </th>
      {/if}
      {foreach from=$columnHeaders item=header}
        <th scope="col">
          {if $header.sort}
            {assign var='key' value=$header.sort}
            {$sort->_response.$key.link}
          {else}
            {$header.name}
          {/if}
        </th>
      {/foreach}
      <th scope="col">
        {ts}Actions{/ts}
      </th>
    </tr>
    {counter start=0 skip=1 print=false}
    {foreach from=$rows item=row}
      <tr id="rowid{$row.contact_id}" class="{cycle values='odd-row,even-row'}">
        {assign var=cbName value=$row.checkbox}
        <td>
          <div class="checkbox-inline"><label></label>{$form.$cbName.html}<div>
        </td>
        {if $context eq 'smog'}
          {if $row.status eq 'Pending'}<td class="status-pending"}>
          {elseif $row.status eq 'Removed'}<td class="status-removed">
          {else}<td>{/if}
          {$row.status}</td>
        {/if}
        {foreach from=$columnHeaders item=value key=column}
          {assign var='columnName' value=$value.sort}
          {if $columnName neq 'action'}
            <td class="crm-{$columnName} crm-{$columnName}_{$row.columnName}">{$row.$columnName} </td>
          {/if}
        {/foreach}
        <td class="staff-directory__results-list_actions">{$row.action|replace:'xx':$row.contact_id}</td>
      </tr>
    {/foreach}
  </table>
</div>

<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    initContactsCheckboxes();
    transformCiviDropdownIntoBootstrap();

    /**
     * Initiates contacts checkboxes in the table.
     * 1. Clear any old selections
     * 2. Retrieves stored checkboxes
     */
    function initContactsCheckboxes () {
      var cids = {/literal}{$selectedContactIds|@json_encode}{literal};

      $("input.select-row, input.select-rows", 'form.crm-search-form')
        .prop('checked', false)
        .closest('tr')
        .removeClass('crm-row-selected');

      if (cids.length > 0) {
        $('#mark_x_' + cids.join(',#mark_x_') + ',input[name=radio_ts][value=ts_sel]')
          .prop('checked', true);
      }
    }

    /**
     * Moves dropdown to other container. This is needed if the original
     * container is overflowed and clips the dropdown.
     * It watches for changes in `class` attribute of the wrapper to detect
     * the `open` class. If class is detected, then it positions the dropdown
     * relatively to the wrapper, if not, then it simply hides it.
     *
     * @param {jQuery} $dropdownWrapper element that holds the trigger and the dropdown
     * @param {String} targetContainerSelector selector of an element to move the dropdown to
     */
    function moveDropdownToOtherContainer ($dropdownWrapper, targetContainerSelector) {
      var $dropdown = $dropdownWrapper.find('.dropdown-menu');
      var observer = new MutationObserver(function () {
        if ($dropdownWrapper.hasClass('open')) {
          $dropdown
            .css({
              top: $dropdownWrapper.position().top + $dropdownWrapper.height()
            })
            .show();
        } else {
          $dropdown.hide();
        }
      });

      $dropdown.appendTo(targetContainerSelector);
      observer.observe($dropdownWrapper[0], {
        attributes: true,
        attributeFilter: ['class'],
        childList: false,
        characterData: false
      });
    }

    /**
     * Transforms CiviCRM dropdowns into Boostrap dropdowns
     * 1. It removes classes from the wrapper and adds according to the Styleguide
     * 2. It appends a button with an ellipsis and removes the default ellipsis
     * 3. It removes classes from the <ul> element and adds according to the Styleguide
     * 4. It removes classes from the <a> element and adds according to the Styleguide
     */
    function transformCiviDropdownIntoBootstrap () {
      $.each($('.staff-directory__results-list .btn-slide'), function (index, dropdownWrapper) {
        var $dropdownWrapper = $(dropdownWrapper);

        $dropdownWrapper
          .removeClass('btn-slide')
          .removeClass('crm-hover-button')
          .addClass('btn-group')
          .prepend('<button class="fa fa-ellipsis-v dropdown-toggle btn btn-default btn-sm" data-toggle="dropdown"></button>')
          .contents()
          .filter(function() {
            return this.nodeType == 3;
          })
          .remove();
        $dropdownWrapper
          .find('ul')
          .removeClass('panel')
          .addClass('dropdown-menu')
          .addClass('dropdown-menu-right');
        $dropdownWrapper
          .find('a')
          .removeClass('crm-hover-button');
        moveDropdownToOtherContainer($dropdownWrapper, '#bootstrap-theme');
      });
    }
  });
  {/literal}
</script>
{include file="CRM/HRCore/Form/Search/StaffDirectoryResultsSectionListPager.tpl" location="bottom"}
{include file="CRM/HRCore/Form/Search/StaffDirectoryResultsSectionListPagerScript.tpl"}
