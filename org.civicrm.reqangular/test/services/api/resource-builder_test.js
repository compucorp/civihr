define([
  'common/angular',
  'common/angularMocks',
  'common/services/api/resource-builder'
], function () {
  'use strict';

  describe('resourceBuilder', function () {
    var resourceBuilder, $httpBackend;

    beforeEach(module('common.apis'));
    beforeEach(inject(['resourceBuilder', '$httpBackend',
      function (_resourceBuilder_, _$httpBackend_) {
        resourceBuilder = _resourceBuilder_;
        $httpBackend = _$httpBackend_;
      }
    ]));

    describe('the built angular resource', function () {
      var resource;

      beforeEach(function () {
        var dataTransformations = {
          toApi: function (data) {
            data.additionalCustomParameter = 'SENDING ADDITIONAL PARAM TO API!';
            return data;
          },
          fromApi: function (values) {
            return values.map(function (i) {
              i.customProp = i.label;
              return i;
            });
          }
        };
        var entityPrototype = {
          entityProperty: 'customPropertyValue'
        };
        resource = resourceBuilder.build('theEntityName', {
          'customParam': 'testing'
        }, dataTransformations, entityPrototype);
      });

      afterEach(function () {
        $httpBackend.verifyNoOutstandingRequest();
      });

      describe('the "get" action', function () {
        var mockedResult = {
          "is_error": 0,
          "version": 3,
          "count": 2,
          "values": [{
            "id": "12",
            "contact_id": "158",
            "entity_type": "hrjc_region",
            "entity_id": "1046",
            "label": "Region 1"
          }, {
            "id": "36",
            "contact_id": "158",
            "entity_type": "hrjc_region",
            "entity_id": "1051",
            "label": "test"
          }]
        };

        it('queries the correct URL', function () {
          $httpBackend.expectGET('/civicrm/ajax/rest?customParam=testing&entity=theEntityName&json=%7B%22sequential%22:1%7D').respond(mockedResult);
          resource.getAll();
          $httpBackend.flush();
        });

        it('transforms the response', function (done) {
          $httpBackend.expectGET('/civicrm/ajax/rest?customParam=testing&entity=theEntityName&json=%7B%22sequential%22:1%7D').respond(mockedResult);
          resource.getAll().$promise.then(function (data) {
            data.forEach(function (i) {
              expect(i.customProp).toEqual(i.label);
            });
            done();
          });
          $httpBackend.flush();
        });

        it('returns the correct data', function (done) {
          $httpBackend.expectGET('/civicrm/ajax/rest?customParam=testing&entity=theEntityName&json=%7B%22sequential%22:1%7D').respond(mockedResult);
          resource.getAll().$promise.then(function (data) {
            expect(JSON.stringify(data)).toEqual('[{"id":"12","contact_id":"158","entity_type":"hrjc_region","entity_id":"1046","label":"Region 1","customProp":"Region 1"},{"id":"36","contact_id":"158","entity_type":"hrjc_region","entity_id":"1051","label":"test","customProp":"test"}]');
            done();
          });
          $httpBackend.flush();
        });

        it('returns the data with the given prototype', function (done) {
          $httpBackend.expectGET('/civicrm/ajax/rest?customParam=testing&entity=theEntityName&json=%7B%22sequential%22:1%7D').respond(mockedResult);
          resource.getAll().$promise.then(function (data) {
            expect(data[0].entityProperty).toBe('customPropertyValue');
            done();
          });
          $httpBackend.flush();
        });
      });

      describe('the "save" action', function () {
        it('saves data using the correct URL, sending the correct request body', function () {
          $httpBackend.expectPOST('/civicrm/ajax/rest?action=create&customParam=testing&entity=theEntityName&json=%7B%22sequential%22:1%7D',
            'additionalCustomParameter=SENDING+ADDITIONAL+PARAM+TO+API!&prop1=aaa&prop2=bbb').respond(200, 'success');
          resource.save(null, {
            prop1: 'aaa',
            prop2: 'bbb'
          });
          $httpBackend.flush();
        });
      });

      describe('the "remove" action', function () {
        it('removes data using the correct URL, sending the correct request body', function () {
          $httpBackend.expectPOST('/civicrm/ajax/rest?action=delete&customParam=testing&entity=theEntityName&json=%7B%22sequential%22:1%7D',
            'additionalCustomParameter=SENDING+ADDITIONAL+PARAM+TO+API!&id=123').respond(200, 'success');
          resource.remove(null, {
            id: 123
          });
          $httpBackend.flush();
        });
      });
    });
  });
});
