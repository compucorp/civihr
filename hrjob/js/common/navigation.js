// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('Common.Navigation', function(Navigation, HRApp, Backbone, Marionette, $, _) {
  /**
   * Navigate to a major/new screen.
   *
   * @param string route The fragment to append to the URL
   * @param Object options:
   *   - warnTitle: string
   *   - warnMessages: array of string
   *   - success: function(route, options) -- Callback if navigation is allowed
   *   - [TODO] cancel: function(route, options) --  Callback if navigation is cancelled
   *
   * Events:
   *  - navigate:warnings: function(route, options) -- Allow other components
   *    to display warnings before navigation occurs. Warnings should be
   *    added to options.warnMessages.
   *  - navigate: function(route, options) -- Allow other components to
   *    update based on the navigation
   */
  Navigation.navigate = function(route, options) {
    options || (options = {});
    _.defaults(options, {
      warnTitle: ts('Confirm Action'),
      warnMessages: []
    });
    HRApp.trigger('navigate:warnings', route, options);

    var doNavigate = function() {
      Backbone.history.navigate(route, options);
      HRApp.trigger('navigate', route, options);
      if (options.success) options.success(route, options);
    };

    if (options.warnMessages.length == 0) {
      doNavigate();
    } else {
      CRM.confirm({
        title: options.warnTitle,
        message: options.warnMessages.join(' ')
      })
        .on('crmConfirm:yes', doNavigate)
        .on('crmConfirm:no', function() {
          if (options.cancel) options.cancel(route, options);
        });
    }
  };

  Navigation.getCurrentRoute = function() {
    return Backbone.history.fragment;
  };

  Navigation.onRoute = function(router, route, params) {
    HRApp.trigger('navigate', Navigation.getCurrentRoute(), {});
  };

  HRApp.on("initialize:after", function() {
    Backbone.history.on('route', Navigation.onRoute);

    window.onbeforeunload = _.wrap(window.onbeforeunload, function(onbeforeunload) {
      var options = {
        warnTitle: ts('Confirm Action'),
        warnMessages: []
      };
      HRApp.trigger('navigate:warnings', null, options);
      if (options.warnMessages.length > 0) {
        return options.warnMessages.join(' ');
      } else if (onbeforeunload) {
        return onbeforeunload.apply(this, arguments);
      }
    });
  });
});
