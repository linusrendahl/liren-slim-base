module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        less: {
          dist: {
            files: {
              'assets/css/main.min.css': [
                'assets/less/source.less'
              ]
            },
            options: {
              compress: true,
              // LESS source map
              // To enable, set sourceMap to true and update sourceMapRootpath based on your install
              //sourceMap: false,
              //sourceMapFilename: 'assets/css/main.min.css.map',
              //sourceMapRootpath: '/app/themes/roots/'
            }
          }
        },

        watch: {
            scripts: {
                files: ['assets/less/*.less', 'assets/js/main.js'],
                tasks: ['less', 'uglify'],
                options: {
                    spawn: false,
                },
            }
        },

        uglify: {
            build: {
                src: 'assets/js/main.js',
                dest: 'assets/js/main.min.js'
            }
        }

    });

    // 3. Where we tell Grunt we plan to use this plug-in.
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    grunt.registerTask('default', ['less', 'uglify']);

};