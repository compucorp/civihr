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
{foreach from=$vacanciesByStatus item="status" key="statusID"}
<ul class="vacancy-summary">
  <div class="crm-accordion-header"><h1>{$status.title}</h1></div>
    <div class="crm-accordion-body">
      {if isset($status.$statusID.vacancies)}
        {foreach from=$status.$statusID.vacancies key="vacancyID" item="vacancy"}
          <li id="vacancy-position">
            <table style="background-color: #EBEBEB;">
              <tr><td><h3><a href="{crmURL p='civicrm/case/pipeline' q="reset=1&vid=$vacancyID"}">{$vacancy.position}</a></h3></td></tr>
              <tr><td>{$vacancy.location}</td></tr>
              <tr><td>({$vacancy.date})</td></tr>
              {if !empty($vacancy.stages)}
              <tr>
                <td>
                  <br>
                  <div>
                    {foreach from=$vacancy.stages key="weight" item="stage"}
                      <span class="arrow-right {if $weight eq $vacancy.stages|@count}stage-end{else}stage-{$weight}{/if}" title="{$stage.count} Application(s) with status '{$stage.title}'">
                        <font>{$stage.count}</font>
                      </span>
                    {/foreach}
                  </div>
                  <br>
                </td>
              </tr>
            {/if}
          </table>
        </li>
      {/foreach}
    {/if}
  </div>

</ul>
{/foreach}
