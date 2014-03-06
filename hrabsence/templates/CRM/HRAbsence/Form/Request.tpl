{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                 |
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
{* Form to Add, Edit, Approve, Reject absence request *}
<div class="crm-block crm-content-block">
  <table class="abempInfo" style="width: auto; border: medium none ! important;">
  {if $contactDataURL AND $permEditContact}
    <tr>
      <td>{ts}Employee{/ts}</td>
      <td colspan="2">{$form.contacts.html}</td>
    </tr>
  {else}
    <tr>
      <td>{ts}Employee{/ts}</td>
      <td colspan="2"> {if $permContact} <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$emp_id"}"> {$emp_name} {else}  {$emp_name} {/if}</td>
    </tr>
  {/if}
    <tr id="position">
      <td>{ts}Position{/ts}</td>
      <td colspan="2">{$emp_position}</td>
    </tr>
    <tr>
      <td>{ts}Absence Type{/ts}</td> 
      <td colspan="2">{$absenceType}</td> 
    </tr>
    <tr class="crm-event-manage-eventinfo-form-block-start_date">
      <td class="label">{ts}Dates{/ts}</td>
      <td>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</td>
      <td>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
    </tr>
  </table>
  <table id="tblabsence" class="report">
    <tbody>
      <tr class="tblabsencetitle">
        <td>{ts}Date{/ts}</td>
        <td>{ts}Absence{/ts}</td>
      </tr>
    </tbody>
  </table>

   <div id="commentDisplay">
     {ts}(Please mark absences for dates that you need off. If you don't normally work on a weekened or holiday, omit it.){/ts}
   </div>

</div>
<div id='customData'>{include file="CRM/Custom/Form/CustomData.tpl"}</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>

{include file="CRM/common/customData.tpl" location="bottom"}
{literal}
  <script type="text/javascript">
    cj(function($) {
    var $form = $('form#{/literal}{$form.formName}{literal}');
    var dataUrl = "{/literal}{$contactDataURL}{literal}";
    $('#contacts', $form).autocomplete( dataUrl, { width : 180, selectFirst : false, matchContains: true });
    $('#contacts', $form).result(function( event, data ) {
    $("input[name=contacts_id]", $form).val(data[1]);
    var contactid = data[1];
    CRM.api('HRJob', 'get', {'sequential': 1, 'contact_id': contactid, 'is_primary': 1},
      {success: function(data) {
        $.each(data.values, function(key, value) {
          $('#position td:nth-child(2)', $form).html(value.position);
	});
      }
    }
    );
    });
    
    $('span.crm-error', $form).insertAfter('input#end_date_display', $form);
      {/literal}
        {if $customValueCount}
          {foreach from=$customValueCount item="groupCount" key="groupValue"}
            {if $groupValue}{literal}
              for ( var i = 1; i < {/literal}{$groupCount}{literal}; i++ ) {
                CRM.buildCustomData( 'Activity', {/literal}"{$activityType}"{literal}, i, {/literal}{$groupValue}{literal}, true);
              }
              {/literal}
            {/if}
          {/foreach}
        {/if}
      {literal}

    $('#start_date_display', $form).change(function() {
      addabsencetbl();
      var end_date = $('#end_date_display', $form).datepicker( "getDate" );
      var start_date = $('#start_date_display', $form).datepicker( "getDate" );
      if (!end_date){
        $('#end_date_display', $form).datepicker('setDate', start_date);
        addabsencetbl();	
      }
    })
    $('#end_date_display', $form).change(function() {
      addabsencetbl();
    })

    var additn = 0;
    $form.on('change','#tblabsence select', function(){
      var selectoptn = $(this).val();
      additn = new Number(additn) + new Number(selectoptn);
    });
  
  // Function is used to add absence table based on selected date.
  function addabsencetbl() {
    var end_date = $('#end_date_display', $form).datepicker( "getDate" );
    var start_date = $('#start_date_display', $form).datepicker( "getDate" );
    if (start_date && end_date) {
      $("#tblabsence", $form).show();
      $("#commentDisplay", $form).show();
    }
    var pubHoliday = {/literal}{$publicHolidays}{literal};
    var d = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    $('table#tblabsence tbody tr.trabsence', $form).remove();
    var selectedVal = [];
    for (var x = 0; x <= d; x++) {
      var earlierdate = new Date(start_date);
      var absenceDate = earlierdate.toDateString();
      var sDate = absenceDate.substring(4,7)+' '+earlierdate.getDate()+','+' '+earlierdate.getFullYear();
      if ( sDate in pubHoliday ) {
        var holidayDes = pubHoliday[sDate];
        var holidayDesc = ", "+holidayDes;
        var startDate = absenceDate.substring(4,7)+' '+earlierdate.getDate()+','+' '+earlierdate.getFullYear()+' ('+absenceDate.substring(0,3)+''+holidayDesc+')';
      }
      else{
        var startDate = absenceDate.substring(4,7)+' '+earlierdate.getDate()+','+' '+earlierdate.getFullYear()+' ('+absenceDate.substring(0,3)+')';
      }
      var abday = absenceDate.substring(0,3);
      if ((abday == 'Sat' || abday == 'Sun') || (sDate in pubHoliday)) {
        var createSelectBox = '<tr class="trabsence" ><td><label id="label_'+x+'" >'+startDate+'</label></td><td><select id="options_'+x+'" class="form-select" disabled="disabled" ><option value=""></option><option value="1">Full Day</option><option value="0.5">Half Day</option></select></td></tr>';
      }
      else {
      	var createSelectBox = '<tr class="trabsence" ><td><label id="label_'+x+'" >'+startDate+'</label></td><td><select id="options_'+x+'" class="form-select"><option value="1">Full Day</option><option value="0.5">Half Day</option><option value=""></option></select></td></tr>';
      }
      $('form#AbsenceRequest table#tblabsence tbody').append(createSelectBox);
      var numberOfDaysToAdd = 1;
      start_date.setDate(start_date.getDate() + numberOfDaysToAdd);
      var dd = start_date.getDate();
      var mm = start_date.getMonth() + 1;
      if (mm<10) mm="0"+mm;
      if (dd<10) dd="0"+dd;
      var y = start_date.getFullYear();
      var start_date = new Date(mm+"/"+dd+"/"+y);
      selectedVal.push(x);
    }    
    var countDays = 0;
    var end_date = $('#end_date_display', $form).datepicker( "getDate" );
    var start_date = $('#start_date_display', $form).datepicker( "getDate" );
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    var totalDays=0;
    for (var x = 0; x <=diDate; x++) {
      var selectopt = $('#options_'+x+' :selected', $form).val();	
      totalDays = new Number(totalDays) + new Number(selectopt);
    }
    if (totalDays <= 1) {
      totalDays += ' {/literal}{ts}day{/ts}{literal}';
    }
    else {
      totalDays += ' {/literal}{ts}days{/ts}{literal}';
    }
    $('#countD', $form).html(totalDays);
  }

    var uID = '{/literal}{$loginUserID}{literal}';
    var absencesTypeID = $('#activity_type_id', $form).val();
    var upActivityId = '{/literal}{$upActivityId}{literal}';
    var upfromDate = '{/literal}{$fromDate}{literal}';
    var uptoDate = '{/literal}{$toDate}{literal}';

      {/literal}{if $mode eq 'edit'}{literal}
        $("#tblabsence", $form).show();
        $('input#start_date_display', $form).datepicker('setDate', upfromDate);
        $('input#end_date_display', $form).datepicker('setDate', uptoDate);
        var end_date = $('#end_date_display', $form).datepicker( "getDate" );
        var start_date = $('#start_date_display', $form).datepicker( "getDate" );
        var difDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
        var pubHoliday = {/literal}{$publicHolidays}{literal};
        var param = {};
        CRM.api('Activity', 'get', {'sequential': 1, 'source_record_id': upActivityId, 'option_sort': 'activity_date_time ASC', 'option.limit': 31},
          {success: function(data) {
            $.each(data.values, function(key, value) {
            var val = value.activity_date_time;
            param[val]=value.duration;
          });   
	  var x=0;
          var selectopt;
          var totalDays=0;
          $.each(param, function(key, value) {
            var datepicker = key;
            var parms = datepicker.split("-");
            var subpar2 = parms[2].substring(0,3);
            var joindate = new Date(parms[1]+"/"+subpar2+"/"+parms[0]);
            var absenceDate = joindate.toDateString();
            var abdate = absenceDate.substring(4,7)+' '+joindate.getDate()+','+' '+joindate.getFullYear()+' ('+absenceDate.substring(0,3)+')';
            var abday = absenceDate.substring(0,3);
            var sDate = absenceDate.substring(4,7)+' '+joindate.getDate()+','+' '+joindate.getFullYear();
            if ( sDate in pubHoliday ) {
              var holidayDes = pubHoliday[sDate];
              var holidayDesc = ", "+holidayDes;
              var abdate = absenceDate.substring(4,7)+' '+joindate.getDate()+','+' '+joindate.getFullYear()+' ('+absenceDate.substring(0,3)+''+holidayDesc+')';
            }
            else {
              var abdate = absenceDate.substring(4,7)+' '+joindate.getDate()+','+' '+joindate.getFullYear()+' ('+absenceDate.substring(0,3)+')';
            }
	    var createSelectBox = '<tr class="trabsence"><td><label id="label_'+x+'" >'+abdate+'</label></td><td><select id="options_'+x+'" class="form-select"><option value="1">Full Day</option><option value="0.5">Half Day</option><option value=""></option></select></td></tr>';
            $('form#AbsenceRequest table#tblabsence tbody').append(createSelectBox);
            if (value==240) {
              $("#options_"+x).val('0.5');
              selectopt = $('#options_'+x+' :selected').val();
              totalDays = new Number(totalDays) + new Number(selectopt);
	    } 
	    else if (value==480) {
	      $("#options_"+x, $form).val('1');
	      selectopt = $('#options_'+x+' :selected', $form).val();
	      totalDays = new Number(totalDays) + new Number(selectopt);
	    }
	    else {
	      $("#options_"+x, $form).val('');
                if (abday == 'Sat' || abday == 'Sun') {
                  $("#options_"+x, $form).attr("disabled","disabled");
                }
           }
	    x = new Number(x) + 1;
 	  });
          if (totalDays <= 1) {
	    totalDays += ' {/literal}{ts}day{/ts}{literal}';
          }
          else {
            totalDays += ' {/literal}{ts}days{/ts}{literal}';
	  }
	  $('#countD', $form).html(totalDays);
        }
      });

      $("#_qf_AbsenceRequest_submit-bottom", $form).click(function(event){
        var dateValues = [];
        var end_date = $('#end_date_display', $form).datepicker( "getDate" );
        var start_date = $('#start_date_display', $form).datepicker( "getDate" );
        var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
        for (var x = 0; x <= diDate; x++) {
          var selDate = $('#label_'+x, $form).text();
          var selectopt = $('#options_'+x+' :selected', $form).text();
          if (selectopt == "Full Day"){
            dateValues[x] = selDate +":" + "480";
          }
          else {
            if (selectopt == "Half Day"){
              dateValues[x] = selDate +":" + "240";
            }
	    else {
	      dateValues[x] = selDate +":" + "0";
	    }
          }
        }
    	$("#date_values", $form).val(dateValues.join('|'));
      });

{/literal}{/if}{literal}
{/literal}{if $action eq 1}{literal}
  $("#tblabsence", $form).hide();
  $("#commentDisplay", $form).hide();
  var dateValues = [];
  $("#_qf_AbsenceRequest_submit-bottom", $form).click(function(event){
    var end_date = $('#end_date_display', $form).datepicker( "getDate" );
    var start_date = $('#start_date_display', $form).datepicker( "getDate" );
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
      for (var x = 0; x <= diDate; x++) {
        var selDate = $('#label_'+x, $form).text();
        var selectopt = $('#options_'+x+' :selected', $form).text();
        if (selectopt == "Full Day") {
          dateValues[x] = selDate +":" + "480";
        }
        else {
          if (selectopt == "Half Day") {
            dateValues[x] = selDate +":" + "240";
          }
	  else{
	    dateValues[x] = selDate +":" + "0";
	  }
        }
      }
    $("#date_values", $form).val(dateValues.join('|'));
  });
{/literal}{/if}{literal}

  var countDays = 0;
  $('#tblabsence tbody:last', $form).after('<tr class="tblabsencetitle"><td>{/literal}{ts}Total{/ts}{literal}</td><td id="countD">'+countDays+'</td></tr>');
  $form.on('change','#tblabsence select', function(){
    var end_date = $('#end_date_display', $form).datepicker( "getDate" );
    var start_date = $('#start_date_display', $form).datepicker( "getDate" );
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    var totalDays=0;
    for (var x = 0; x <=diDate; x++) {
      var selectopt = $('#options_'+x+' :selected', $form).val();	
      totalDays = new Number(totalDays) + new Number(selectopt);
    }
    if (totalDays <= 1) {
      totalDays += ' {/literal}{ts}day{/ts}{literal}';
    }
    else {
      totalDays += ' {/literal}{ts}days{/ts}{literal}';
    }
    $('#countD', $form).html(totalDays);
  });
});
</script>
{/literal}
