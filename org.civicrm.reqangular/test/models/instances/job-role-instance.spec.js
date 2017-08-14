/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/job-role-instance',
  'common/mocks/services/hr-settings-mock'
], function (_) {
  'use strict';

  describe('JobRoleInstance', function () {
    var $provide, JobRoleInstance, ModelInstance;

    beforeEach(function () {
      module('common.models.instances', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject(['HR_settingsMock', function (HRSettingsMock) {
        $provide.value('HR_settings', HRSettingsMock);
      }]);
    });
    beforeEach(inject(function (_JobRoleInstance_, _ModelInstance_) {
      JobRoleInstance = _JobRoleInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(JobRoleInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('init()', function () {
      var instance;

      describe('when initializing with data from the API', function () {
        var attributes = {
          foo: 'foo',
          bar: 'bar',
          start_date: '2014-04-14 00:00:00',
          end_date: '2015-04-14 00:00:00',
          'api.HRJobContract.getsingle': {
            id: '4',
            contact_id: '4',
            is_primary: '1'
          }
        };

        beforeEach(function () {
          instance = JobRoleInstance.init(attributes, true);
        });

        it('keeps the basic properties the same', function () {
          expect(instance.id).toEqual(attributes.id);
          expect(instance.title).toEqual(attributes.title);
        });

        it('converts the dates to the date format specified in HR_settings', function () {
          expect(instance.start_date).toBe('14/04/2014');
          expect(instance.end_date).toBe('14/04/2015');
        });

        it('takes the data from the api chain call property', function () {
          expect(instance.contact_id).toBeDefined();
          expect(instance.job_contract_id).toBeDefined();
          expect(instance.contact_id).toBe(attributes['api.HRJobContract.getsingle'].contact_id);
          expect(instance.job_contract_id).toBe(attributes['api.HRJobContract.getsingle'].id);
        });

        it('removes the api chain call property', function () {
          expect(instance['api.HRJobContract.getsingle']).not.toBeDefined();
        });
      });
    });

    describe('toAPI()', function () {
      var instance, toAPIData;

      beforeEach(function () {
        instance = JobRoleInstance.init({
          foo: 'foo',
          start_date: '14/04/2014',
          end_date: '14/04/2015',
          contact_id: '4',
          job_contract_id: '4'
        });
        toAPIData = instance.toAPI();
      });

      it('filters out the custom data field', function () {
        expect(Object.keys(toAPIData)).toEqual(_.without(
                    Object.keys(instance.attributes()),
                    'contact_id',
                    'job_contract_id'
                ));
      });

      it('formats the dates in the YYYY-MM-DD HH:ii:ss format', function () {
        expect(toAPIData.start_date).toBe('2014-04-14 00:00:00');
        expect(toAPIData.end_date).toBe('2015-04-14 00:00:00');
      });
    });
  });
});
