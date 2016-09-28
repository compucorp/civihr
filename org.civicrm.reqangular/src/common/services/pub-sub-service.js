define([
  'common/modules/services'
], function (module) {
  'use strict';

  var topics = Object.create(null);
  module.factory("pubSubService", ['$rootScope', function ($rootScope) {
    return {
      subscribe: function (topic, listener) {
        if (!topics[topic]) {
          topics[topic] = [];
        }
        listener.$rootScope = $rootScope;
        var index = topics[topic].push(listener) - 1;
        return {
          remove: function () {
            delete topics[topic][index];
          }
        };
      },
      publish: function (topic, info) {
        if (!topics[topic]) return;
        topics[topic].forEach(function (item) {
          item(info != undefined ? info : {});
          if (!item.$rootScope.$$phase)
            item.$rootScope.$apply();
        });
      }
    };
  }
  ]);
});
