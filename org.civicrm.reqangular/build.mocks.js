({
    baseUrl : 'test',
    out: 'dist/reqangular.mocks.min.js',
    uglify: {
        no_mangle: true,
        max_line_length: 1000
    },
    paths: {
        'common': 'empty:',
        'common/mocks': 'mocks/'
    },
    include: [
        'common/mocks/services/api/appraisal-cycle-mock'
    ]
})
