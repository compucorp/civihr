/**
* This script is responsible for some changes on 'Advanced Search / Add activity' page
*/

CRM.$(function() {
  'use strict';

  /**
   * Checks if the "org.civicrm.bootstrapcivicrm" is installed
   * @return {Promise} Resolves to a boolean
   */
  function isBootstrapcivicrmInstalled() {
    return CRM.api3('Extension', 'get', {
      'sequential': 1
    }).then(function(result) {
      return result.values.filter(function(extension) {
        return extension.key === 'org.civicrm.bootstrapcivicrm' && extension.status === 'installed';
      }).length > 0;
    });
  }

  /**
   * Replaces the "with contact" links with a multiselect "select2" dropdown
   */
  function addSelect2DropdownForWithContact() {
    var withContactSelect = document.createElement('select');
    withContactSelect.id = 'with-contact-select';
    withContactSelect.multiple = 'multiple';
    var crmFrozenField = document.querySelector('.crm-frozen-field');
    crmFrozenField.parentNode.insertBefore(withContactSelect, crmFrozenField);
    crmFrozenField.style.display = 'none';
    var targetContactId = document.querySelector('input[name="target_contact_id"]');
    targetContactId.value = '';

    Array.prototype.forEach.call(document.querySelectorAll('.crm-frozen-field a.view-contact'), function(el, idx) {
      var opt = document.createElement('option');
      opt.value = /.+cid=([0-9]+)/.exec(el.href)[1];
      opt.textContent = el.textContent;
      withContactSelect.appendChild(opt);
    });

    CRM.$(withContactSelect)
      .select2()
      .on('change', function() {
        targetContactId.value = CRM.$(this)
          .val()
          .join(',');
      });
  }

  isBootstrapcivicrmInstalled().then(function(installed) {
    if (installed) {
      addSelect2DropdownForWithContact();
    }
  });
});
