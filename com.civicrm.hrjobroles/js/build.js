// ({
//     baseUrl : "js",
//     name: "hrjobroles-main",
//     out: "dist/hrjobroles-main.js",
//     removeCombined: true,
//     paths: {
//         angularEditable: 'vendor/angular/xeditable.min',
//         angularFilter: 'vendor/angular/angular-filter.min',
//         requireLib:  'empty:'
//     },
//     deps: [
//         'app',
//         'controllers/HRJobRolesController',
//         'services/HRJobRolesService',
//         'directives/example',
//         'requireLib'
//     ],
//     wrap: true
// })

({
    baseUrl : 'src',
    out: 'dist/contact-summary.min.js',
    name: 'job-roles',
    skipModuleInsertion: true,
    paths: {
        'common': 'empty:',
        'job-roles/vendor/angular-editable': 'job-roles/vendor/angular/xeditable.min',
        'job-roles/vendor/angular-filter': 'job-roles/vendor/angular/angular-filter.min'
    }
})
