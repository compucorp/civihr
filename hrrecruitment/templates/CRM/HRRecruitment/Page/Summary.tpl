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
  <div class="crm-clearfix">
    <div class="crm-accordion-header"><h2>{$status.title}</h2></div>
    <div class="crm-accordion-body crm-clearfix">
      {if isset($status.$statusID.vacancies)}
        {foreach from=$status.$statusID.vacancies key="vacancyID" item="vacancy"}
          <div class="hr-vacancy" style="float:{cycle values='left,right'}">
            <div>
              <table>
                <tr>
                  <td>
                    <h3>
                      <a class="hr-vacancy-title" href="{crmURL p='civicrm/case/pipeline' q="reset=1&vid=$vacancyID"}">{$vacancy.position}</a>
                      <a class="crm-hover-button action-item" href="{crmURL p='civicrm/vacancy/add' q="reset=1&id=$vacancyID"}" title="{ts}Edit this vacancy{/ts}"><span class="icon edit-icon"></span></a>
                    </h3>
                  </td>
                </tr>
                <tr><td>{$vacancy.location}</td></tr>
                <tr><td>({$vacancy.date})</td></tr>
                {if !empty($vacancy.stages)}
                  <tr>
                    <td>
                      <ul class="hr-stage-pipeline">
                        {foreach from=$vacancy.stages key="weight" item="stage"}
                          {math assign=fraction equation="x/y" x=$weight y=$vacancy.stages|@count}
                          {math assign=red equation="150-(100*x)" x=$fraction format="%.00f"}
                          {math assign=green equation="100*x" x=$fraction format="%.00f"}
                          <li class="hr-stage" style="border-left: 70px solid rgb({$red},{$green}, 50);">
                            <a class="hr-stage-link" href="{crmURL p='civicrm/case/pipeline' q="reset=1&vid=$vacancyID"}" title="{ts 1=$stage.count 2=$stage.title}%1 application(s) with status '%2'{/ts}">{$stage.count}</a>
                          </li>
                        {/foreach}
                      </ul>
                    </td>
                  </tr>
                {/if}
              </table>
            </div>
          </div>
        {/foreach}
      {/if}
    </div>
  </div>
{/foreach}
