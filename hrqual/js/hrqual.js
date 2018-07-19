// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  $(document).ajaxSuccess(function () {
    var categoryID = CRM.hrqual.category;
    var nameID = CRM.hrqual.name;
    var oGroups = CRM.hrqual.optionGroups;
    var select = $('#category_name');
    var categoryDefault = $('#custom_' + categoryID).val();
    var nameDefault = $('#custom_' + nameID).val();
    renderSelectBox(oGroups, nameID, categoryDefault, select, nameDefault);

    $('.crm-profile-name-hrqual_tab #custom_' + categoryID).change(function () {
      var selectedVal = $(this).val();
      renderSelectBox(oGroups, nameID, selectedVal, select);
    });
    // hrqual: hide/display fields based on "Certification Acquired"
    if ($(this).find('div#profile-dialog').length) {
      if ($(this).find('div#profile-dialog').html().indexOf('crm-profile-name-hrqual_tab') > -1) {
        var elementNameCertificationAcquired = $('[data-crm-custom="Qualifications:Certification_Acquired_"]').attr('name');
        var elementValueCertificationAcquired = $('input:radio[name=' + elementNameCertificationAcquired + ']:checked').val();
        if (elementValueCertificationAcquired == 1) {
          showCertificationFields();
        } else {
          hideCertificationFields();
        }
        $(':radio[name="' + elementNameCertificationAcquired + '"]').change(function () {
          if ($(this).val() == 0) {
            hideCertificationFields();
          } else if ($(this).val() == 1) {
            showCertificationFields();
          }
        });
      }
    }
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
  * @param select        the select field ID. eg. $('#fieldID');
  * @param nameDefault   the default value to be assigned to the rendered select in EDIT mode
  */
  function renderSelectBox (oGroups, nameID, selectedVal, select, nameDefault) {
    if (oGroups[selectedVal]) {
      select.find('option').remove().end().append($('<option></option>').val('').html('-select-'));
      for (var i = 0; i < oGroups[selectedVal].length; i++) {
        select.append($('<option></option>').val(oGroups[selectedVal][i]).html(oGroups[selectedVal][i]));
      }
    } else {
      select.find('option').remove().end().append($('<option></option>').val('').html('-select-'));
    }
    select.removeAttr('name').attr('name', 'custom_' + nameID).removeAttr('style');
    $('#custom_' + nameID).replaceWith(select);

    // assign the defaults to the "name" field in the Edit mode.
    if (nameDefault) {
      select.val(nameDefault);
    }
  }

  function hideCertificationFields () {
    var nameOfCertificationId = $('[data-crm-custom="Qualifications:Name_of_Certification"]').attr('id');
    var cetificationAuthorityId = $('[data-crm-custom="Qualifications:Certification_Authority"]').attr('id');
    var gradeAchievedId = $('[data-crm-custom="Qualifications:Grade_Achieved"]').attr('id');
    var dateOfAttainmentId = $('[data-crm-custom="Qualifications:Attain_Date"]').attr('id');
    var dateOfExpiration = $('[data-crm-custom="Qualifications:Expiry_Date"]').attr('id');
    $('div#editrow-' + nameOfCertificationId).hide();
    $('div#editrow-' + cetificationAuthorityId).hide();
    $('div#editrow-' + gradeAchievedId).hide();
    $('div#editrow-' + dateOfAttainmentId).hide();
    $('div#editrow-' + dateOfExpiration).hide();
  }

  function showCertificationFields () {
    var nameOfCertificationId = $('[data-crm-custom="Qualifications:Name_of_Certification"]').attr('id');
    var cetificationAuthorityId = $('[data-crm-custom="Qualifications:Certification_Authority"]').attr('id');
    var gradeAchievedId = $('[data-crm-custom="Qualifications:Grade_Achieved"]').attr('id');
    var dateOfAttainmentId = $('[data-crm-custom="Qualifications:Attain_Date"]').attr('id');
    var dateOfExpiration = $('[data-crm-custom="Qualifications:Expiry_Date"]').attr('id');
    $('div#editrow-' + nameOfCertificationId).show();
    $('div#editrow-' + cetificationAuthorityId).show();
    $('div#editrow-' + gradeAchievedId).show();
    $('div#editrow-' + dateOfAttainmentId).show();
    $('div#editrow-' + dateOfExpiration).show();
  }
}(CRM.$, CRM._));
