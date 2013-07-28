CRM.HRApp = new Marionette.Application();

CRM.HRApp.addRegions({
  mainRegion: ".hrjob-main-region",
  treeRegion: ".hrjob-tree-region"
});

/**
 * Navigate to a major/new screen.
 *
 * @param string route The fragment to append to the URL
 * @param Object options:
 *   - success: function(route, options) -- Callback if navigation is allowed
 *   - [TODO] cancel: function(route, options) --  Callback if navigation is cancelled
 *
 * Events:
 *  - navigate:warnings: function(route, options) -- Allow other components
 *    to display warnings before navigation occurs. Warnings should be
 *    added to options.navWarnings.
 *  - navigate: function(route, options) -- Allow other components to
 *    update based on the navigation
 */
CRM.HRApp.navigate = function(route, options) {
  options || (options = {});
  _.defaults(options, {
    navWarnings: []
  });
  CRM.HRApp.trigger('navigate:warnings', route, options);

  var doNavigate = function() {
    Backbone.history.navigate(route, options);
    CRM.HRApp.trigger('navigate', route, options);
    if (options.success) options.success(route, options);
  };

  if (options.navWarnings.length == 0) {
    doNavigate();
  } else {
    var buttons = {};
    buttons[ts('Continue')] = doNavigate;
    buttons[ts('Cancel')] = function() {
      if (options.cancel) options.cancel(route, options);
    };
    CRM.confirm(buttons, {
      message: options.navWarnings.join(' ')
    });
  }
};

CRM.HRApp.getCurrentRoute = function() {
  return Backbone.history.fragment
};

CRM.HRApp.on("initialize:after", function() {
  if (Backbone.history) {
    Backbone.history.start();

    if (this.getCurrentRoute() === "") {
      CRM.HRApp.trigger("intro:show", CRM.jobTabApp.contact_id);
    }
  }
});

CRM.HRApp.on("ui:block", function(message) {
  // cj('.hrjob-container').block({
  //   message: message
  // });
  cj.blockUI({
    css: { top: '50px', left: '', right: '50px' },
    message: null // disregard: message
  });
});
CRM.HRApp.on("ui:unblock", function() {
  // cj('.hrjob-container').unblock();
  cj.unblockUI();
});