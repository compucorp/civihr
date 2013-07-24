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

function renderSelectBox(oGroups, nameID, selectedVal, select, nameDefault) {
  if (oGroups[selectedVal]) {
    select.find('option').remove().end();
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
 