var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('sass', function () {
  gulp.src('scss/*.scss')
    .pipe(sass({
        outputStyle: 'compressed',
        includePaths: ['vendor/']
    }).on('error', sass.logError))
    .pipe(gulp.dest('css/'));
});

gulp.task('default', ['sass']);;
