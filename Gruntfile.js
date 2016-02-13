module.exports = function(grunt){
	grunt.initConfig({
		less: {
			development: {
				src: ['./less/*.less'],
				dest: './css/report-app.css'
			}
		},
		watch: {
			less: {
				files: ['./less/*.less'],
				tasks: ['less']
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.registerTask('default', ['less', 'watch']);
}
