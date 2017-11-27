/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/directives/help-text.directive'
], function (angular) {
  describe('Help Text directive', function () {
    var $compile, $scope, element, helpText, helpTitle, notificationService;

    beforeEach(module('common.directives', function ($provide) {
      notificationService = jasmine.createSpyObj('notificationService', ['info']);
      $provide.value('notificationService', notificationService);
    }));

    beforeEach(inject(function ($rootScope, _$compile_) {
      $scope = $rootScope.$new();
      $compile = _$compile_;
    }));

    describe('Displaying the help text', function () {
      beforeEach(function () {
        element = angular.element('<help-text title="Help Me"><p>Help text</p></help-text>');

        $compile(element)($scope);

        helpText = element.find('.help-text').html();
        helpTitle = element.attr('title');

        element.find('button').click();
      });

      it('displays the help text when the button is clicked', function () {
        expect(notificationService.info).toHaveBeenCalledWith(helpTitle, helpText);
      });
    });

    describe('When no title is provided', function () {
      var defaultTitle = 'Help';

      beforeEach(function () {
        element = angular.element('<help-text><p>Help text</p></help-text>');

        $compile(element)($scope);

        helpText = element.find('.help-text').html();

        element.find('button').click();
      });

      it('displays the help text when the button is clicked', function () {
        expect(notificationService.info).toHaveBeenCalledWith(defaultTitle, helpText);
      });
    });
  });
});
