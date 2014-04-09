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
<div class="crm-clearfix hr-case-application-action-button">
  <a title="Email" class="crm-popup button" href="/civihr/civicrm/case/activity/email/add?atype=3&statusid={$activityStatsId}&caseid={$case_id}&action=add&reset=1&context=standalone" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-2">Email</a>
  <a title="Comment" class="crm-popup button" href="/civihr/civicrm/case/activity/add?action=add&reset=1&cid={$contactID}&caseid={$case_id}&statusid={$activityStatsId}&selectedChild=activity&atype={$activityType}" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-2">Comment</a>
  <span class="hr-activity-action-item">{$form.new_activity.html}</span>
  <span class='activity-link' style='display:none'></span>
</div>

<div class="crm-clearfix hr-case-application-profile">
  {include file="CRM/UF/Form/Block.tpl" fields=$profileFields}
  {include file="CRM/Case/Page/CustomDataView.tpl"}
  {include file="CRM/Case/Form/ActivityTab.tpl"}
</div>

{literal}
<script>
  cj(function($){
    $('.hr-activity-action-item select option').on('click', function() {
      var $this = $(this).parent();
      changeActivityAction($this);
    });
    function changeActivityAction($this) {
      var tabid = ''+$this.val(),
      activeAria = $('.ui-tabs-active').attr("id"),
      active = 'a.button, a.action-item, a.crm-popup',
      link,
      sUrl = {/literal}"{crmURL p='civicrm/case/activity/add' h=0 q='action=add&reset=1&cid='}{$contactID}&selectedChild=activity"{literal};
      sUrl = sUrl + '&atype={/literal}{$activityLetterId}{literal}';
      sUrl = sUrl + '&caseid={/literal}{$case_id}{literal}';
      link = "<a href="+sUrl+" id='hrnewActivityLink' class='action-item crm-popup'>"+$this.val()+"</a>";
      $('.activity-link').html(link);
      $( "#hrnewActivityLink" ).trigger( "click" );
      $('#crm-main-content-wrapper')
      // Widgetize the content area
      .crmSnippet()
      .off('.crmLivePage')
      .on('click#hrnewActivityLink', active, CRM.popup)
      .on('crmPopupFormSuccess.crmLivePage', active, function(e) {
      });
    }
  });
</script>
{/literal}
