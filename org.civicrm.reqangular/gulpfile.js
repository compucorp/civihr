var gulp = require('gulp');
var exec = require('child_process').exec;
var replace = require('gulp-replace');
var templateCache = require('gulp-angular-templatecache');
var gulp = require('gulp');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var karma = require('karma');
var exec = require('child_process').exec;
var path = require('path');
var fs = require('fs');

gulp.task('cache-templates', function (cb) {
    gulp.src('src/common/templates/**/*.html')
        .pipe(templateCache({
            moduleSystem: 'RequireJS'
        }))
        .pipe(replace("['angular']", "['common/angular']"))
        .pipe(replace('module("templates")', 'module("common.templates", [])'))
        .pipe(gulp.dest('src/common/modules'));

    cb();
});

gulp.task('requirejs-bundle', function (done) {
    exec('r.js -o build.js', function (_, stdout, stderr) {
        done();
    });
});

gulp.task('watch', function () {
    gulp.watch('src/common/**/*.js', ['requirejs-bundle', 'test']);
    gulp.watch('src/common/templates/**/*.html', ['cache-templates', 'requirejs-bundle']);
    gulp.watch(['src/tests/**/*.js', '!src/tests/mocks/**/*.js', '!src/tests/test-main.js'], ['test']);
});

gulp.task('default', ['cache-templates', 'requirejs-bundle', 'watch']);

gulp.task('test', function (done) {
    test.all();
});

var test = (function () {

    /**
     * Runs the karma server which does a single run of the test/s
     *
     * @param {string} configFile - The full path to the karma config file
     * @param {Function} cb - The callback to call when the server closes
     */
    function runServer(configFile, cb) {
        new karma.Server({
            configFile: __dirname + '/src/tests/' + configFile,
            singleRun: true
        }, function () {
            cb && cb();
        }).start();
    }

    return {

        /**
         * Runs all the tests
         */
        all: function () {
            runServer('karma.conf.js');
        },

        /**
         * Runs the tests for a specific source file
         *
         * Looks for a test file (*_test.js) in `test/`, using the same path
         * of the source file in `src/appraisals/`
         *   i.e. src/appraisals/models/model.js -> test/models/model_test.js
         *
         * @throw {Error}
         */
        for: function (srcFile) {
            var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));
            var testFile = srcFile
                .replace('src/appraisals/', 'test/')
                .replace(srcFileNoExt + '.js', srcFileNoExt + '_test.js');

            try {
                var stats = fs.statSync(testFile);

                stats.isFile() && this.single(testFile);
            } catch (ex) {
                throw ex;
            }
        },

        /**
         * Runs a single test file
         *
         * It passes to the karma server a temporary config file
         * which is deleted once the test has been run
         *
         * @param {string} testFile - The full path of a test file
         */
        single: function (testFile) {
            var configFile = 'karma.' + path.basename(testFile, path.extname(testFile)) + '.conf.temp.js';

            gulp.src(__dirname + '/js/karma.conf.js')
                .pipe(replace('*_test.js', path.basename(testFile)))
                .pipe(rename(configFile))
                .pipe(gulp.dest(__dirname + '/js'))
                .on('end', function () {
                    runServer(configFile, function () {
                        gulp.src(__dirname + '/js/' + configFile, { read: false }).pipe(clean());
                    });
                });
        }
    };
})();
