(function ($, _) {
  /**
   * Add an icon in each tab item.
   */
  function setTabsIcon() {
    var iconsMap = {
      'Job Contract': 'fa fa-file-o',
      'Job Roles': 'fa fa-list-alt',
      'Notes': 'fa fa-pencil',
      'Emergency Contacts': 'fa fa-medkit',
      'Change Log': 'fa fa-archive',
      'Groups': 'fa fa-group',
      'Cases': 'fa fa-bomb',
      'Managers': 'fa fa-bomb',
      'Absences': 'fa fa-bomb',
      'Identification': 'fa fa-bomb',
      'Immigration': 'fa fa-bomb',
      'Bank Details': 'fa fa-bomb',
      'Career History': 'fa fa-bomb',
      'Medical & Disability': 'fa fa-bomb',
      'Qualifications': 'fa fa-bomb',
    };

    for (var iconTabName in iconsMap) {
      $('.crm-contact-tabs-list li a[title="' + iconTabName + '"]').prepend('<span class="' + iconsMap[iconTabName] + '" />');
    }
  }

  setTabsIcon();
}(CRM.$, CRM._));
