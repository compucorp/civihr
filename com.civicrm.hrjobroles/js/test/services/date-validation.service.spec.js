/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/job-role.data',
  'common/angularMocks',
  'job-roles/modules/job-roles.module'
], function (angular, Mock) {
  'use strict';

  describe('Date Validation Service:', function () {
    var dateValidation;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (_dateValidation_) {
      dateValidation = _dateValidation_;
      dateValidation.dateFormats.push('DD/MM/YYYY');
    }));

    it('should be defined', function () {
      expect(dateValidation).toBeDefined();
    });

    it('Custom function can be assigned to _error()', function () {
      function customError (errorMsg, fields) {
      }

      expect(function () {
        dateValidation.setErrorCallback(customError);
      }).not.toThrow();

      expect(function () {
        dateValidation.setErrorCallback();
      }).toThrow();

      expect(function () {
        dateValidation.setErrorCallback(3);
      }).toThrow();

      expect(function () {
        dateValidation.setErrorCallback('dfsdf');
      }).toThrow();

      expect(function () {
        dateValidation.setErrorCallback({});
      }).toThrow();

      expect(function () {
        dateValidation.setErrorCallback(true);
      }).toThrow();
    });

    it('Check if Format is valid', function () {
            /* Should pass */
      expect(function () {
        dateValidation.validate('12/11/2009', '10/01/2013');
      }).not.toThrow();

      expect(function () {
        dateValidation.validate(new Date(2009, 3, 4), new Date(2013, 6, 10));
      }).not.toThrow();

      expect(function () {
        dateValidation.validate('1/1/112013', '10/01/2009');
      }).toThrow();

      expect(function () {
        dateValidation.validate('testString', '10/01/2009');
      }).toThrow();

      expect(function () {
        dateValidation.validate('12/11/2013', '123/01/2009');
      }).toThrow();

      expect(function () {
        dateValidation.validate('aa/bb/ccss', 'dd/MM/yyyy');
      }).toThrow();

      expect(function () {
        dateValidation.validate('12/11/2009', '10/-1/2012');
      }).toThrow();

      expect(function () {
        dateValidation.validate('12/11/2009', '10/91/2012');
      }).toThrow();

      expect(function () {
        dateValidation.validate('12/11/2009', '90/11/2012');
      }).toThrow();

      expect(function () {
        dateValidation.validate('12/111/013', '10/01/2009');
      }).toThrow();

      expect(function () {
        dateValidation.validate('12/11/2013', '1001/20/09');
      }).toThrow();

      expect(function () {
        dateValidation.validate('1/11/2009', '10/16/2012');
      }).toThrow();
    });

    it('Start date cannot be higher', function () {
            /* Should pass */
      expect(function () {
        dateValidation.validate('12/11/2003', '10/01/2009');
      }).not.toThrow();

            /*  Should NOT pass. Start date cannot be higher */
      expect(function () {
        dateValidation.validate('12/11/2009', '10/01/2003');
      }).toThrow();
    });

    it('Start date cannot be higher - recursive check', function () {
            /* Should pass */
      expect(function () {
        dateValidation.validate('12/11/2003', '13/11/2003');
      }).not.toThrow();

      expect(function () {
        dateValidation.validate('16/11/2003', '13/11/2003');
      }).toThrow();

      expect(function () {
        dateValidation.validate('13/11/2003', '13/11/2003');
      }).toThrow();
    });

    it('Function will break even if wont throw an exception', function () {
      expect(function () {
        dateValidation.validate('14/11/2003', '13/11/2003');
      }).toThrow();
    });

    it('Can run validate while only start date is entered', function () {
      expect(function () {
        dateValidation.validate('02/11/2013');
      }).not.toThrow();

            /* Cannot run validate while no dates are entered */
      expect(function () {
        dateValidation.validate('', '02/11/2017');
      }).toThrow();

      expect(function () {
        dateValidation.validate(null, '02/11/2017');
      }).toThrow();

      expect(function () {
        dateValidation.validate(true);
      }).toThrow();

      expect(function () {
        dateValidation.validate({});
      }).toThrow();

      expect(function () {
        dateValidation.validate(dateValidation);
      }).toThrow();
    });

    it('Invalid values', function () {
            /* valid date */
      expect(function () {
        dateValidation.validate('20/03/2013', 'ttestowystrring');
      }).toThrow();

            /* invalid dates */
      expect(function () {
        dateValidation.validate('testteest', '02/11/2017');
      }).toThrow();

      expect(function () {
        dateValidation.validate('sgdlskgs', 'sdgskjdgsdkg');
      }).toThrow();
    });

    it('Start date cannot be higher than contract start date', function () {
      expect(function () {
        dateValidation.validate('25/04/2016', '30/04/2016', '26/04/2016', '30/06/2016');
      }).toThrow();

      expect(function () {
        dateValidation.validate('26/04/2016', '30/04/2016', '26/04/2016', '30/06/2016');
      }).not.toThrow();

      expect(function () {
        dateValidation.validate('26/04/2016', '30/04/2016', '26/04/2016', '30/06/2016');
      }).not.toThrow();
    });

    it('End date cannot be higher than contract end date', function () {
      expect(function () {
        dateValidation.validate('30/06/2016', '01/07/2016', '26/04/2016', '30/06/2016');
      }).toThrow();

      expect(function () {
        dateValidation.validate('30/06/2016', '20/06/2016', '26/04/2016', '30/06/2016');
      }).toThrow();
    });
  });
});
