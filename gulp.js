const gulp = require( 'gulp' );
const eslint = require( 'gulp-eslint' );
const newer = require( 'gulp-newer' );

const paths = {
	scripts: {
		src: 'resources/js',
		dest: 'public/js'
	}
};

gulp.task( 'lint-scripts', () => {
	return gulp
		.src( `${paths.scripts.src}/*.js` )
		.pipe( newer( {
			dest: `${paths.scripts.src}/*.js`,
			extra: '.eslintrc'
		} ) )
		.pipe( eslint() )
		.pipe( eslint.format() );
} );
