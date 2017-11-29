(function () {
  'use strict';

  var extPath = CRM.vars.contactAccessRights.baseURL + '/js/src/access-rights';
  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'access-rights': extPath
    }
  });

  require([
    'access-rights/modules/access-rights.module',
    'dom-initialization'
  ], function (angular, domInitialization) {
    angular.bootstrap(domInitialization.addAppToDOM(), ['access-rights']);
  });

})(require);
