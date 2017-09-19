/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/angular',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/angularBootstrap'
], function (_, angularMocks, angular, $uibModalDecorator, $document) {
  'use strict';

  describe('$uibModal.open', function () {
    var $uibModal, $provide, $document;

    beforeEach(module('ui.bootstrap'));

    beforeEach(function () {
      module(function (_$provide_) {
        $provide = _$provide_;
      });
      inject(function () {
        $provide.decorator('$uibModal', $uibModalDecorator);
      });
    });

    beforeEach(inject(function (_$uibModal_, _$document_) {
      $uibModal = _$uibModal_;
      $document = _$document_;
    }));

    describe('init', function () {
      var arg1 = { template: '<component></component>' };
      var arg2 = 'another_argument';
      var elements = {};
      var doc, originalStyles;

      beforeEach(function () {
        doc = $document[0];
        elements.body = doc.body;
        elements.html = doc.getElementsByTagName('html')[0];

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
          spyOn($uibModal, 'open').and.callThrough();
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
            // @TODO this doesn't simply work...
            modalInstance.dismiss();
          });

          ['body', 'html'].forEach(function (element) {
            ['width', 'height', 'overflow'].forEach(function (style) {
              it('sets original ' + style + ' for <' + element + '>', function () {
                expect(elements[element].style[style]).toBe(originalStyles[element][style]);
              });
            });
          });
        });
      });
    });
  });
});
