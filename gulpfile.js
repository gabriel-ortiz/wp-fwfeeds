var gulp = require('gulp');
var plumber = require('gulp-plumber');
var rename = require('gulp-rename');
var autoprefixer = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var sass = require('gulp-ruby-sass');
var browserSync = require('browser-sync');


gulp.task('browser-sync', function() {
  browserSync({
    server: {
       baseDir: "./"
    }
  });
});

gulp.task('bs-reload', function () {
  browserSync.reload();
});

gulp.task('styles', function() {
  return sass('assets/styles/style.scss', { style: 'expanded' })
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))  
    .pipe(autoprefixer({
        browsers: ['last 2 versions']
    }))
    .pipe(gulp.dest('dist/styles'))
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('dist/styles'))
    .pipe(browserSync.reload({stream:true}));    
});



gulp.task('scripts', function(){
  return gulp.src(['assets/scripts/**/*.js', '!assets/scripts/main/example.js', '!assets/scripts/main/pluginTemplate.js'])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(concat('main.js'))
    .pipe(gulp.dest('dist/scripts/'))
    .pipe(rename({suffix: '.min'}))
    .pipe(uglify())
    .pipe(gulp.dest('dist/scripts/'))
    .pipe(browserSync.reload({stream:true}));
});

gulp.task('default', ['browser-sync'], function(){
  gulp.watch(["assets/styles/**/*.scss","assets/styles/**/*.sass" ], ['styles']);
  gulp.watch("assets/scripts/**/*.js", ['scripts']);
  gulp.watch("*.html", ['bs-reload']);
});