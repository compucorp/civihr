(function () {
  var extPath = CRM.vars.contactAccessRights.baseURL + '/js/src/access-rights';
  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'access-rights': extPath
    }
  });

  require(['access-rights/app'], function (angular) {
    var actions = angular.element('.crm-actions-ribbon ul#actions');
    var app = angular.element('<li id="access-rights"><div id="bootstrap-theme"> \
        <div ng-controller="AccessRightsCtrl as $ctrl"> \
          <a href class="btn btn-secondary-outline text-uppercase pull-right" ng-click="$ctrl.openModal()"> \
            <i class="fa fa-wrench fa-fw"></i> Manage roles and teams \
          </a> \
        </div> \
      </div></li>');
    actions.append(app);
    angular.bootstrap(app, ['access-rights']);
  });
})(require);
