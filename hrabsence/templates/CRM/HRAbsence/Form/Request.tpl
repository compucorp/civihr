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
{* Search form and results for Event Participants *}
  {assign var='fldName' value=$prefix|cat:'contact'}
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
      <td colspan="2">{$emp_name}</td>
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
    cj(function(cj) {
    var dataUrl = "{/literal}{$contactDataURL}{literal}";
    cj('#contacts').autocomplete( dataUrl, { width : 180, selectFirst : false, matchContains: true });
    cj('#contacts').result(function( event, data ) {
    cj("input[name=contacts_id]").val(data[1]);
    var contactid = data[1];
    CRM.api('HRJob', 'get', {'sequential': 1, 'contact_id': contactid, 'is_primary': 1},
      {success: function(data) {
        cj.each(data.values, function(key, value) {
	  cj('#position td:nth-child(2)').html(value.position); // do something 
	});
      }
    }
    );
    });
    
    cj('span.crm-error').insertAfter('input#end_date_display');
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

    cj('#start_date_display').change(function() {
      addabsencetbl();
      var end_date = cj('#end_date_display').val();
      var start_date = cj('#start_date_display').val();
      if (end_date == ""){
        cj('#end_date_display').datepicker('setDate', start_date);
        addabsencetbl();	
      }
    })
    cj('#end_date_display').change(function() {
      addabsencetbl();
    })

    var additn = 0;
    cj(document).on('change','#tblabsence select', function(){
      var selectoptn = cj(this).val();
      additn = new Number(additn) + new Number(selectoptn);
    });
  });  

  function addabsencetbl() {
    var end_date = cj('#end_date_display').val();
    var start_date = cj('#start_date_display').val();
    if (start_date && end_date) {
      cj("#tblabsence").show();
      cj("#commentDisplay").show();
    }
    var pubHoliday = {/literal}{$publicHolidays}{literal};
    var d = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    cj('table#tblabsence tbody tr.trabsence').remove();
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
      cj('form#AbsenceRequest table#tblabsence tbody').append(createSelectBox);
      var datepicker = start_date;
      var parms = datepicker.split("/");
      var joindate = new Date(parms[0]+"/"+parms[1]+"/"+parms[2]);
      var numberOfDaysToAdd = 1;
      joindate.setDate(joindate.getDate() + numberOfDaysToAdd);
      var dd = joindate.getDate();
      var mm = joindate.getMonth() + 1;
      if (mm<10) mm="0"+mm;
      if (dd<10) dd="0"+dd;
      var y = joindate.getFullYear();
      var start_date = mm + '/' + dd + '/' + y;
      selectedVal.push(x);
    }    
    var countDays = 0;
    var end_date = cj('#end_date_display').val();
    var start_date = cj('#start_date_display').val();
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    var totalDays=0;
    for (var x = 0; x <=diDate; x++) {
      var selectopt = cj('#options_'+x+' :selected').val();	
      totalDays = new Number(totalDays) + new Number(selectopt);
    }
    if (totalDays <= 1) {
      totalDays += ' {/literal}{ts}day{/ts}{literal}';
    }
    else {
      totalDays += ' {/literal}{ts}days{/ts}{literal}';
    }
    cj('#countD').html(totalDays);
  }
  </script>
{/literal}

{literal}
  <script type="text/javascript">
    var uID = '{/literal}{$loginUserID}{literal}';
    var absencesTypeID = cj('#activity_type_id').val();
    var upActivityId = '{/literal}{$upActivityId}{literal}';
    var upfromDate = '{/literal}{$fromDate}{literal}';
    var uptoDate = '{/literal}{$toDate}{literal}';

    cj(document).ready(function() {
      {/literal}{if $action eq 2}{literal}
        cj("#tblabsence").show();
        cj('input#start_date_display').val(upfromDate);
        cj('input#end_date_display').val(uptoDate);
        var end_date = cj('#end_date_display').val();
        var start_date = cj('#start_date_display').val();
        var difDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
        var pubHoliday = {/literal}{$publicHolidays}{literal};
        var param = cj.parseJSON('{}');
        CRM.api('Activity', 'get', {'sequential': 1, 'source_record_id': upActivityId, 'option_sort': 'activity_date_time ASC', 'option.limit': 31},
          {success: function(data) {
            cj.each(data.values, function(key, value) {
            var val = value.activity_date_time;
            param[val]=value.duration;
          });   
	  var x=0;
          var selectopt;
          var totalDays=0;
          cj.each(param, function(key, value) {
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
            cj('form#AbsenceRequest table#tblabsence tbody').append(createSelectBox);
            if (value==240) {
	      cj("#options_"+x).val('0.5');
	      selectopt = cj('#options_'+x+' :selected').val();
	      totalDays = new Number(totalDays) + new Number(selectopt);
	    } 
	    else if (value==480) {
	      cj("#options_"+x).val('1');
	      selectopt = cj('#options_'+x+' :selected').val();
	      totalDays = new Number(totalDays) + new Number(selectopt);
	    }
	    else {
	      cj("#options_"+x).val('');
	      cj("#options_"+x).attr("disabled","disabled");
	    }
	    x = new Number(x) + 1;
 	  });
          if (totalDays <= 1) {
	    totalDays += ' {/literal}{ts}day{/ts}{literal}';
          }
          else {
            totalDays += ' {/literal}{ts}days{/ts}{literal}';
	  }
	  cj('#countD').html(totalDays);
        }
      });

      cj("#_qf_AbsenceRequest_submit-bottom").click(function(event){
        var dateValues = [];
        var params = cj.parseJSON('{"sequential": "1"}');
        var end_date = cj('#end_date_display').val();
        var start_date = cj('#start_date_display').val();
        var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
        for (var x = 0; x <= diDate; x++) {
          var selDate = cj('#label_'+x).text();
          var selectopt = cj('#options_'+x+' :selected').text();
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
    	cj("#date_values").val(dateValues.join('|'));
      });

{/literal}{/if}{literal}
{/literal}{if $action eq 1}{literal}
  cj("#tblabsence").hide();
  cj("#commentDisplay").hide();
  var dateValues = [];
  cj("#_qf_AbsenceRequest_submit-bottom").click(function(event){
    var params = cj.parseJSON('{"sequential": "1"}');
    var end_date = cj('#end_date_display').val();
    var start_date = cj('#start_date_display').val();
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
      for (var x = 0; x <= diDate; x++) {
        var selDate = cj('#label_'+x).text();
        var selectopt = cj('#options_'+x+' :selected').text();
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
    cj("#date_values").val(dateValues.join('|'));
  });
{/literal}{/if}{literal}
});

  var countDays = 0;
  cj('#tblabsence tbody:last').after('<tr class="tblabsencetitle"><td>{/literal}{ts}Total{/ts}{literal}</td><td id="countD">'+countDays+'</td></tr>');
  cj(document).on('change','#tblabsence select', function(){
    var end_date = cj('#end_date_display').val();
    var start_date = cj('#start_date_display').val();
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    var totalDays=0;
    for (var x = 0; x <=diDate; x++) {
      var selectopt = cj('#options_'+x+' :selected').val();	
      totalDays = new Number(totalDays) + new Number(selectopt);
    }
    if (totalDays <= 1) {
      totalDays += ' {/literal}{ts}day{/ts}{literal}';
    }
    else {
      totalDays += ' {/literal}{ts}days{/ts}{literal}';
    }
    cj('#countD').html(totalDays);
  });
</script>
{/literal}
