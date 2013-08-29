cj(document).ajaxSuccess(function() {
  var categoryID = CRM.hrqual.category;
  var nameID = CRM.hrqual.name;
  var oGroups = CRM.hrqual.optionGroups;
  var select = cj('#category_name');
  var categoryDefault = cj('#custom_' + categoryID).val();
  var nameDefault = cj('#custom_' + nameID).val();
  renderSelectBox(oGroups, nameID, categoryDefault, select, nameDefault)

  cj('.crm-profile-name-hrqual_tab #custom_' + categoryID).change(function() {
    var selectedVal = cj(this).val();
    renderSelectBox(oGroups, nameID, selectedVal, select);
  });
});

/**
* This function is called to render a select box in
* place of a customField and to populate its options tags
* with the relevent optionGroup values. Its also used to
* assign default value to the rendered select.
*
*
* @param oGroups       list of optionGroups and its values.
* @param nameID        ID of the customField on which the select is to be rendered.
* @param selectedVal   the value(OptionGroup name) of the selectBox which will help the change 
*                      event to populate the relevant OptionGroup values in the rendered selectbox options.
* @param select        the select field ID. eg. cj('#fieldID');
* @param nameDefault   the default value to be assigned to the rendered select in EDIT mode
*/
function renderSelectBox(oGroups, nameID, selectedVal, select, nameDefault) {
  if (oGroups[selectedVal]) {
    select.find('option').remove().end().append(cj('<option></option>').val("").html("-select-"));
    for (var i = 0; i < oGroups[selectedVal].length; i++) {
      select.append(cj('<option></option>').val(oGroups[selectedVal][i]).html(oGroups[selectedVal][i]));
    }
  }
  else {
    select.find('option').remove().end().append(cj('<option></option>').val("").html("-select-"));
  }
  select.removeAttr('name').attr('name', 'custom_' + nameID).removeAttr('style');
  cj("#custom_" + nameID).replaceWith(select);

  //assign the defaults to the "name" field in the Edit mode.
  if (nameDefault) {
    select.val(nameDefault);
  }
}

cj(document).ready(function($) {
    //hrqual: hide/display fields based on "Certification Acquired"
    cj('input[type=radio]').live('change', function() {
	var nameOfCertificationId = cj("label:contains('Name of Certification')").attr("for");
	var cetificationAuthorityId = cj("label:contains('Certification Authority')").attr("for");
	var gradeAchievedId = cj("label:contains('Grade Achieved')").attr("for");
	var dateOfAttainmentId = cj("label:contains('Date of Attainment')").attr("for");
	var dateOfExpiration = cj("label:contains('Date of Expiration')").attr("for");
	if (cj(this).val()==0) {
	    cj("div#editrow-"+nameOfCertificationId).hide();
	    cj("div#editrow-"+cetificationAuthorityId).hide();
	    cj("div#editrow-"+gradeAchievedId).hide();
	    cj("div#editrow-"+dateOfAttainmentId).hide();
	    cj("div#editrow-"+dateOfExpiration).hide();
	} else if(cj(this).val()==1) {
	    cj("div#editrow-"+nameOfCertificationId).show();
	    cj("div#editrow-"+cetificationAuthorityId).show();
	    cj("div#editrow-"+gradeAchievedId).show();
	    cj("div#editrow-"+dateOfAttainmentId).show();
	    cj("div#editrow-"+dateOfExpiration).show();
	}
    });
});
 