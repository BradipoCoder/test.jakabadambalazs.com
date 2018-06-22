var gulp = require('gulp');
var less = require('gulp-less');
 
/* Task to compile less */
gulp.task('compile-less', function() {  
  gulp.src('./less/style.less')
    .pipe(less())
    .pipe(gulp.dest('./css/'));
  // Spengo la compilazione della stampa
  //gulp.src('./less/print/print-style.less')
  //  .pipe(less())
  //  .pipe(gulp.dest('./css/'));
}); 
/* Task to watch less changes */
gulp.task('watch-less', function() {  
  gulp.watch(['./less/*.less','./less/utility/*.less'] , ['compile-less']);
  
  // Spengo il watch sul file di stampa
  //gulp.watch('./less/print/*.less' , ['compile-less']);
});
 
/* Task when running `gulp` from terminal */
gulp.task('default', ['watch-less']);