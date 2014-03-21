{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.3                                                 |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
  <div class="crm-block crm-content-block crm-inline-edit-container">
    <div id="mainTabContainer">
      <ul class="crm-case-stage-tabs-list">
        {foreach from=$allTabs key=tabName item=tabValue}
          <li id="tab_{$tabValue.id}" class="crm-tab-button ui-corner-all crm-count-{$tabValue.count}{if isset($tabValue.class)} {$tabValue.class}{/if}">
            <a href="{$tabValue.url}" title="{$tabValue.title}">
              <span> </span> {$tabValue.title}
              <em>{$tabValue.count}</em>
            </a>
          </li>
        {/foreach}
      </ul>
    </div>
  </div>

