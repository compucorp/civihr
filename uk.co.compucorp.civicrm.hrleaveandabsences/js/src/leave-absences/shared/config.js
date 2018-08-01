/* eslint-env amd */

(function (require) {
  define(function () {
    // This require.config will picked up by the r.js optimizer
    require.config({
      paths: {
        'mocks': '../test/mocks'
      }
    });

    // This require.config will be used by the "live" RequireJS (with debug ON)
    require.config({
      paths: {
        'mocks': CRM.vars.leaveAndAbsences.baseURL + '/js/test/mocks'
      }
    });
  });
})(require);
