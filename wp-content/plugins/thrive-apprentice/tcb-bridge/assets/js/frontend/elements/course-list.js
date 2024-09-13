/**
 * @param {jQuery} $
 * @param {Object} TCB_Front
 * @return {{initCourseLists: initCourseLists, class: CourseList}}
 */
module.exports = ( $, TCB_Front ) => {
	const Utils = require( '../utils' ),
		/**
		 * Initialize all the course lists and their attachments
		 */
		initCourseLists = () => {
			TCB_Front.$document.ready( function () {
				function tvaCourseListBootstrap () {
					$( '.tva-course-list' ).each( ( index, element ) => {
						const courseListInstance = new CourseList( $( element ) );

						courseListInstance
							.renderPagination()
							.renderSearchFormFilter()
							.renderDropdownFilter();
					} );
				}

				setTimeout( tvaCourseListBootstrap );
			} );
		};

	TCB_Front.Hooks.addFilter( 'tve.carousel.element_with_links', selector => `${ selector },.tva-course-list-item` );

	class CourseList extends TCB_Front.PostList {
		/**
		 * @param {jQuery} $courseList
		 */
		constructor ( $courseList ) {
			super( $courseList );

			this.courseProgress = {
				notStarted: 0,
				inProgress: 1,
				finished: 2,
				noAccess: 3
			};

			/* overwrite the parent 'listIdentifier' */
			this.listIdentifier = '.tva-course-list-item';
			this.uniqueID = $courseList.attr( 'data-css' );
			this.query = {}; //Used for filter in FrontEnd

			try {
				this.originalQuery = JSON.parse( $courseList.data( 'query' ).split( "'" ).join( '"' ) );
			} catch ( e ) {
				this.originalQuery = {};
			}
		}

		/**
		 * @return {CourseList}
		 */
		editorInit () {
			TCB_Front.PostList.prototype.editorInit.call( this );

			return this;
		}

		/**
		 * @param {jQuery} $root
		 * @return {CourseList}
		 */
		frontendInit ( $root ) {
			$root = $root || TCB_Front.$document;

			if ( typeof tva_course_lists === 'undefined' ) {
				console.warn( 'Error initializing the course list parameters' );
			} else {
				this.listArgs = tva_course_lists.find( courseList => {
					return this.$element.is( courseList.identifier );
				} );

				if ( this.listArgs ) {
					this.content = $( `.tcb-course-list-template[data-identifier="${ this.listArgs.template }"]` ).html();
				}

				this.initCoursePermalinks( $root );
				this.frontendMarkupChanges();
			}

			return this;
		}

		/**
		 * If the 'Link entire item to content' is checked, add links to the course wrappers
		 *
		 * @param {jQuery} $wrapper
		 * @return {CourseList}
		 */
		initCoursePermalinks ( $wrapper ) {
			$wrapper.on( 'click', '.tva-course-list[data-disabled-links="1"] .tva-course-list-item', function ( e ) {
				//prevent course redirect during drag
				if ( ! this.classList.contains( 'tcb-during-drag' ) ) {
					let target;

					switch ( e.button ) {
						case 0:
							target = '_self';
							break;
						case 1:
							target = '_blank';
							break;
						default:
							break;
					}

					window.open( $( this ).attr( 'data-permalink' ), target );
				}
			} );

			return this;
		}

		/**
		 * Handles the frontend markup changes
		 */
		frontendMarkupChanges () {

			/**
			 * For the empty inline shortcodes we hide the elements and the wrapper if the wrapper contains only that element
			 */
			this.$element.find( '.thrive-shortcode-content:empty' ).each( ( index, element ) => {
				const $paragraph = $( element ).closest( 'p' );

				if ( $paragraph.height() === 0 ) {
					$paragraph.hide();
				}

				const $textWrapper = $paragraph.closest( '.thrv_wrapper' );
				if ( $textWrapper.height() === 0 ) {
					$textWrapper.hide();
				}

			} );
		}

		/* Retrieve the pagination instance and render it if it exists */
		renderPagination () {
			this.pagination = this.getPaginationInstance();

			if ( this.pagination ) {
				if ( ! tve_frontend_options.is_editor_page ) {
					this.pagination.addLoadListeners();
				}

				this.pagination.render();
			}

			return this;
		}

		/**
		 * Renders the search form
		 *
		 * @return {CourseList}
		 */
		renderSearchFormFilter () {
			this.$searchForm = $( `.thrv-search-form[data-list="${ this.uniqueID }"]` );

			if ( this.$searchForm.length ) {
				this.$searchForm.find( 'form' ).submit( event => {
					//Kill the submit event for the Search Form Filter
					event.preventDefault();
					this.filterCourses( event );
				} );

				this.$searchForm.find( 'form input[type="search"]' ).on( 'keyup', Utils.debounce( this.filterCourses.bind( this ), 300 ) );
			}

			return this;
		}

		/**
		 * Renders the dropdown filter
		 *
		 * @return {CourseList}
		 */
		renderDropdownFilter () {
			this.$dropdown = $( `.tva-course-list-dropdown[data-list="${ this.uniqueID }"]` );

			if ( this.$dropdown.length ) {

				/**
				 * If the Course list is filtered by topics / labels,
				 * We need to remove from the dropdown filter the values that are different from the values that the course list comes with
				 *
				 * Ex: if the course list is filtered by topic ID = 3 we keep only the topic ID = 3 as a filter in the dropdown
				 */
				[ 'topics', 'labels', 'progress' ].forEach( filterElementKey => {
					if ( Array.isArray( this.originalQuery[ filterElementKey ] ) && this.originalQuery[ filterElementKey ].length > 0 ) {
						this.$dropdown.find( `li[data-value^="${ filterElementKey }_"]` ).each( ( index, domElement ) => {
							const val = domElement.getAttribute( 'data-value' ).replace( `${ filterElementKey }_`, '' );

							if ( ! this.originalQuery[ filterElementKey ].includes( val ) ) {
								if ( tve_frontend_options.is_editor_page ) {
									domElement.style.display = 'none';
								} else {
									domElement.remove();
								}
							}
						} );
					}
				} );


				this.$dropdown.on( 'tcb.dropdown_value_changed', () => this.filterCourses() );
			}

			return this;
		}

		/**
		 * Can be called by key up event on input type search or by pressing the search button
		 *
		 * @param {Event} event
		 */
		filterCourses ( event ) {
			this.query = { ...this.originalQuery };

			if ( this.$searchForm.length ) {
				this.query.search = this.$searchForm.find( 'input[type="search"]' ).val();
			}

			if ( this.$dropdown.length ) {
				const value = this.$dropdown.find( 'input' ).val();
				if ( value ) {
					if ( value.indexOf( '_' ) > - 1 ) {
						const aux = value.split( '_' );
						this.query[ aux[ 0 ] ] = [ aux[ 1 ] ]; //We need to send this in an array form
					} else if ( value === 'progress-all' ) {
						this.query.progress = ( this.originalQuery.progress && this.originalQuery.progress.length ) ?
							[ ...this.originalQuery.progress.filter( ( i ) => parseInt( i ) !== this.courseProgress.noAccess ) ] :
							[ this.courseProgress.notStarted, this.courseProgress.inProgress, this.courseProgress.finished ];
					}
				}
			}

			/* always reset the current page to 1 when filtering courses */
			this.listArgs.attr.paged = 1;

			this.getItems( response => {
				this.insertItems( response, true );

				if ( this.pagination && this.pagination.type === 'numeric' && this.pagination.$element.is( ':visible' ) ) {
					this.refreshPaginationAfterFilter( response );
				}
			} );
		}

		/**
		 * Certain actions to do before rendering the numeric pagination again
		 * The alternative is to re-instantiate the entire pagination, but that's too much because we don't want to change the wrapper
		 *
		 * @param {Object} response
		 */
		refreshPaginationAfterFilter ( response ) {
			/* add the query inside the attributes prepared for paginating results */
			this.listArgs.attr.query = this.query;

			/* after filtering, the current page is always the first */
			this.pagination.currentPage = 1;

			/* recalculate and update the last & total page */
			const totalPages = Math.ceil( response.total / this.listArgs.attr.posts_per_page );

			this.pagination.lastPage = totalPages;
			this.pagination.totalPages = totalPages;

			/* update the data-page of the 'last page' button ( this is usually not re-rendered by the post list since it makes no sense, but here...it makes sense ) */
			this.pagination.buttons.$last.attr( 'data-page', totalPages );

			this.pagination.render();
			this.pagination.disableLoading();
		}

		/**
		 * Do an ajax request to get new courses.
		 *
		 * @param {Function} callback
		 * @param {Object} args
		 */
		getItems ( callback, args = this.listArgs ) {

			if ( Object.keys( this.query ).length === 0 ) {
				this.query = { ...this.originalQuery };
			}

			$.ajax( {
				url: tve_frontend_options.routes.courses + '/html',
				method: 'POST',
				headers: {
					/* send the nonce so Membership Content plugins can retrieve the current user during ajax calls */
					'X-WP-Nonce': tve_frontend_options.nonce
				},
				beforeSend: () => this.$element.addClass( 'tve-loader-square' ),
				data: {
					content: Utils.prepareWordfencePostContent( this.content ),
					args,
					query: this.query,
					disable_links: Number( args.attr && parseInt( args.attr[ 'disabled-links' ] ) === 1 ),
					tva_skin_id: TCB_Front.queryString.get( 'tva_skin_id' ), //If this parameter is defined in the URL we need to send it in the request
				},
				success: response => {
					if ( typeof callback === 'function' ) {
						callback( response );
					}

					/* re-initialize the animation events after loading the new courses */
					TCB_Front.event_triggers( this.$element );

					/**
					 * Remove the loader class after the courses have been fetched
					 */
					this.$element.removeClass( 'tve-loader-square' );
				},
				error: () => {
					console.warn( 'There was an error and the content could not be loaded.' );
				}
			} );
		}

		/**
		 * @param {Object} ajaxResponse
		 * @param {boolean} replace
		 */
		insertItems ( ajaxResponse, replace = false ) {
			if ( ajaxResponse.count && ajaxResponse.html ) {
				const $courseHTML = this.$element.clone().empty();

				for ( const courseHTML in ajaxResponse.html ) {
					if ( ajaxResponse.html.hasOwnProperty( courseHTML ) ) {
						$courseHTML.append( ajaxResponse.html [ courseHTML ] );
					}
				}

				if ( replace ) {
					this.$element.replaceWith( $courseHTML );

					/* since we replaced the element, we have to look for it again.
					 * if this is a data-css identifier, modify the identifier so we can search for it
					 */
					if ( this.identifier.includes( 'tve-u-' ) ) {
						this.$element = $( `[data-css=${ this.identifier }]` );
					} else {
						this.$element = $( this.identifier );
					}

					/* re-initialize the masonry because we replaced the initial items */
					TCB_Front.postGridLayout();
				} else {
					this.$element.append( $courseHTML.html() );

					/* re-do the masonry */
					this.masonryRedo();
				}

				this.frontendMarkupChanges();

				if ( TCB_Front.progressBar ) {
					/**
					 * The progress bar module should be called only if there exists the resource for it.
					 * TCB_Front.progressBar can be NULL if the course list has no progress bar element inside it
					 */
					TCB_Front.progressBar.init();
				}
			}

			this.$element.toggleClass( 'tva-empty-list', parseInt( ajaxResponse.count ) === 0 );
		}
	}

	return {
		'class': CourseList,
		initCourseLists,
	};
};
