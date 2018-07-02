/* global dataLayer */

(function (window, CRM, dataLayer) {
  trackContactTabVirtualPageviews();

  /**
   * Sends a virtual pageview to Google Analytics whenever the user clicks on
   * any of the tabs of the Contact Summary page
   *
   * The path of the pageview is the Contact Summary page path + the selectedChild
   * param's value of the selected tab, thus simulating the user landing
   * directly on the tab
   */
  function trackContactTabVirtualPageviews () {
    var contactPagePath = window.location.pathname + '?reset=1&cid=' + CRM.contactId;

    CRM.$('#mainTabContainer').on('tabsactivate', function (event, ui) {
      var tabName = ui.newTab[0].id.replace('tab_', '');

      dataLayer.push({
        event: 'virtual-pageview',
        virtualPageviewPath: contactPagePath + '&selectedChild=' + tabName
      });
    });
  }
}(window, CRM, dataLayer));
