var gulp = require('gulp');
var exec = require('child_process').exec;

gulp.task('requirejs-bundle', function (done) {
    exec('r.js -o build.js', function (_, stdout, stderr) {
        done();
    });
});

gulp.task('watch', function () {
    gulp.watch('src/**/*.js', ['requirejs-bundle']);
});

gulp.task('default', ['requirejs-bundle', 'watch']);
