/**
 * Wordpress Plugin development Gruntfile
 * 
 *
 * @version 0.1.0
 * @author Stuart Laverick http://www.appropriatesolutions.co.uk/
 */

module.exports = function(grunt) {
  // configure the tasks
  grunt.initConfig({
    pluginDir: 'wp-social-streams',
    distPath: 'dist/<%= pluginDir %>',
    srcPath: 'src',

    pkg: grunt.file.readJSON('package.json'),

    clean: {
      build: {
        src: [ '<%= distPath %>' ]
      },
      php: {
        src: [ '<%= distPath %>/**/*.php', '!<%= distPath %>/vendor/' ]
      },
    },

    less: {
      development: {
        options: {
          paths: '<%= srcPath %>/assets/less',
          plugins: [
            new (require('less-plugin-autoprefix'))({browsers: ["last 2 versions"]})
          ],
          sourceMap: true,
          sourceMapBasepath: 'less',
          sourceMapRootpath: '/',
          optimization: 9
        },
        files: {
          '<%= distPath %>/assets/css/style.css': '<%= srcPath %>/assets/less/style.less'
        }
      },
      production: {
        options: {
          paths: '<%= srcPath %>/assets/less',
          plugins: [
            new (require('less-plugin-autoprefix'))({browsers: ["last 2 versions"]}),
            new (require('less-plugin-clean-css'))()
          ],
          cleancss: true,
          compress: true,
          optimization: 1
        },
        files: {
          '<%= distPath %>/assets/css/style.min.css': '<%= srcPath %>/assets/less/style.less'
        }
      }
    },

    imagemin: {
      development: {
        options: {
          optimizationLevel: 1
        },
        files: [{
          expand: true,                  // Enable dynamic expansion
          cwd: '<%= srcPath %>/assets/images/',                // Src matches are relative to this path
          src: ['**/*.{png,jpg,gif}'],   // Actual patterns to match
          dest: '<%= distPath %>/assets/img/'              // Destination path prefix
        }]
      },
      production: {
        options: {
          optimizationLevel: 7
        },
        files: [{
          expand: true,                  // Enable dynamic expansion
          cwd: '<%= srcPath %>/assets/images/',                // Src matches are relative to this path
          src: ['**/*.{png,jpg,gif}'],   // Actual patterns to match
          dest: '<%= distPath %>/assets/img/'              // Destination path prefix
        }]
      }
    },

    jshint: {
      build: ['Gruntfile.js', '<%= srcPath %>/assets/js/main.js', '<%= srcPath %>/assets/js/**/*.js']
    },

    concat: {
      options: {
        // separator: ';',
          // sourceMap: true,
        nonull: true
      },
      dist: {
        src: [
          // 'bower_components/modernizr/modernizr.js',
          // 'bower_components/jquery/dist/jquery.js',
          // 'bower_components/bootstrap/js/transition.js',
          // 'bower_components/bootstrap/js/collapse.js',
          // 'bower_components/bootstrap/js/tab.js',
          // 'bower_components/bootstrap/js/dropdown.js',
          // 'bower_components/bootstrap/js/carousel.js',
          // 'bower_components/bootstrap/js/modal.js',
          // 'bower_components/flexslider/jquery.flexslider.js',
          '<%= srcPath %>/assets/js/main.js',
          '<%= srcPath %>/assets/js/**/*.js'
          ],
        dest: '<%= distPath %>/assets//js/script.js'
      }
    },

    uglify: {
      development: {
        options: {
          mangle: false
        },
        files: {
          'dist/js/script.js': [
          'bower_components/modernizr/modernizr.js',
          'bower_components/jquery/dist/jquery.js',
          // 'bower_components/bootstrap/js/transition.js',
          'bower_components/bootstrap/js/collapse.js',
          // 'bower_components/bootstrap/js/tab.js',
          // 'bower_components/bootstrap/js/dropdown.js',
          // 'bower_components/bootstrap/js/carousel.js',
          // 'bower_components/bootstrap/js/modal.js',
          // 'bower_components/flexslider/jquery.flexslider.js',
          '<%= srcPath %>/js/main.js',
          '<%= srcPath %>/js/**/*.js'
          ]
        }
      },
      production: {
        options: {
          mangle: false
        },
        files: {
          'dist/js/script.js': [
          'bower_components/modernizr/modernizr.js',
          'bower_components/jquery/dist/jquery.js',
          // 'bower_components/bootstrap/js/transition.js',
          'bower_components/bootstrap/js/collapse.js',
          // 'bower_components/bootstrap/js/tab.js',
          // 'bower_components/bootstrap/js/dropdown.js',
          // 'bower_components/bootstrap/js/carousel.js',
          // 'bower_components/bootstrap/js/modal.js',
          // 'bower_components/flexslider/jquery.flexslider.js',
          '<%= srcPath %>/js/main.js',
          '<%= srcPath %>/js/**/*.js'
          ]
        }
      }
    },

    copy: {
      php: {
        cwd: '<%= srcPath %>/',
        src: [ 'lib/**/*.php', '*.php', 'composer.json'],
        dest: '<%= distPath %>',
        expand: true
      },
      images: {
        expand: true,                 // Enable dynamic expansion
        cwd: '<%= srcPath %>/assets/images/',             // Src matches are relative to this path
        src: ['**/*.{png,jpg,gif}'],  // Actual patterns to match
        dest: '<%= distPath %>/assets/img/'           // Destination path prefix
      }
    },

    composer: {
      development: {
        options: {
          flags: ['no-dev'],
          cwd: '<%= distPath %>'
        }
      }
    },

    watch: {
      less: {
        files: '<%= srcPath %>/assets/less/*.less',
        tasks: 'css'
      },
      scripts: {
        files: ['<%= srcPath %>/assets/js/main.js','<%= srcPath %>/assets/js/**/*.js'],
        tasks: [ 'build' ]
      },
      images: {
        files: [ '<%= srcPath %>/assets/images/**/*'],
        tasks: [ 'copy:images' ]
      },
      php: {
        files: ['<%= srcPath %>/*.php', '<%= srcPath %>/lib/**/*.php'],
        tasks: ['copy:php']
      },
      composer: {
        files: '<%= srcPath %>/composer.json',
        tasks: ['copy:php', 'composer:development:update']
      }
    }

  });

  // load the tasks
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-composer');
  grunt.loadNpmTasks('grunt-contrib-imagemin');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // define the tasks
  grunt.registerTask(
    'build',
    'Creates a development version of the files',
    ['clean:build', 'copy:php', 'composer:development:install', 'css', 'buildScripts', 'copy:images']
    // ['clean', 'css', 'buildScripts', 'imagemin:development']
  );

  grunt.registerTask(
    'deploy',
    'Creates a production version of the files',
    ['clean:build', 'less:production', 'deployScripts']
    // ['clean', 'less:production','deployScripts', 'imagemin:production']
  );

  grunt.registerTask(
    'css',
    'Process template.less into compiled.css',
    [ 'less:development' ]
  );

  grunt.registerTask(
    'buildScripts',
    'Checks and concatenates the JavaScript files.',
    [ 'jshint', 'concat' ]
  );

  grunt.registerTask(
    'deployScripts',
    'Checks and compiles the JavaScript files.',
    [ 'jshint', 'uglify:production' ]
  );

  grunt.registerTask(
    'default',
    'Watches the project for changes and automatically builds the dist components.',
    [ 'build', 'watch' ]
  );
};