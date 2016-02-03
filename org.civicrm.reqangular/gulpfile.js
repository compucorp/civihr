var gulp = require('gulp');
var exec = require('child_process').exec;
var replace = require('gulp-replace');
var templateCache = require('gulp-angular-templatecache');


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
    gulp.watch('src/**/*.js', ['requirejs-bundle']);
    gulp.watch('src/common/templates/**/*.html', ['cache-templates', 'requirejs-bundle']);
});

gulp.task('default', ['cache-templates', 'requirejs-bundle', 'watch']);
