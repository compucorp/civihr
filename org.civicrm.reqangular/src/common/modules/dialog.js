define([
    'common/angular',
    'common/services/dialog/dialog',
    'common/controllers/dialog/dialog-ctrl',
    'common/angularBootstrap',
    'common/directives/loading',
    'common/modules/templates',
], function (angular, dialog, DialogCtrl) {
    'use strict';

    return angular
      .module('common.dialog', ['ui.bootstrap', 'common.directives', 'common.templates'])
      .factory('dialog', dialog)
      .controller('DialogCtrl', DialogCtrl);
});
