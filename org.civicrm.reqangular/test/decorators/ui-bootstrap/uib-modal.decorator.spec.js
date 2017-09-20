/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/angularMocks',
  'common/angular',
  'common/angularBootstrap'
], function (_, $uibModalDecorator) {
  'use strict';

  describe('$uibModal.open', function () {
    var $uibModal, $document;

    beforeEach(module('ui.bootstrap'));

    beforeEach(module(function ($provide) {
      $provide.decorator('$uibModal', $uibModalDecorator);
    }));

    beforeEach(inject(function (_$document_, _$uibModal_) {
      $document = _$document_;
      $uibModal = _$uibModal_;
    }));

    describe('init', function () {
      var originalStyles;
      var arg1 = { template: '<component></component>' };
      var arg2 = 'another_argument';
      var elements = {};

      beforeEach(function () {
        elements.body = $document[0].body;
        elements.html = $document[0].getElementsByTagName('html')[0];

        if (!originalStyles) {
          originalStyles = {
            body: _.clone(elements.body.style),
            html: _.clone(elements.html.style)
          };
        }
      });

      describe('when modal is opened', function () {
        var modalInstance;

        beforeEach(function () {
          modalInstance = $uibModal.open(arg1, arg2);
        });

        ['body', 'html'].forEach(function (element) {
          _.forOwn({ overflow: 'hidden', height: '100%', width: '100%' }, function (value, style) {
            it('sets ' + style + ' to ' + value + ' for <' + element + '>', function () {
              expect(elements[element].style[style]).toBe(value);
            });
          });
        });

        describe('when modal is closed', function () {
          beforeEach(function () {
            // @TODO The solution to simply close the modal was not found and
            // the test cannot be run at the moment. If you have any idea
            // on how to make this test work, please amend it and turn on.
            modalInstance.close();
          });

          ['body', 'html'].forEach(function (element) {
            ['width', 'height', 'overflow'].forEach(function (style) {
              xit('sets original ' + style + ' for <' + element + '>', function () {
                expect(elements[element].style[style]).toBe(originalStyles[element][style]);
              });
            });
          });
        });
      });
    });
  });
});
