var gulp = require('gulp');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var exec = require('child_process').exec;

gulp.task('sass', function () {
  gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(gulp.dest('css/'));
});

gulp.task('requirejs-bundle', function () {
    exec('r.js -o js/build.js', function (_, stdout, stderr) {
        console.log(stdout);
        console.log(stderr);
    });
});

gulp.task('watch', function () {
    gulp.watch('scss/**/*.scss', ['sass']);
    gulp.watch('js/src/**/*.js', ['requirejs-bundle']);
});

gulp.task('default', ['requirejs-bundle', 'sass', 'watch']);;
