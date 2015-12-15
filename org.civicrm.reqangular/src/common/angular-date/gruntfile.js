module.exports = function (grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        html2js:{
            main: {
                src: ['src/directives/*.html'],
                dest: 'src/directives/templates.js'
            }
        },
        browserify: {
            dist: {
                files: {
                    'dist/angular-date.js': ['main.js']
                },
                options: {
                    plugin: [
                        [ "browserify-derequire" ]
                    ]
                }
            }
        },
        uglify: {
            options: {
                banner: '/*\n <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> \n*/\n'
            },
            build: {
                files: {
                    'dist/angular-date.min.js': 'dist/angular-date.js'
                }
            }
        },
        watch: {
            scripts: {
                files: ['src/**/*.js', 'src/**/*.html', 'main.js'], tasks: ['html2js', 'browserify', 'test']
            },
            options: {
                atBegin: true
            }
        },
        karma: {
            unit: {
                options: {
                    frameworks: ['jasmine', 'commonjs'],
                    singleRun: true,
                    browsers: ['PhantomJS'],
                    files: [
                        'node_modules/angular/angular.js',
                        'dist/angular-date.js',
                        'node_modules/angular-mocks/angular-mocks.js',
                        'src/**/*Test.js'
                    ],
                    reporters: ['progress', 'coverage'],
                    preprocessors: {
                        'dist/angular-date.js': ['commonjs', 'coverage']
                    },
                    coverageReporter: {
                        type : 'html',
                        dir : 'coverage/'
                    }
                }
            }
        },
        jshint: {
            all: ['gruntfile.js', 'src/**/*.js']
        }
    });


    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-browserify');
    grunt.loadNpmTasks('grunt-html2js');

    // Unit Test
    grunt.loadNpmTasks('grunt-karma');

    grunt.registerTask('test', [
        'jshint',
        'karma'
    ]);


    // Custom Tasks
    grunt.registerTask('build_js', ['browserify', 'uglify']);

    grunt.registerTask('build', ['build_js', 'test']);

};
