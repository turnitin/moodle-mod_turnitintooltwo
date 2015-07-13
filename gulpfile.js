var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var minifyCss = require('gulp-minify-css');
var sourcemaps = require('gulp-sourcemaps');
var notify = require('gulp-notify');

gulp.task('sass', function() {
    return gulp.src('./sass/styles.scss')
        .pipe(sourcemaps.init())
            .pipe(sass().on('error', sass.logError))
            .pipe(minifyCss())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('.'))
        .pipe(notify("CSS Compiled!"));
});

gulp.task('watch', function() {
    gulp.watch('./sass/**/*.scss', ['sass']);
})

gulp.task('default', ['watch']);