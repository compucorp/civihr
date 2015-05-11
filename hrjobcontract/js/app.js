define(['controllers/controllers', 'directives/directives', 'filters/filters', 'services/services'], function(){
    return angular.module('hrjc',['ngAnimate','ngRoute','ngResource','ui.bootstrap','angularFileUpload','hrjc.controllers','hrjc.directives','hrjc.filters','hrjc.services']);
});