define(['angularEditable', 'angularFilter','controllers/controllers', 'directives/directives', 'filters/filters', 'services/services'], function(){
    return angular.module('hrjobroles',
        [
            'ngAnimate',
            'angular-date',
            'ngRoute',
            'xeditable',
            'angular.filter',
            'ngResource',
            'ui.bootstrap',
            'hrjobroles.controllers',
            'hrjobroles.directives',
            'hrjobroles.filters',
            'hrjobroles.services'
        ]
    );
});