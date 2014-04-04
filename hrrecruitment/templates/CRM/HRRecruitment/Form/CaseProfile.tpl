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
  <span class="hr-case-action-item">{$form.stages.html}</span>
  <span class='hidden-link' style='display:none'></span>
</div>

<div class="crm-clearfix hr-case-application-profile">
  {include file="CRM/UF/Form/Block.tpl" fields=$profileFields}
  {include file="CRM/Case/Page/CustomDataView.tpl"}
  {include file="CRM/Case/Form/ActivityTab.tpl"}
</div>

{literal}
<script>
cj(function($){
  $("#stages > option").each(function() {
    if(this.value >= {/literal}{$statusID}{literal}) {
      $(this).addClass("highWeight");
    }
  });

  $('.hr-case-action-item select option')
    .on('click', function() {
      var $this = $(this).parent();
      changeCaseAction($this);
    });

  function changeCaseAction($this) {
    var tabid = ''+$this.val(),
      activeAria = $('.ui-tabs-active').attr("id"),
      active = 'a.button, a.action-item, a.crm-popup',
      link,
      sourceUrl = {/literal}"{crmURL p='civicrm/case/activity' h=0 q='reset=1&action=add&cid='}{$contactID}&atype=16&selectedChild=activity"{literal};

    sourceUrl = sourceUrl + '&caseid={/literal}{$caseID}{literal}';
    sourceUrl = sourceUrl + '&statusid=' + tabid;
    link = "<a href="+sourceUrl+" id='hrcaseApplicantLink' class='action-item crm-popup'>"+$this.val()+"</a>";
    $('.hidden-link').html(link);
    $( "#hrcaseApplicantLink" ).trigger( "click" );

    $('#crm-main-content-wrapper')
      // Widgetize the content area
      .crmSnippet()
      // Open action links in a popup
      .off('.crmLivePage')
      .on('click#hrcaseApplicantLink', active, CRM.popup)
      .on('crmPopupFormSuccess.crmLivePage', active, function(e) {
        // Refresh page when form completes
        CRM.tabHeader.resetTab('#'+activeAria);
        CRM.tabHeader.focus('#tab_'+tabid);
        CRM.tabHeader.resetTab('#tab_'+tabid);
      });
  }
});
</script>
{/literal}
