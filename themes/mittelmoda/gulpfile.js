var gulp = require('gulp');
var less = require('gulp-less');
var util = require('gulp-util');

/* Task to compile less */
gulp.task('compile-less', function() {  
  gulp.src('./less/style.less')
    .pipe(less().on('error', util.log))
    .pipe(gulp.dest('./css/'));

}); 
/* Task to watch less changes */
gulp.task('watch-less', function() {  
  gulp.watch(['./less/*.less', './less/*/*.less'] , ['compile-less']);
});

// gulp.task('my-watch', function(){
//   var watcher = gulp.watch(['./less/*.less', './less/*/*.less'], ['compile-less']);
//   watcher.on('change', function(event) {
//     //gulp.start('compile-less');
//     console.log('File ' + event.path + ' was ' + event.type + ' | Compiling ');
//   });
// });

 
/* Task when running `gulp` from terminal */
gulp.task('default', ['watch-less']);