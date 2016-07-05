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
      'Managers': 'fa fa-sitemap',
      'Absences': 'fa fa-calendar-times-o',
      'Bank Details': 'fa fa-university',
      'Identification': 'fa fa-credit-card',
      'Immigration': 'fa fa-plane',
      'Career History': 'fa fa-history',
      'Medical & Disability': 'fa fa-wheelchair',
      'Qualifications': 'fa fa-certificate',
    };

    for (var iconTabName in iconsMap) {
      $('.crm-contact-tabs-list li a[title="' + iconTabName + '"]').prepend('<span class="' + iconsMap[iconTabName] + '" />');
    }
  }

  setTabsIcon();
}(CRM.$, CRM._));
