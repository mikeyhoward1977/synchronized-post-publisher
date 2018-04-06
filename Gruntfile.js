/* jshint node:true */
'use strict';

module.exports = function( grunt ) {

	// auto load grunt tasks
	require( 'load-grunt-tasks' )( grunt );

	var pluginConfig = {

		// gets the package vars
		pkg: grunt.file.readJSON( 'package.json' ),

		// plugin directories
		dirs: {
			main: {
				js: 'assets/js',
				lang: 'languages'
			}
		},

		// pot file
		makepot: {
			target: {
				options: {
					domainPath: '<%= dirs.main.lang %>/',    // Where to save the POT file.
					exclude: ['build/.*'],
					mainFile: 'synchronized-post-publisher.php',    // Main project file.
					potFilename: 'synchronized-post-publisher.pot',    // Name of the POT file.
					potHeaders: {
						poedit: true,                 // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
								},
					type: 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true,    // Whether the POT-Creation-Date should be updated without other changes.
					processPot: function( pot ) {
						pot.headers['report-msgid-bugs-to'] = 'https://mikesplugins.co.uk/';
						pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)';
						pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>';
						pot.headers.language = 'en_US';
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Synchornized Post Publisher',
								'https://mikesplugins.co.uk/',
								'Mike Howard'
							];
							for ( translation in pot.translations[''] ) {
								if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
									if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
										console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
										delete pot.translations[''][ translation ];
									}
								}
							}
						return pot;
					}
				}
			}
		},

		// checktextdomain
		checktextdomain: {
			options:{
				text_domain: 'synchronized-post-publisher',
				create_report_file: false,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
					]
			},
			files: {
				src: [
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!build/.*', // Exclude build/
					'!tests/**'
					],
				expand: true
			}
		},

		// potomo
		/*potomo: {
			dist: {
				options: {
					poDel: true
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.main.lang %>',
					src: ['*.po'],
					dest: '<%= dirs.main.lang %>',
					ext: '.mo',
					nonull: true
				}]
			}
		},*/

		// glotpress
		/*glotpress_download: {
			core: {
				options: {
					domainPath: '<%= dirs.main.lang %>',
					url: 'https://translate.wordpress.org',
					slug: 'wp-plugins/synchronized-post-publisher/stable',
					textdomain: 'synchronized-post-publisher',
					filter: {
						minimum_percentage: 1,
					}
				}
			},
		},*/

		// svn settings
		svn_settings: {
			path: 'https://plugins.svn.wordpress.org/synchronized-post-publisher/<%= pkg.name %>',
			tag: '<%= svn_settings.path %>/tags/<%= pkg.version %>',
			trunk: '<%= svn_settings.path %>/trunk',
			exclude: [
				'.editorconfig',
				'.git/',
				'.gitignore',
				'.jshintrc',
				'.sass-cache/',
				'node_modules/',
				'phpunit.xml',
				'tests/',
				'Gruntfile.js',
				'README.md',
				'package.json',
				'*.zip'
			]
		},

		// javascript linting with jshint
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'<%= dirs.main.js %>/admin-scripts.js',
			]
		},

		// cssmin
		cssmin:	{
			build:	{
				files: {
				}
			}
		},

		// uglify to concat and minify
		uglify: {
			dist: {
				files: {
					'<%= dirs.main.js %>/admin-scripts.min.js': ['<%= dirs.main.js %>/admin-scripts.js'],
				}
			}
		},

		// watch for changes and trigger jshint and uglify
		watch: {
			js: {
				files: [
					'<%= jshint.all %>'
				],
				tasks: ['jshint', 'uglify']
			}
		},

		// image optimization
		imagemin: {
			dist: {
				options: {
					optimizationLevel: 7,
					progressive: true
				},
				files: [
					{
						expand: true,
						cwd: '<%= dirs.main.images %>/',
						src: '**/*.{png,jpg,gif}',
						dest: '<%= dirs.main.images %>/'
					},
					{
						expand: true,
						cwd: './',
						src: 'screenshot-*.png',
						dest: './'
					}
				]
			}
		},

		// rsync commands used to take the files to svn repository
		rsync: {
			options: {
				args: ['--verbose'],
				exclude: '<%= svn_settings.exclude %>',
				syncDest: true,
				recursive: true
			},
			tag: {
				options: {
					src: './',
					dest: '<%= svn_settings.tag %>'
				}
			},
			trunk: {
				options: {
				src: './',
				dest: '<%= svn_settings.trunk %>'
				}
			}
		},

		// shell command to commit the new version of the plugin
		shell: {
			// Remove delete files.
			svn_remove: {
				command: 'svn st | grep \'^!\' | awk \'{print $2}\' | xargs svn --force delete',
				options: {
					stdout: true,
					stderr: true,
					execOptions: {
						cwd: '<%= svn_settings.path %>'
					}
				}
			},
			// Add new files.
			svn_add: {
				command: 'svn add --force * --auto-props --parents --depth infinity -q',
				options: {
					stdout: true,
					stderr: true,
					execOptions: {
						cwd: '<%= svn_settings.path %>'
					}
				}
			},
			// Commit the changes.
			svn_commit: {
				command: 'svn commit -m "updated the plugin version to <%= pkg.version %>"',
				options: {
					stdout: true,
					stderr: true,
					execOptions: {
						cwd: '<%= svn_settings.path %>'
					}
				}
			}
		}
	};

	// initialize grunt config
	// --------------------------
	grunt.initConfig( pluginConfig );

	// register tasks
	// --------------------------

	// default task
	grunt.registerTask( 'default', [
		'cssmin',
		'jshint',
		'uglify',
		'makepot',
		'checktextdomain',
		//'potomo',
		//'glotpress_download'
	] );

	// deploy task
	grunt.registerTask( 'deploy', [
		'default',
		'rsync:tag',
		'rsync:trunk',
		'shell:svn_remove',
		'shell:svn_add',
		'shell:svn_commit'
	] );
};