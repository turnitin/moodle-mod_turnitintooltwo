var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var minifyCss = require('gulp-minify-css');
var sourcemaps = require('gulp-sourcemaps');
var notify = require('gulp-notify');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');

gulp.task('sass', function() {
    return gulp.src('./sass/styles.scss')
        .pipe(sourcemaps.init())
            .pipe(sass().on('error', sass.logError))
            .pipe(minifyCss())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('.'))
        .pipe(notify("CSS Compiled!"));
});

gulp.task('jsmin', function() {
	return gulp.src(['./jquery/turnitintooltwo*.js', '!./jquery/turnitintooltwo*.min.js'])

    .pipe(uglify())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('./jquery/'))
    .pipe(notify('js minified'));
});


gulp.task('watch', function() {
    gulp.watch('./sass/**/*.scss', ['sass']);
    gulp.watch('./jquery/turnitintooltwo*.js', ['jsmin']);
})

gulp.task('default', ['watch']);