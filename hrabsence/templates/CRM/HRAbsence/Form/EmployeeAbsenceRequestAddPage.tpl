{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
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
  <table class="" style="width: auto; border: medium none ! important;">
    <tr>
      <td>Employee </td>
      <td colspan="2">{$emp_name}</td>
    </tr>
    <tr>
      <td>Position </td> 
      <td colspan="2">{$emp_position}</td>
    </tr>
    <tr>
      <td>Absence Type </td> 
      <td colspan="2">{$absenceType}</td> 
    </tr>
    <tr class="crm-event-manage-eventinfo-form-block-start_date">
      <td class="label">Dates</td>
      <td>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</td>
      <td>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
    </tr>
  </table>
  <table id="tblabsence" class="report">
    <tbody>
      <tr>
        <td>Date</td>
	<td>Absence</td>
      </tr>
    </tbody>
  </table>
</div>
<div id='customData'>{include file="CRM/Custom/Form/CustomData.tpl"}</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>

{include file="CRM/common/customData.tpl" location="bottom"}
{literal}
  <script type="text/javascript">
    cj(function(cj) {

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
    if( start_date && end_date ){
      cj("#tblabsence").show();
    }
    var d = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
    cj('table#tblabsence tbody tr.trabsence').remove();
    var selectedVal = [];
    for (var x = 0; x <= d; x++) {
      var createSelectBox = '<tr class="trabsence"><td><label id="label_'+x+'" >'+start_date+'</label></td><td><select id="options_'+x+'" class="form-select"><option value=""></option><option value="0.5">Half Day</option><option value="1">Full Day</option></select></td></tr>';
      cj('form#EmployeeAbsenceRequestPage table#tblabsence tbody').append(createSelectBox);
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
        var param = cj.parseJSON('{}');
        CRM.api('Activity', 'get', {'sequential': 1, 'source_record_id': upActivityId, 'option_sort': 'activity_date_time ASC'},
          {success: function(data) {
            cj.each(data.values, function(key, value) {
            var val = value.activity_date_time;
            param[val]=value.duration;
          });   
	  var x=0;  
	  cj.each(param, function(key, value) {
            var createSelectBox = '<tr class="trabsence"><td><label id="label_'+x+'" >'+key+'</label></td><td><select id="options_'+x+'" class="form-select"><option value=""></option><option value="0.5">Half Day</option><option value="1">Full Day</option></select></td></tr>';
            cj('form#EmployeeAbsenceRequestPage table#tblabsence tbody').append(createSelectBox);
            if(value==240) {
	      cj("#options_"+x).val('0.5');
	    } 
	    else {
	      cj("#options_"+x).val('1');
	    }
	    x = new Number(x) + 1;
 	  });
        }
      });

      cj("#_qf_EmployeeAbsenceRequestPage_submit-bottom").click(function(event){
        for (var x = 0; x <= difDate; x++) {
          var selDate = cj('#label_'+x).text();
	  var selectopt = cj('#options_'+x+' :selected').text();
	  if(selectopt == "Full Day"){
	    CRM.api('Activity', 'create', {'sequential': 1, 'activity_type_id': 'Absence', 'source_contact_id': uID, 'activity_date_time': selDate, 'duration': 480, 'source_record_id': upActivityId},{success: function(data) {} });
	  }
	  else {
            if(selectopt == "Half Day"){
              CRM.api('Activity', 'create', {'sequential': 1, 'activity_type_id': 'Absence', 'source_contact_id': uID, 'activity_date_time': selDate, 'duration': 240, 'source_record_id': upActivityId}, {success: function(data) {} });
            }
          } 
        }
	alert("Your absences have been updated.");
	event.preventDefault();
});

{/literal}{/if}{literal}
{/literal}{if $action eq 1}{literal}
  cj("#tblabsence").hide();
    var dateValues = [];
  cj("#_qf_EmployeeAbsenceRequestPage_submit-bottom").click(function(event){
    var params = cj.parseJSON('{"sequential": "1"}');
    var end_date = cj('#end_date_display').val();
    var start_date = cj('#start_date_display').val();
    var diDate = Math.floor(( Date.parse(end_date) - Date.parse(start_date) ) / 86400000);
      for (var x = 0; x <= diDate; x++) {
        var selDate = cj('#label_'+x).text();
        var selectopt = cj('#options_'+x+' :selected').text();
        if(selectopt == "Full Day"){
          dateValues[x] = selDate +":" + "480";
        }
        else {
          if(selectopt == "Half Day"){
          dateValues[x] = selDate +":" + "240";
          }
        }
      }
    cj("#date_values").val(dateValues.join('|'));
  });
{/literal}{/if}{literal}
});
</script>
{/literal}
