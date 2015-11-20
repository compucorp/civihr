describe('Unit: CustomDateFilter', function() {

    var Filter;

    beforeEach(module('angular-date'));

    beforeEach(inject(function(CustomDateFilter) {
        Filter = CustomDateFilter;
    }));

    it('Should be defined.', function() {
        expect(Filter).toBeDefined();
    });

    it('Should not modify good date.', function() {
        expect(Filter('12/11/2013')).toEqual('12/11/2013');
    });

    it('Should convert Date object.', function() {
        expect(Filter(new Date(2013, 10, 12))).toEqual('12/11/2013');
    });

    it('Should convert other formats.', function() {
        expect(Filter('12-11-2013')).toEqual('12/11/2013');
        expect(Filter('2013-11-12')).toEqual('12/11/2013');
        expect(Filter(1448031278000)).toEqual('20/11/2015');
    });

    it('Invalid date Should return null.', function() {
        expect(Filter(undefined)).toEqual(null);
    });

    it('Empty date Should return empty string.', function() {
        expect(Filter('')).toEqual('');
    });

    it('Invalid string should not be modified.', function() {
        expect(Filter('testString')).toEqual('testString');
    });

});