({
    baseUrl : "js",
    name: "hrjobroles-main",
    out: "dist/hrjobroles-main.js",
    removeCombined: true,
    paths: {
        angularEditable: 'vendor/angular/xeditable.min',
        angularFilter: 'vendor/angular/angular-filter.min',
        requireLib:  'empty:'
    },
    deps: [
        'app',
        'controllers/example',
        'services/example',
        'directives/example',
        'requireLib'
    ],
    wrap: true
})