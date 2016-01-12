describe('Unit: DateValidationService', function () {

    var Service;

    beforeEach(module('angular-date'));

    beforeEach(inject(function (DateValidationService) {
        Service = DateValidationService;
    }));

    it('Should be defined.', function () {
        expect(Service).toBeDefined();
    });

    it('_error() should throw error if custom function is not appiled', function () {
        var shouldThrow = function () {
            Service._error('Test', ['1', '2']);
        };

        expect(shouldThrow).toThrow(new Error('Test'));
    });


    it('Custom function can be assigned to _error()', function () {
        function customError(error_msg, fields) {
        }

        Service.setErrorCallback(customError);

        expect(Service._error).toEqual(customError);

        expect(function () {
            Service.setErrorCallback();
        }).toThrow();

        expect(function () {
            Service.setErrorCallback(3);
        }).toThrow();

        expect(function () {
            Service.setErrorCallback('dfsdf');
        }).toThrow();

        expect(function () {
            Service.setErrorCallback({});
        }).toThrow();

        expect(function () {
            Service.setErrorCallback(true);
        }).toThrow();
    });


    it('Check if Format is valid', function () {
        /* Should pass */
        expect(function () {
            Service.validate('12/11/2009', '10/01/2013');
        }).not.toThrow();

        expect(function () {
            Service.validate(new Date(2009, 3, 4), new Date(2013, 6, 10));
        }).not.toThrow();

        expect(function () {
            Service.validate('1/1/112013', '10/01/2009');
        }).toThrow();

        expect(function () {
            Service.validate('testString', '10/01/2009');
        }).toThrow();

        expect(function () {
            Service.validate('12/11/2013', '123/01/2009');
        }).toThrow();

        expect(function () {
            Service.validate('aa/bb/ccss', 'dd/MM/yyyy');
        }).toThrow();

        expect(function () {
            Service.validate('12/11/2009', '10/-1/2012');
        }).toThrow();

        expect(function () {
            Service.validate('12/11/2009', '10/91/2012');
        }).toThrow();

        expect(function () {
            Service.validate('12/11/2009', '90/11/2012');
        }).toThrow();

        expect(function () {
            Service.validate('12/111/013', '10/01/2009');
        }).toThrow();

        expect(function () {
            Service.validate('12/11/2013', '1001/20/09');
        }).toThrow();

        expect(function () {
            Service.validate('1/11/2009', '10/16/2012');
        }).toThrow();
    });

    it('Start date cannot be higher', function () {
        /* Should pass */
        expect(function () {
            Service.validate('12/11/2003', '10/01/2009');
        }).not.toThrow();

        /*  Should NOT pass. Start date cannot be higher */
        expect(function () {
            Service.validate('12/11/2009', '10/01/2003');
        }).toThrow();
    });

    it('Start date cannot be higher - recursive check', function () {
        /* Should pass */
        expect(function () {
            Service.validate('12/11/2003', '13/11/2003');
        }).not.toThrow();

        expect(function () {
            Service.validate('16/11/2003', '13/11/2003');
        }).toThrow();

        expect(function () {
            Service.validate('13/11/2003', '13/11/2003');
        }).toThrow();
    });

    it('Function will break even if wont throw an exception', function () {
        expect(function () {
            Service.validate('14/11/2003', '13/11/2003');
        }).toThrow();
    });


    it('Can run validate while only start date is entered', function () {

        expect(function () {
            Service.validate('02/11/2013');
        }).not.toThrow();

        /* Cannot run validate while no dates are entered */
        expect(function () {
            Service.validate('', '02/11/2017');
        }).toThrow();

        expect(function () {
            Service.validate(null, '02/11/2017');
        }).toThrow();

        expect(function () {
            Service.validate(true);
        }).toThrow();

        expect(function () {
            Service.validate({});
        }).toThrow();

        expect(function () {
            Service.validate(Service);
        }).toThrow();
    });


    it('Max & Min Date', function () {

        Service.setMinDate('01/01/2000');
        Service.setMaxDate('31/12/2020');

        /* valid date */
        expect(function () {
            Service.validate('20/03/2013', '02/11/2017');
        }).not.toThrow();

        /* invalid dates */
        expect(function () {
            Service.validate('20/03/1992', '02/11/2017');
        }).toThrow();

        expect(function () {
            Service.validate('20/03/2006', '02/11/2037');
        }).toThrow();
    });

    it('Invalid values', function () {
        /* valid date */
        expect(function () {
            Service.validate('20/03/2013', 'ttestowystrring');
        }).toThrow();

        /* invalid dates */
        expect(function () {
            Service.validate('testteest', '02/11/2017');
        }).toThrow();

        expect(function () {
            Service.validate('sgdlskgs', 'sdgskjdgsdkg');
        }).toThrow();
    });
});