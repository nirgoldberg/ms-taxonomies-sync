module.exports = function(grunt) {

	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},

			js: {
				cwd: 'assets/js',
				src: ['*.js'],
				dest: 'assets/js/min/',
				expand: true,
				flatten: true,
				ext: '.min.js',
			}
		},

		sass: {
			dist: {
				options: {
					style: 'compressed',
				},

				files: [{
					cwd: 'assets/scss',
					src: ['*.scss'],
					dest: 'assets/css/',
					expand: true,
					flatten: false, 
					ext: '.css',
				}]
			}
		},

		watch: {
			js: {
				files: ['assets/js/*.js'],
				tasks: ['uglify']
			},

			css: {
				files: ['assets/scss/*.scss'],
				tasks: ['sass']
			}
		}
    });

	// Load plugins
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Default tasks
	grunt.registerTask('default', ['uglify', 'sass']);

};