/**
 * Frontend javascript functionalities handling the display of forms
 */
window.TL_Front = window.TL_Front || {};

/* minor hackery to ensure we have this available */
window.ThriveGlobal = window.ThriveGlobal || {$j: jQuery.noConflict()};

let modulesFinishedLoading = false;

TL_Front.add_page_css = function ( stylesheets ) {
	ThriveGlobal.$j.each( stylesheets, function ( _id, href ) {
		_id += '-css';

		href = href.replace( /^http(s)?:\/\//, '//' );

		if ( href.indexOf( 'thrive_flat' ) !== - 1 ) {
			var testElement = document.createElement( 'div' );
			testElement.classList.add( 'tve-cb' );
			document.body.append( testElement );
			/* tcb/editor/css/sass/elements/_contentbox.scss line 59 - we're checking if a specific css exists then the file has been loaded
			 * one of the cases where the previous implementation was not working was when a caching plugin was merging the js files */
			if ( getComputedStyle( testElement )[ 'clear' ] === 'both' ) {
				testElement.remove();
				return;
			}

			testElement.remove();
		}

		/* make sure a css is not loaded twice */
		if ( ! ThriveGlobal.$j( '#' + _id ).length && ! ThriveGlobal.$j( 'link[href="http:' + href + '"]' ).length && ! ThriveGlobal.$j( 'link[href="https:' + href + '"]' ).length ) {
			ThriveGlobal.$j( '<link rel="stylesheet" id="' + _id + '" type="text/css" href="' + href + '"/>' ).prependTo( 'head' );
		}
	} );
};

/**
 * Load optimized styles and js modules
 * @param assets
 */
TL_Front.lightspeed_assets = function ( assets ) {
	var hasFlat = function () {
		return ThriveGlobal.$j( 'link[href*="thrive_flat.css"]' ).length > 0
	}, modulesToLoad = [];

	if ( assets.css ) {
		if ( assets.css.files ) {
			for ( var file in assets.css.files ) {
				var isFlat = file === 'flat';
				if ( ( ! hasFlat() && isFlat ) || ! isFlat ) {
					ThriveGlobal.$j( '<link rel="stylesheet" href="' + assets.css.files[ file ] + '"/>' ).prependTo( 'head' );
				}
			}
		}

		if ( assets.css.inline && assets.css.inline.length ) {
			assets.css.inline.forEach( function ( style ) {
				if ( ! hasFlat() ) {
					ThriveGlobal.$j( style ).prependTo( 'head' );
				}
			} )
		}
	}

	if ( assets.js ) {
		for ( var module in assets.js ) {
			if ( TCB_Front && ! TCB_Front.isModuleLoaded( module ) ) {
				modulesToLoad.push( module )
			}
		}
	}

	function loadModule() {
		if ( modulesToLoad.length ) {
			var currentModule = modulesToLoad.shift()
			/* load next module only after the current one is loaded */
			TL_Front.add_head_script( assets.js[ currentModule ], 'tl-lightspeed-' + currentModule, loadModule );
		} else {
			modulesFinishedLoading = true
		}
	}

	loadModule()
}
/**
 * Add the conditions from TL in the localized conditions preview object
 *
 * @param allData - request data
 */
TL_Front.conditional_display = function ( allData ) {
	/* if we have conditional displays on the request, we append them to the localized variable */
	if ( typeof allData !== 'undefined' && allData.lazy_load_conditional_preview && tcb_condition_sets ) {
		allData.lazy_load_conditional_preview.forEach( function ( display ) {
			var index = tcb_condition_sets.findIndex( function ( item ) {
				return item.key === display.key
			} );
			if ( index === - 1 ) {
				tcb_condition_sets.push( display );
			}
		} )
	}
}

TL_Front.$document = ThriveGlobal.$j( document );

/**
 * Extend the TL_Const object being careful not to overwrite some of its fields.
 *
 * @param {*} configuration
 */
TL_Front.extendConst = function ( configuration ) {
	if ( TL_Const.current_screen ) { // this makes sure the original screen data is not changed
		delete configuration.current_screen;
	}
	ThriveGlobal.$j.extend( true, TL_Const, configuration );
}

/**
 * Emulates the default document.write function - but appending elements with jquery - thus not breaking the body on document.write after domready
 * @param str
 */
TL_Front.document_write = function ( str ) {
	ThriveGlobal.$j( 'body' ).append( str );
};

/**
 * we need to add the scripts using this method, to make sure the onload callback is fired properly
 * @param src
 * @param onload
 */
TL_Front.add_head_script = function ( src, id, onload ) {
	var script = document.createElement( 'script' ),
		head = ThriveGlobal.$j( 'head' )[ 0 ];

	script.async = true;
	if ( typeof onload === 'function' ) {
		script.onload = script.onreadystatechange = onload;
	}
	if ( typeof id !== 'undefined' ) {
		script.id = id;
	}

	script.src = src;

	head.insertBefore( script, head.firstChild );
};

TL_Front.add_page_js = function ( links, onLoad ) {
	if ( typeof onLoad !== 'function' ) {
		onLoad = function () {
		};
	}
	var to_load = 0;
	ThriveGlobal.$j.each( links, function ( _id, href ) {
		if ( _id === 'tve_frontend' && typeof TCB_Front !== 'undefined' ) {
			return true;
		}
		_id += '-js';
		/* make sure a script is not loaded twice */
		if ( href && ! ThriveGlobal.$j( '#' + _id ).length && ! ThriveGlobal.$j( 'script[src="' + href + '"]' ).length ) {
			to_load ++;
			/* facebook needs to be inserted with a custom fragment appended - jQuery.getScript does not allow that */
			if ( href.indexOf( 'connect.facebook.net' ) !== - 1 ) {
				TL_Front.add_head_script( href, _id, function () {
					to_load --;
				} );
				return true;
			}
			ThriveGlobal.$j.getScript( href, function () {
				to_load --;
			} );
		}
	} );

	function check_loaded() {
		if ( to_load === 0 ) {
			onLoad();
			return;
		}
		setTimeout( check_loaded, 50 );
	}

	check_loaded();
};

TL_Front.do_impression = function () {
	var data = TL_Front.impressions_data;
	if ( data === undefined ) {
		console.log( "No form to register impression for !" );
		return;
	}

	var ajax_data = {
		security: TL_Const.security,
		action: TL_Const.action_impression,
		tl_data: data,
		current_screen: TL_Const.current_screen
	};
	ThriveGlobal.$j.each( TL_Const.custom_post_data, function ( k, v ) {
		ajax_data[ k ] = v;
	} );

	if ( window.TVE_Dash && ! TVE_Dash.ajax_sent ) {
		TVE_Dash.add_load_item( 'tl_impression', ajax_data );
	} else {
		ThriveGlobal.$j.post( TL_Const.ajax_url, ajax_data );
	}
};

ThriveGlobal.$j( function () {

	function _ieVersion() {
		/*global navigator */
		var rv = - 1;
		var ua;
		var re;

		if ( navigator.appName == 'Microsoft Internet Explorer' ) {
			ua = navigator.userAgent;
			re = new RegExp( 'MSIE ([0-9]{1,}[\\.0-9]{0,})' );

			if ( re.exec( ua ) !== null ) {
				rv = parseFloat( RegExp.$1 );
			}
		} else if ( navigator.appName == 'Netscape' ) {
			ua = navigator.userAgent;
			re = new RegExp( 'Trident/.*rv:([0-9]{1,}[\\.0-9]{0,})' );

			if ( re.exec( ua ) !== null ) {
				rv = parseFloat( RegExp.$1 );
			}
		}

		return rv;
	}

	/**
	 * SUPP-1217 Divi + Yoast + TL conflict
	 */
	if ( typeof TL_Const === 'undefined' ) {
		return;
	}

	ThriveGlobal.$j( '.tve-leads-screen-filler iframe, .tve-leads-ribbon iframe' ).not( '.thrv_social_default iframe' ).not( '.tcb-dr-done' ).each( function () {
		var $frame = ThriveGlobal.$j( this ).addClass( 'tcb-dr-done' );
		if ( $frame.attr( 'src' ) ) {
			$frame.attr( 'data-src', $frame.attr( 'src' ) );
		}
		$frame.attr( 'src', '' );
	} );

	if ( typeof TCB_Front !== 'undefined' ) {
		ThriveGlobal.$j( TCB_Front ).on( 'content_loaded.thrive', function ( event, $element ) {
			$element.find( '.tve-tl-anim' ).each( function () {
				var $this = ThriveGlobal.$j( this );
				ThriveGlobal.$j( TL_Front ).trigger( 'showform.thriveleads', {$target: $this} );
			} );
		} );
	}

	ThriveGlobal.$j( window ).on( 'tcb_after_dom_ready', function () {
		if ( ! tve_frontend_options.is_editor_page ) {
			var postType = TCB_Front.queryString.get( 'post_type' );
			if ( postType && postType === 'tve_lead_2s_lightbox' ) {
				//do it with timeout just in case they were hidden from other places
				setTimeout( function () {
					TCB_Front.handleIframes( ThriveGlobal.$j( '.tve_p_lb_content' ) );
				}, 100 );
			}
		}
	} );

	ThriveGlobal.$j( TL_Front ).on( 'showform.thriveleads', function ( event, data ) {
		var $target = data.$target ? data.$target : ThriveGlobal.$j( '.' + data.form_id ),
			$anim_target;
		if ( ! $target.length ) {
			return;
		}

		if ( data.TargetEvent && data.TargetEvent.tve_trigger === 'exit' && $target.data( 'shown-on-exit' ) ) {
			/* do not show form again on exit intent it if was shown once */
			return;
		}

		if ( data.first ) {
			$target = $target.first();
		}

		if ( $target.attr( 'data-s-state' ) ) {
			/**
			 * find the already subscribed state and show it
			 */
			$target = $target.closest( '.tl-states-root' ).find( '[data-state="' + $target.attr( 'data-s-state' ) + '"] .tl-lb-target' );
		}

		/**
		 * to know where we came from
		 */
		if ( data && data.$parentStateEl ) {
			$target.closest( '.tl-style' ).first().data( 'parentStateEl', data.$parentStateEl )
		}

		$anim_target = $target.hasClass( 'tve-tl-anim' ) ? $target : $target.find( '.tve-tl-anim' );
		$target.css( 'display', '' );
		setTimeout( function () {
			$anim_target.addClass( 'tve-leads-triggered' );
			TL_Front.handle_typefocus( $anim_target, 'start' );
			if ( ThriveGlobal.$j( '.tve_post_grid_masonry' ).length ) {
				TCB_Front.postGridLayout();
			}
		}, 0 );

		if ( typeof TL_Front[ 'open_' + data.form_type ] === 'function' ) {
			TL_Front[ 'open_' + data.form_type ]( $target, data.TargetEvent );
		} else {
			$target.show();
		}
		TCB_Front.resizePageSection();

		$target.on( 'switchstate', function ( event, container ) {
			if ( container.offset().top - ThriveGlobal.$j( window ).scrollTop() + container.outerHeight() < 0 ) {
				ThriveGlobal.$j( 'html, body' ).animate( {scrollTop: container.offset().top + 'px'}, 50 );
			}
			TCB_Front.resizePageSection();
		} );
		setTimeout( function () {
			$target.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe' ).each( function () {
				var $this = ThriveGlobal.$j( this );
				if ( $this.attr( 'data-src' ) ) {
					$this.attr( 'src', $this.attr( 'data-src' ) );
				}
			} );
		}, 200 );
	} );

	/* SUPP-3000 Close ribbon - moved here due to some weird cases involving ScrollMat on mobile devices*/
	ThriveGlobal.$j( 'body' ).on( 'click', '.tve-ribbon-close', function () {
		var $target = ThriveGlobal.$j( this ).closest( '.tve-leads-ribbon' ),
			position = $target.data( 'position' );
		$target.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
			var $this = ThriveGlobal.$j( this );
			$this.attr( 'data-src', $this.attr( 'src' ) );
			$this.attr( 'src', '' );
		} );
		$target.removeClass( 'tve-leads-triggered' );
		if ( position === 'top' ) {
			ThriveGlobal.$j( 'body' ).animate( {marginTop: 0}, 200, function () {
				document.body.style.removeProperty( 'margin-top' );
				if ( TCB_Front && TCB_Front.$window ) {
					TCB_Front.$window.trigger( 'scroll' );
				}
			} );
		} else if ( position === 'bottom' ) {
			ThriveGlobal.$j( 'body' ).animate( {marginBottom: 0 + 'px'}, 200, function () {
				document.body.style.removeProperty( 'margin-bottom' );
			} );
		}
		TL_Front.handle_typefocus( $target, 'pause' );

		setTimeout( function () {
			$target.css( position, '' );
		}, 400 );
		/**
		 * close any error messages that might have been displayed on forms inside the lightbox
		 */
		ThriveGlobal.$j( '#tve-lg-error-container' ).hide();
	} );

	/**
	 * Replace shortcodes from conditional displays
	 */
	ThriveGlobal.$j( window ).on( 'conditional_display_loaded', () => {
		const shortcodeKeys = Object.keys( TL_Front.contentHtml || {} );
		if ( shortcodeKeys.length ) {
			shortcodeKeys.forEach( key => {
				const $placeholder = ThriveGlobal.$j( `.tl-placeholder-f-type-${key}` );
				if ( $placeholder.length ) {
					const $content = ThriveGlobal.$j( TL_Front.contentHtml[ key ] );
					$placeholder.replaceWith( $content );
					setTimeout( () => {
						ThriveGlobal.$j( TL_Front ).trigger( 'showform.thriveleads', {$target: $content} );
					} );
				}
			} );
		}
	} )

	if ( ! TL_Const.ajax_load ) {
		TL_Front.do_impression();
	}

	TL_Front.ajax_load_callback = function ( response, allData ) {
		if ( allData ) {
			TL_Front.conditional_display( allData );
		}

		if ( ! response || ! response.res || ! response.js || ! response.html ) {
			return;
		}

		TL_Front.lightspeed_assets( response.lightspeed || {} );
		TL_Front.add_page_css( response.res.css );
		TL_Front.add_page_css( response.res.fonts );

		if ( response.html ) {
			if ( ! response.html.widget ) {
				ThriveGlobal.$j( '.tl-widget-container' ).remove();
			}

			function show_trigger( twoStepKey ) {
				ThriveGlobal.$j( `.tl-2step-trigger-${twoStepKey.replace( 'two_step_', '' )}` ).show();
			}

			ThriveGlobal.$j.each( response.html, function ( elementType, html ) {
				if ( ! html ) {
					return true;
				}
				TL_Front.contentHtml = TL_Front.contentHtml || {};
				TL_Front.contentHtml[ elementType ] = html;
				if ( elementType === 'in_content' ) {
					// move the placeholder after the nth paragraph
					let position = parseInt( response.in_content_pos ),
						fn = 'after',
						post = ThriveGlobal.$j( '.tve-tl-cnt-wrap' );

					if ( ! post.length ) {
						post = ThriveGlobal.$j( '#tve_editor.tar-main-content' );
					}
					if ( ! post.length ) {
						post = ThriveGlobal.$j( '#post_content' );
					}
					const p = post.find( 'p' ).filter( ':visible' ).not( '.thrv_table p, form p, .tcb-post-list p, .thrv_text_element div p, p.wp-caption-text, .thrv_responsive_video p, .thrv_header p' );

					if ( p.length === 0 && position === 0 ) {
						post.prepend( html );
					} else {
						if ( position === 0 ) {
							position = 1;
							fn = 'before';
						}

						p.eq( position - 1 )[ fn ]( html );
					}
				} else {
					const $placeholder = ThriveGlobal.$j( '.tl-placeholder-f-type-' + elementType );
					if ( response.js[ elementType ] && response.js[ elementType ].content_locking ) {
						/**
						 * content locking shortcode - add the blur class if this is the case
						 * or show the locked content if the user has a conversion registered
						 */
						const $parent = $placeholder.parents( '.tve_content_lock' ).first();

						if ( response.js[ elementType ].has_conversion ) {
							/* remove the placeholder because we're going to display the content. */
							$placeholder.remove();
							$parent.removeClass( 'tve_lock_hide' );
							return true;
						}

						if ( response.js[ elementType ].lock === 'tve_lock_blur' ) {
							$parent.removeClass( 'tve_lock_hide' ).addClass( response.js[ elementType ].lock );
						}
					}

					if ( elementType === 'widget' ) {
						if ( $placeholder.hasClass( 'tl-preload-form' ) ) {
							$placeholder.first().replaceWith( ThriveGlobal.$j( html ).addClass( 'tve-leads-triggered' ) )
						} else {
							$placeholder.replaceWith( html );
							ThriveGlobal.$j( '.tl-widget-container' ).children().unwrap();
						}
					} else {
						$placeholder.replaceWith( html );
					}

					/* for the forms that we going to be added in the page, we're displaying the trigger */
					if ( elementType.indexOf( 'two_step' ) === 0 ) {
						show_trigger( elementType );
					}
				}
			} );
			if ( _ieVersion() > 0 ) {
				/* weird behaviour in IE11 in some cases - style node does not get interpreted after being inserted via AJAX */
				setTimeout( function () {
					var c = '';
					ThriveGlobal.$j( 'body style.tve_custom_style' ).each( function () {
						c = this.innerText;
						this.innerText = c;
					} );
				} );
			}
		}
		if ( response.body_end ) {
			/**
			 * filter the end-of-body contents to remove any (possible) existing wistia embed divs
			 */
			var $body_end = ThriveGlobal.$j( response.body_end );
			$body_end.find( '.tve_wistia_popover' ).each( function () {
				if ( ThriveGlobal.$j( '#' + this.id ).length ) {
					this.parentNode.removeChild( this );
				}
			} );
			/* make sure there are no CSS resources loaded twice */
			$body_end.filter( 'link[href]' ).each( function () {
				if ( ThriveGlobal.$j( 'link[href="' + this.getAttribute( 'href' ) + '"]' ).length ) {
					$body_end = $body_end.not( this );
				}
			} );
			/* make sure there are no JS resources loaded twice */
			$body_end.filter( 'script[src]' ).each( function () {
				if ( ThriveGlobal.$j( 'script[src="' + this.getAttribute( 'src' ) + '"]' ).length ) {
					$body_end = $body_end.not( this );
				}
			} );
			try {
				/**
				 * SUPP-3252, for some reason when the $body_end is appended X-Theme
				 * throws a js error for unknown ajaxurl variable, this try and catch fixes the issue.
				 */
				ThriveGlobal.$j( 'body' ).append( $body_end );
			} catch ( error ) {
				console.log( 'Body append: ' + error );
			}

		}

		if ( typeof response.js.TVO_Form !== 'undefined' ) {
			TVO_Form = response.js.TVO_Form;
		}

		TL_Front.add_page_js( response.res.js, function () {
			resources_loaded = true;
		} );

		/**
		 * rebind the TCB event listeners
		 */
		function dom_ready() {
			if ( ! resources_loaded || ! modulesFinishedLoading ) {
				setTimeout( dom_ready, 50 );
				return;
			}

			TCB_Front.event_triggers( ThriveGlobal.$j( 'body' ) );
			TCB_Front.onDOMReady();
			init();
			ThriveGlobal.$j( TCB_Front ).trigger( 'tl-ajax-loaded' );

			/**
			 * Ribbon compatibility with sticky header from other plugins
			 */
			TCB_Front.add_scroll_callback( function () {
				var $ribbon = ThriveGlobal.$j( '.tve-leads-ribbon[data-position="top"]' ),
					$editor = $ribbon.find( '.tve_shortcode_editor' ),
					visible = $ribbon.is( ':visible' ) && Number( $ribbon.css( 'opacity' ) ),
					height = visible ? Math.max( $ribbon.outerHeight(), $editor.outerHeight() ) : 0,
					$elementorHeader = ThriveGlobal.$j( '[data-elementor-type="header"] .elementor-top-section.elementor-sticky.elementor-sticky--active' );

				/**
				 * fallback in case Stickyheader plugin
				 */
				if ( ! $elementorHeader.length ) {
					$elementorHeader = ThriveGlobal.$j( '.she-header' );
				}

				if ( $elementorHeader.length ) {
					$elementorHeader[ 0 ].style.setProperty( 'margin-top', height + 'px', 'important' );
				}

			} );
		}

		setTimeout( dom_ready, 50 );
		TL_Const.forms = response.js;

		/* After the wait we should search for the placeholders and remove them
		because for hidden displays we get no response and we don't have any way of replacing them with content */
		setTimeout( () => {
			ThriveGlobal.$j( '.tl-preload-form' ).remove();
		}, 1000 );
	};

	function init() {
		if ( ! TL_Const.forms ) {
			return false;
		}

		/**
		 * try to detect the email field from a form and get its value
		 *
		 * @param $form
		 */
		function try_getting_email( $form ) {
			/* if there is a field which requires email validation, use that */
			if ( $form.find( '[data-validation="email"]' ).length ) {
				return $form.find( '[data-validation="email"]' ).val();
			}
			var maybe_email = '';
			/* try search for 'email' in the input name */
			$form.find( 'input' ).each( function () {
				if ( this.name && this.name.match( /email/i ) ) {
					maybe_email = this.value;
					return false;
				}
			} );

			return maybe_email;
		}

		/**
		 * For the fluent form prevent our handling to dont mess up with user's setup
		 * @param $form
		 * @returns {boolean}
		 */
		function isFluentForm( $form ) {
			var id = $form.attr( 'id' ),
				fluentVars = window.fluentFormVars,
				isFluent = false;

			if ( fluentVars && fluentVars.forms ) {
				isFluent = fluentVars.forms.some( function ( formData ) {
					return formData.form_id_selector === id;
				} )
			}
			return isFluent;
		}

		function isHappyForm( $form ) {
			var formID = $form.attr( 'id' );
			return formID && formID.includes( 'happyforms' ) && window.HappyForms;
		}

		/**
		 * listen for the forms submission, and send tracking data requests
		 * the submit listener is delegated just to be sure we can track everything
		 */
		ThriveGlobal.$j( 'body' ).on( 'submit', '.tve-leads-conversion-object form', function ( event ) {
			var $form = ThriveGlobal.$j( this ),
				type = $form.parents( '.tve-leads-conversion-object' ).first().attr( 'data-tl-type' ),
				custom_fields = {};

			if ( $form.data( 'tve-force-submit' ) || $form.closest( '.thrv_custom_html_shortcode' ).length || $form.data( 'tl-do-submit' ) || ! type || ! TL_Const.forms[ type ] || isFluentForm( $form ) || isHappyForm( $form ) ) {
				return true;
			}
			$form.tve_form_loading();

			$form.find( 'input' ).each( function () {
				var $input = ThriveGlobal.$j( this ),
					field = $input.attr( 'name' );
				if ( typeof field !== 'undefined' && TL_Const.ignored_fields.indexOf( field ) === - 1 ) {
					custom_fields[ $input.attr( 'name' ) ] = $input.val();
				}
			} );

			var ajax_data = {
				security: TL_Const.security,
				action: TL_Const.action_conversion,
				type: type,
				tl_data: TL_Const.forms[ type ],
				custom_fields: custom_fields,
				email: try_getting_email( $form ),
				current_screen: TL_Const.current_screen
			};

			ThriveGlobal.$j.each( TL_Const.custom_post_data, function ( k, v ) {
				ajax_data[ k ] = v;
			} );

			/**
			 * XHR synchronous requests on the main threads are deprecated. We need to make it async, and after that trigger the form submission
			 */
			ThriveGlobal.$j.ajax( {
				url: TL_Const.ajax_url,
				data: ajax_data,
				type: 'post',
				xhrFields: {
					withCredentials: true
				}
			} ).always( function () {
				/* When there is no action for the form we reload the page manually so we won't mess up the redirects from WP */
				if ( typeof $form.attr( 'action' ) === 'undefined' ) {
					location.reload();
				} else {
					$form.data( 'tve-force-submit', true ).submit();
				}
			} );

			return false;
		} );

		/**
		 * event listener that allows setting custom post data in forms created with TCB and connected to an API
		 */
		ThriveGlobal.$j( 'body' ).on( 'form_conversion.tcb', '.tve-leads-conversion-object form', function ( event ) {
			var $form = ThriveGlobal.$j( this ),
				type = $form.parents( '.tve-leads-conversion-object' ).first().attr( 'data-tl-type' );

			if ( ! type || ! TL_Const.hasOwnProperty( 'forms' ) || ! TL_Const.forms[ type ] ) {
				return true;
			}

			var ajax_data = {
				type: type,
				tl_data: TL_Const.forms[ type ],
				current_screen: TL_Const.current_screen
			};

			ThriveGlobal.$j.each( TL_Const.custom_post_data, function ( k, v ) {
				ajax_data[ k ] = v;
			} );

			event.post_data = event.post_data || {};
			event.post_data.thrive_leads = ajax_data;
		} ).on( 'lead_conversion_success.tcb', '.tve_lead_lock_shortcode form, .tve_post_lightbox form', function ( event ) {
			var $form = ThriveGlobal.$j( this ),
				$container = $form.parents( '.tve_content_lock' );

			if ( $form.closest( '.tve_post_lightbox' ).length ) {
				/* case: lightbox state of a content-lock shortcode */
				var formType = $form.closest( '.tve-leads-conversion-object' ).attr( 'data-tl-type' );

				/* find out if the lightbox is connected to the shortcode */
				if ( TL_Front.parent_state && TL_Front.parent_state.parent().hasClass( 'tve-leads-track-' + formType ) ) {
					$container = TL_Front.parent_state.closest( '.tve_content_lock' );
				}
			}

			if ( $container.length && $container.hasClass( 'tve_content_lock' ) ) {
				$container.removeClass( 'tve_lead_lock' ).find( '.tve_lead_lock_shortcode' ).remove();
				$container.find( '.tve_lead_locked_overlay' ).remove();

				event.content_unlocked = true;
			}
		} ).on( 'leads_states.tcb', '.tve-leads-conversion-object form', function ( event, content ) {
			var $form = ThriveGlobal.$j( this ),
				_form_type = $form.find( '#_form_type' ).val();

			switch ( _form_type ) {
				case 'ribbon':
					var ribbon = $form.parents( '.tve-leads-ribbon' );
					$form.parents( '.tve_shortcode_editor' ).empty().html( content );
					TL_Front.open_ribbon( ribbon );
					break;
				case 'lightbox':
				case 'tve_lead_2s_lightbox':
					$form.parents( '.tve_p_lb_control' ).empty().html( content );
					break;
				case 'widget':
				case 'in-content':
				case 'post-footer':
				case 'php-insert':
					$form.parents( '.tve_shortcode_editor' ).empty().html( content );
					break;
				case 'slide-in':
					var slide_in = $form.parents( '.tve-leads-slide-in' );
					$form.parents( '.tve_shortcode_editor' ).empty().html( content );
					TL_Front.open_slide_in( slide_in );
					break;
				case 'screen-filler-lightbox':
					var screen_filler = $form.parents( '.tve-leads-screen-filler' );
					$form.parents( '.tve_shortcode_editor' ).empty().html( content );
					TL_Front.open_screen_filler( screen_filler );
					break;
				case 'scroll-mat':
					var ribbon = $form.parents( '.tve-leads-greedy_ribbon' );
					$form.parents( '.tve_shortcode_editor' ).empty().html( content );
					TL_Front.open_ribbon( ribbon );
					break;
				case 'tve_lead_shortcode':
					$form.parents( '.tve-leads-shortcode' ).empty().html( content );
					break;
				case 'lead_generation':
				default:
					event.change_states = false;
					break;
			}

			event.change_states = true;

		} ).on( 'leads_messages.tcb', '.tve-leads-conversion-object form', function ( event ) {
			var $form = ThriveGlobal.$j( this ),
				_form_type = $form.find( '#_form_type' ).val();

			switch ( _form_type ) {
				case 'tve_lead_shortcode':
					if ( event.lightbox_state ) {
						$form.parents( '.tve-leads-shortcode' ).hide();
					}
					break;
				case 'ribbon':
					$form.parents( '.thrv-ribbon' ).find( '.tve-ribbon-close' ).click();
					break;
				case 'lightbox':
				case 'tve_lead_2s_lightbox':
					$form.parents( '.tve_p_lb_content' ).find( '.tve_p_lb_close' ).click();
					$form.parents( '.tve-leads-screen-filler' ).find( '.tve-screen-filler-close' ).click();
					break;
				case 'slide-in':
					$form.parents( '.thrv-leads-slide-in' ).find( '.tve-leads-close' ).click();
					break;
				case 'screen-filler-lightbox':
					$form.parents( '.tve-leads-screen-filler' ).find( '.tve-screen-filler-close' ).click();
					break;
				case 'scroll-mat':
					$form.parents( '.thrv-greedy-ribbon' ).find( '.tve_et_click' ).click();
					break;
				case 'widget':
				case 'in-content':
				case 'post-footer':
				case 'php-insert':
				default:
					$form.find( 'input:not(:hidden):not([type="checkbox"]):not([type="radio"])' ).val( '' );
					break;
			}

			$form.parents( '.tve-leads-conversion-object' ).find( '.tve_ea_thrive_leads_form_close' ).click();

			/* SUPP-2803 - Also close the parent state (e.g. ribbon that triggered a lightbox */
			if ( TL_Front.parent_state ) {
				TL_Front.close_form( TL_Front.parent_state );
			}
		} );
		/**
		 * This code will replace <script type="text/template"> nodes with their content
		 * Used, for example, to execute javascript only when a state is displayed, and not on page load
		 */
		TL_Front.$document.on( 'switchstate', function ( event, container ) {
			container.find( 'script.tcb-lazyload-template' ).each( function () {
				ThriveGlobal.$j( this ).replaceWith( this.innerHTML );
			} );
		} );
	}

	if ( TL_Const.ajax_load ) {

		var resources_loaded = false;

		/* We hide all the triggers for the two step lightbox and display them only if the lightbox is visible */
		var two_step_triggers = ThriveGlobal.$j( '.tve-leads-two-step-trigger' ).hide();

		/**
		 * ajax load all the forms that are to be displayed on this page
		 */
		var ajax_data = {
			tcb_js: typeof TCB_Front !== 'undefined' ? 1 : 0, // do not Load TCB frontend twice
			main_group_id: TL_Const.main_group_id,
			shortcode_ids: TL_Const.shortcode_ids,
			two_step_ids: TL_Const.two_step_ids,
			action: 'tve_leads_ajax_load_forms',
			security: TL_Const.security,
			display_options: TL_Const.display_options,
			current_screen: TL_Const.current_screen
		};
		ThriveGlobal.$j.each( TL_Const.custom_post_data, function ( k, v ) {
			ajax_data[ k ] = v;
		} );

		if ( window.TVE_Dash && ! TVE_Dash.ajax_sent ) {
			ThriveGlobal.$j( document ).on( 'tve-dash.load', function ( event ) {
				TVE_Dash.add_load_item( 'tl_lazy_load', ajax_data, TL_Front.ajax_load_callback );
			} );
		} else {
			ThriveGlobal.$j.ajax( {
				url: TL_Const.ajax_url,
				type: 'post',
				dataType: 'json',
				data: ajax_data,
				xhrFields: {
					withCredentials: true
				}
			} ).done( TL_Front.ajax_load_callback );
		}
		return;
	}
	/**
	 * if there is no ajax_load setting, init stuff as usual
	 */
	init();
} );

TL_Front.switch_lightbox_state = function ( $state_target, $current_state ) {
	$state_target.find( '.tve_p_lb_overlay' ).css( 'opacity', '0.8' );
	$state_target.find( '.tve_p_lb_content' ).css( 'top', $current_state.find( '.tve_p_lb_content' ).css( 'top' ) ).addClass( 'tve-leads-triggered' );
	window.tve_lb_switch_state = true;
	return TL_Front.open_lightbox( $state_target.find( '.tl-lb-target' ) );
};

TL_Front.close_lightbox = function () {
	/**
	 * Clear the parent state in case we have other forms working with lightboxes
	 */
	var $body = ThriveGlobal.$j( 'body' ),
		$html = ThriveGlobal.$j( 'html' ),
		overflow_hidden = 'tve-o-hidden tve-l-open tve-hide-overflow',
		$lightbox = arguments[ 0 ] || $body.data( 'tl-open-lightbox' );
	if ( ! $lightbox || ! $lightbox.length ) {
		return;
	}
	$lightbox.find( '.tve-tl-anim' ).removeClass( 'tve-leads-triggered' );
	if ( ! window.tve_lb_switch_state ) {
		if ( ThriveGlobal.$j( '.tve_lb_open' ).length === 1 ) {
			$body.removeClass( overflow_hidden ).css( 'padding-right', '' );
			$html.removeClass( overflow_hidden );
			if ( $lightbox.data( 'doc-scroll-top' ) ) {
				document.documentElement.scrollTop = $lightbox.data( 'doc-scroll-top' );
				$lightbox.data( 'doc-scroll-top', '' );
			} else if ( $lightbox.data( 'bdy-scroll-top' ) ) {
				document.body.scrollTop = $lightbox.data( 'bdy-scroll-top' );
				$lightbox.data( 'bdy-scroll-top', '' );
			}
		}
		$html.removeClass( $html.data( 'tl-anim-class' ) );
	}
	window.tve_lb_switch_state = false;
	setTimeout( function () {
		$lightbox.addClass( 'tve_lb_closing' );
		$lightbox.removeClass( 'tve_lb_open tve_lb_opening tve_lb_closing tve_p_lb_background' ).css( 'display', 'none' ).find( 'tve_p_lb_content' ).trigger( 'tve.lightbox-close' );
	}, 200 );

	$lightbox.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
		var $this = ThriveGlobal.$j( this );
		$this.attr( 'data-src', $this.attr( 'src' ) );
		$this.attr( 'src', '' );
	} );

	TL_Front.handle_typefocus( $lightbox, 'pause' );

	/**
	 * close any error messages that might have been displayed on forms inside the lightbox
	 */
	ThriveGlobal.$j( '#tve-lg-error-container' ).hide();
};

TL_Front.open_lightbox = function ( $target, TargetEvent ) {
	if ( $target.hasClass( 'tve_lb_open' ) || $target.hasClass( 'tve_lb_opening' ) ) {
		return;
	}

	function getBrowserScrollSize() {
		var $ = ThriveGlobal.$j;
		var css = {
			"border": "none",
			"height": "200px",
			"margin": "0",
			"padding": "0",
			"width": "200px"
		};

		var inner = $( "<div>" ).css( $.extend( {}, css ) );
		var outer = $( "<div>" ).css( $.extend( {
			"left": "-1000px",
			"overflow": "scroll",
			"position": "absolute",
			"top": "-1000px"
		}, css ) ).append( inner ).appendTo( "body" ).scrollLeft( 1000 ).scrollTop( 1000 );

		var scrollSize = {
			"height": (
				          outer.offset().top - inner.offset().top
			          ) || 0,
			"width": (
				         outer.offset().left - inner.offset().left
			         ) || 0
		};

		outer.remove();
		return scrollSize;
	}

	var is_switch_state = window.tve_lb_switch_state;

	ThriveGlobal.$j.fn.thrive_iphone_placeholder && $target.find( 'input[placeholder]' ).thrive_iphone_placeholder();

	/* close any other opened lightboxes */
	TL_Front.close_lightbox( ThriveGlobal.$j( '.tve_p_lb_background.tve_lb_open' ) );

	//$target.css('display', '').parents('.tl-style').css('display', '');
	$target.css( {
		'visibility': '',
		'position': '',
		'left': '',
		'display': ''
	} ).parents( '.tl-style' ).css( {
		'visibility': '',
		'position': '',
		'left': '',
		'display': ''
	} );

	var $body = ThriveGlobal.$j( 'body' ),
		$html = ThriveGlobal.$j( 'html' ),
		overflow_hidden = 'tve-o-hidden tve-l-open tve-hide-overflow',
		scroll_width = getBrowserScrollSize().width,
		oPadding = parseInt( $body.css( 'paddingRight' ) ),
		has_lb_open = ThriveGlobal.$j( '.tve_p_lb_background.tve_lb_open' ).length;

	if ( isNaN( oPadding ) ) {
		oPadding = 0;
	}

	$target.find( '.tve_p_lb_close' ).off().on( "click", function () {
		TL_Front.close_lightbox();
		return false;
	} );

	$body.off( 'keyup.tve_lb_close' ).on( 'keyup.tve_lb_close', function ( e ) {
		if ( e.which == 27 ) {
			TL_Front.close_lightbox();
			return false;
		}
	} );

	$target.find( '.tve_p_lb_overlay' ).off( 'click.tve_lb_close' ).on( 'click.tve_lb_close', function () {
		TL_Front.close_lightbox();
		return false;
	} );

	$body.data( 'tl-open-lightbox', $target );

	$target.addClass( 'tve_p_lb_background' );
	$target.data( 'doc-scroll-top', document.documentElement.scrollTop );
	$target.data( 'bdy-scroll-top', document.body.scrollTop );

	$body.addClass( overflow_hidden );
	$html.addClass( overflow_hidden );

	var wHeight = ThriveGlobal.$j( window ).height(),
		page_has_scroll = wHeight < ThriveGlobal.$j( document ).height();

	if ( ! is_switch_state && page_has_scroll ) {
		$body.css( 'padding-right', (
		                            oPadding + scroll_width
		                            ) + 'px' );
	}

	//load the responsive video iframes
	$target.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
		var $this = jQuery( this );
		if ( $this.attr( 'data-src' ) ) {
			$this.attr( 'src', $this.attr( 'data-src' ) );
		}
	} );

	$target.find( '.thrv_responsive_video' ).each( function () {
		var $this = ThriveGlobal.$j( this );

		TCB_Front.makeAutoplayVideo( $this );
	} );

	var cls = '';
	/* append the animation class to the html element */
	ThriveGlobal.$j.each( $target.parents( '.tl-states-root' ).attr( 'class' ).split( ' ' ), function ( i, c ) {
		if ( c.indexOf( 'tl-anim' ) === 0 ) {
			cls = c;
			return false;
		}
	} );
	$html.addClass( cls ).data( 'tl-anim-class', cls );

	setTimeout( function () {

		setTimeout( function () {
			$target.addClass( 'tve_lb_opening' );
		}, 0 );

		/* reload any iframe that might be in there, this was causing issues with google maps embeds in hidden tabs */
		$target.find( 'iframe' ).not( '.thrv_social_default iframe' ).each( function () {
			var $this = ThriveGlobal.$j( this );
			if ( $this.data( 'tve_ifr_loaded' ) || ! $this.attr( 'data-src' ) ) {
				return;
			}
			$this.data( 'tve_ifr_loaded', 1 ).attr( 'src', $this.attr( 'data-src' ) );
		} );

		function position_it() {
			var cHeight = $target.find( '.tve_p_lb_content' ).outerHeight( true ) + (
					2 * parseInt( $target.css( 'padding-top' ) )
				),
				$lContent = $target.find( '.tve_p_lb_content' ),
				wHeight = ThriveGlobal.$j( window ).height(),
				top = (
					      wHeight - cHeight
				      ) / 2;

			$target.find( '.tve_p_lb_overlay' ).css( {
				height: (
				        cHeight + 80
				        ) + 'px',
				'min-height': wHeight + 'px'
			} );
			if ( has_lb_open ) {
				$lContent.animate( {
					top: (
						top < 40 ? 40 : top
					)
				}, 100 );
			} else {
				$lContent.css( 'top', (
					                      top < 40 ? 40 : top
				                      ) + 'px' );
			}
			if ( cHeight + 40 > wHeight ) {
				$target.addClass( 'tve-scroll' );
			}
		}

		TCB_Front.resizePageSection();
		position_it();
		ThriveGlobal.$j( window ).on( 'resize', position_it );

	}, 20 );

	setTimeout( function () {
		$target.removeClass( 'tve_lb_opening' ).addClass( 'tve_lb_open' ).find( '.tve_p_lb_content' ).trigger( 'tve.lightbox-open' );
		ThriveGlobal.$j( window ).trigger( 'scroll' );
	}, 300 );

	// called to stop other events that might interact in the bad way with the lightbox, like a button that redirects the page.
	if ( TargetEvent && TargetEvent.preventDefault ) {
		TargetEvent.preventDefault();
		TargetEvent.stopPropagation();
	}
	$target.parents( '.tl-states-root' ).off( 'switchstate' ).on( 'switchstate', function ( e, $state ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		TL_Front.switch_lightbox_state.apply( TL_Front, args );
	} );

	/**
	 * If exit intent, do not open this again at exit intent
	 */
	if ( TargetEvent && TargetEvent.tve_trigger === 'exit' ) {
		$target.data( 'shown-on-exit', true );
	}

	/* trigger a custom event after we opened the lightbox */
	TCB_Front.$window.trigger( 'tl_after_lightbox_open', $target );

	/* make sure that its trigger after the animation is done too */
	setTimeout( () => {
		TCB_Front.$window.trigger( 'tve_after_content_toggle', [ $target ] );
	}, 500 );
};

TL_Front.open_two_step_lightbox = TL_Front.open_lightbox;

TL_Front.open_ribbon = function ( $target ) {

	/**
	 * used when there is also a Scroll mat on the page, this will make sure the ribbon is opened after the greedy ribbon
	 */
	function open_it() {
		$target.addClass( 'tve-leads-triggered' );
		const $editor = $target.find( '.tve_shortcode_editor' ),
			editorHeight = $editor.length ? $editor.outerHeight() : 0,
			position = $target.attr( 'data-position' ) || 'top';

		switch ( position ) {
			case 'above':
				$target.css( 'bottom', 'auto' );
			case 'top':
				$target.css( 'top', ThriveGlobal.$j( '#wpadminbar' ).length ? '32px' : '0px' );
				break;

			case 'bottom':
				$target.css( 'bottom', '0px' );
				$target.css( 'top', 'auto' );
				break;
			default:
				break;
		}

		/**
		 * Mozilla is really slow at applying the loaded css. we need this workaround to have it work in mozilla.
		 */
		let iterations = 0,
			initialHeight = Math.max( $target.outerHeight(), editorHeight ),
			ii = setInterval( function () {
				iterations ++;
				const _h = Math.max( $target.outerHeight(), editorHeight );
				if ( _h != initialHeight || iterations == 10 ) {
					clearInterval( ii );
				}
				if ( position === 'top' ) {
					ThriveGlobal.$j( 'body' ).animate( {'margin-top': _h + 'px'}, 200, function () {
						document.body.style.setProperty( 'margin-top', _h + 'px', 'important' );
					} );

					if ( TCB_Front && TCB_Front.$window ) {
						TCB_Front.$window.trigger( 'scroll' );
					}
				} else if ( position === 'bottom' ) {
					ThriveGlobal.$j( 'body' ).animate( {'margin-bottom': _h + 'px'}, 200 );
				}
			}, 100 );

		const $header = ThriveGlobal.$j( 'body' ).find( '.thrv_header.tve-scroll-sticky' );

		if ( $header.length && position === 'top' ) {
			const extraOffset = parseFloat( TCB_Front.inlineCssVariable( $header, '--tcb-header-extra-offset' ) ) || 0;

			TCB_Front.inlineCssVariable( $header, '--tcb-header-extra-offset', extraOffset + initialHeight + 'px' );
		}

		$target.off( 'switchstate' ).on( 'switchstate', function ( e, $target ) {
			const args = Array.prototype.slice.call( arguments, 1 );
			TL_Front.switch_ribbon_state.apply( TL_Front, args );
		} );
	}

	if ( TL_Const.forms.greedy_ribbon ) {
		TL_Const.close_callbacks = TL_Const.close_callbacks || {};
		TL_Const.close_callbacks.greedy_ribbon = [ open_it ];
	} else {
		open_it();
	}
};

TL_Front.switch_ribbon_state = function ( $target ) {
	var h = $target.outerHeight( true ),
		prop = $target.parent().attr( 'data-position' ) === 'top' ? 'margin-top' : 'margin-bottom',
		animation = {};
	animation[ prop ] = h + 'px';
	$target.find( '.tve-tl-anim' ).removeClass( function ( index, className ) {
		return ( className.match( /(^|\s)tl-anim-\S+/g ) || [] ).join( ' ' );
	} ).removeClass( 'tve-tl-anim' );
	ThriveGlobal.$j( 'body' ).animate( animation, 200 );

};

TL_Front.open_greedy_ribbon = function ( $target ) {
	const $body = ThriveGlobal.$j( 'body' ),
		$window = ThriveGlobal.$j( window ),
		initial_position = $body.css( 'position' );

	$window.scrollTop( 0 );
	//for situations where the user's theme adds a position css on body
	$body.css( 'position', 'static' );
	$body.addClass( 'tve-tl-gr-anim' );

	TCB_Front.$window.trigger( 'scroll' );
	$target.css( 'top', ThriveGlobal.$j( '#wpadminbar' ).length ? '32px' : '0px' );
	const wHeight = $target.outerHeight();
	$body[ 0 ].style.setProperty( 'margin-top', wHeight + 'px', 'important' );
	let greedyCondition = 1;
	setTimeout( function () {
		ThriveGlobal.$j( '.tve-leads-ribbon[data-position="top"]' ).removeClass( 'tve-leads-triggered' );
	}, 50 );

	$window.scroll( function () {
		const isFormOpen = $body.hasClass( 'tve-tl-gr-anim' );
		if ( greedyCondition && isFormOpen ) {
			const browserScroll = $window.scrollTop();
			if ( browserScroll > wHeight ) {
				const hasWistiaPopover = $target.find( '.tve_ea_thrive_wistia' ).length || $target.find( '.tve_with_wistia_popover' );
				if ( hasWistiaPopover ) {
					ThriveGlobal.$j( '.wistia_placebo_close_button' ).trigger( 'click' );
				}
				$body.removeClass( 'tve-tl-gr-anim' );
				$target.addClass( 'tve-no-animation' );
				const greedyScroll = browserScroll - wHeight;
				$target.removeClass( 'tve-leads-triggered' );
				$target.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
					const $this = ThriveGlobal.$j( this );
					$this.attr( 'data-src', $this.attr( 'src' ) );
					$this.attr( 'src', '' );
				} );
				$body.css( 'margin-top', '' );
				$body.css( 'position', initial_position );
				$window.scrollTop( greedyScroll );
				$target.removeClass( 'tve-no-animation' );
				greedyCondition = 0;
				TL_Front.form_closed( 'greedy_ribbon' );
				TL_Const.forms.greedy_ribbon.allow_callbacks = false;
			}
		}
	} );
	$target.off( 'switchstate' ).on( 'switchstate', function ( e, $target ) {

	} );
};

TL_Front.open_screen_filler = function ( $target, TargetEvent ) {
	var overflow_hidden = 'tve-so-hidden tve-sl-open tve-s-hide-overflow',
		html_body = ThriveGlobal.$j( 'html,body' ),
		$html = ThriveGlobal.$j( 'html' );

	ThriveGlobal.$j.fn.thrive_iphone_placeholder && $target.find( 'input[placeholder]' ).thrive_iphone_placeholder();

	$target.css( 'top', ThriveGlobal.$j( '#wpadminbar' ).length ? '32px' : '0px' ).css( 'visibility', '' );
	if ( $target.hasClass( 'stl-anim-slip_from_top' ) === true ) {
		var browserScroll = ThriveGlobal.$j( window ).scrollTop();
		$target.css( 'top', browserScroll ).css( 'visibility', '' );
	}

	$target.find( '.tve-screen-filler-close' ).on( 'click', function () {
		close_it( $target );
	} );

	$target.data( 'doc-scroll-top', document.documentElement.scrollTop );
	$target.data( 'bdy-scroll-top', document.body.scrollTop );

	/**
	 * Prevent screen filler to hide page overflow for displays where its content is hidden
	 */
	var $innerContent = $target.find( '.thrv-leads-screen-filler.thrv_wrapper ' );
	if ( $innerContent.css( 'display' ) !== 'none' ) {
		html_body.addClass( overflow_hidden );
	}

	var cls = '';
	/* append the animation class to the html element */
	ThriveGlobal.$j.each( $target.attr( 'class' ).split( ' ' ), function ( i, c ) {
		if ( c.indexOf( 'stl-anim' ) === 0 ) {
			cls = c;
			return false;
		}
	} );
	$html.addClass( cls ).data( 'tl-s-anim-class', cls );

	function close_it( $screen_filler ) {

		$screen_filler.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
			var $this = ThriveGlobal.$j( this );
			$this.attr( 'data-src', $this.attr( 'src' ) );
			$this.attr( 'src', '' );
		} );

		$screen_filler.removeClass( 'tve-leads-triggered' );
		TL_Front.handle_typefocus( $screen_filler, 'pause' );
		ThriveGlobal.$j( document ).off( 'keyup.close-screenfiller' );
		if ( ThriveGlobal.$j.find( '.tve-leads-ribbon' ).length == 0 ) {
			ThriveGlobal.$j( 'body' ).animate( {'margin-top': 0 + 'px'}, 200 );
		}
		html_body.removeClass( overflow_hidden );
		$html.removeClass( $html.data( 'tl-s-anim-class' ) );
		setTimeout( function () {
			$screen_filler.css( 'top', '' ).hide();
			if ( $screen_filler.css( 'display' ) === 'none' ) {
				html_body.removeClass( overflow_hidden );
				$screen_filler.removeClass( 'tve-leads-triggered' );
			}
		}, 400 );
		if ( $screen_filler.data( 'doc-scroll-top' ) ) {
			document.documentElement.scrollTop = $screen_filler.data( 'doc-scroll-top' )
			$screen_filler.data( 'doc-scroll-top', '' );
		} else if ( $screen_filler.data( 'bdy-scroll-top' ) ) {
			document.body.scrollTop = $screen_filler.data( 'bdy-scroll-top' );
			$screen_filler.data( 'bdy-scroll-top', '' )
		}

		/**
		 * close any error messages that might have been displayed on forms inside the lightbox
		 */
		ThriveGlobal.$j( '#tve-lg-error-container' ).hide();
	}

	//each time a screen filler is opened then it should open with the default(1st state) or already subscribed state SUPP-6469
	var $states = $target.find( '> .tl-style' ).hide(),
		$already_subscribed = $target.find( '> .tl-style[data-form-state="already_subscribed"]' );
	if ( $already_subscribed.length ) {
		$already_subscribed.show();
	} else {
		$states.first().show();
	}

	TL_Front.close_screen_filler = close_it;

	// called to stop other events that might interact in the bad way with the lightbox, like a button that redirects the page.
	if ( TargetEvent && TargetEvent.preventDefault ) {
		TargetEvent.preventDefault();
		TargetEvent.stopPropagation();
	}

	ThriveGlobal.$j( document ).off( 'keyup.close-screenfiller' ).on( 'keyup.close-screenfiller', function ( e ) {
		if ( e.which == 27 ) {
			close_it( $target );
		}
	} );

	//load the responsive video iframes
	$target.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
		var $this = jQuery( this );
		if ( $this.attr( 'data-src' ) ) {
			$this.attr( 'src', $this.attr( 'data-src' ) );
		}
	} );
	setTimeout( () => {
		TCB_Front.resizePageSection();
	}, 500 )
};

TL_Front.switch_slide_in_state = function ( $state ) {
	$state.find( '.tve-tl-anim' ).removeClass( function ( index, className ) {
		return ( className.match( /(^|\s)tl-anim-\S+/g ) || [] ).join( ' ' );
	} ).removeClass( 'tve-tl-anim' );
	TL_Front.slide_in_position( $state.find( '.thrv-leads-slide-in' ) );
};
TL_Front.slide_in_position = function ( $lContent ) {
	var $window = ThriveGlobal.$j( window ),
		elHeight = $lContent.outerHeight();
	if ( $window.width() <= 782 || $window.height() < elHeight ) {
		$lContent.parents( '.tve-leads-slide-in' ).addClass( 'tve-lb' ); // display it as a lightbox
		var wHeight = $window.height();

		setTimeout( function () {
			let top;

			//set the top value according to the position set for the element
			if ( $lContent.parents( '.tve-leads-slide-in' ).is( '[class*="bot"]' ) ) {
				top = wHeight - elHeight; //bottom
			} else {
				top = 0; //top
			}

			$lContent.closest( '.tve-leads-slide-in' )
			         .data( 'doc-scroll-top', document.documentElement.scrollTop )
			         .data( 'bdy-scroll-top', document.body.scrollTop );

			$lContent.parents( '.tve-leads-conversion-object' ).first().css( {
				height: ( elHeight + 80 ) + 'px',
				'min-height': wHeight + 'px'
			} );

			$lContent.css( 'top', ( top < 40 ? 40 : top ) + 'px' );
			if ( elHeight + 40 > wHeight ) {
				$lContent.parents( '.tve-leads-slide-in' ).css( 'overflow-y', 'scroll' );
			}
		}, 0 );
	}
};
TL_Front.open_slide_in = function ( $target, TargetEvent ) {
	var overflow_hidden = 'tve-o-hidden tve-l-open tve-hide-overflow',
		$body = ThriveGlobal.$j( 'body' ),
		$html = ThriveGlobal.$j( 'html' );

	ThriveGlobal.$j.fn.thrive_iphone_placeholder && $target.find( 'input[placeholder]' ).thrive_iphone_placeholder();

	TL_Front.slide_in_position( $target.find( '.thrv-leads-slide-in' ).filter( ':visible' ) );

	function close_it( $slidein ) {
		$slidein.removeClass( 'tve-leads-triggered' );
		TL_Front.handle_typefocus( $slidein, 'pause' );
		ThriveGlobal.$j( document ).off( 'keyup.close-slidein' );
		/* If we still have a lightbox open on the page we don't need to remove the overflow hidden classes */
		if ( ! ThriveGlobal.$j( '.tve_lb_open' ).length ) {
			$body.removeClass( overflow_hidden );
			$html.removeClass( overflow_hidden );
		}
		$slidein.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
			var $this = ThriveGlobal.$j( this );
			$this.attr( 'data-src', $this.attr( 'src' ) );
			$this.attr( 'src', '' );
		} );
		/**
		 * close any error messages that might have been displayed on forms inside the lightbox
		 */
		ThriveGlobal.$j( '#tve-lg-error-container' ).hide();
		if ( $target.data( 'doc-scroll-top' ) ) {
			document.documentElement.scrollTop = $target.data( 'doc-scroll-top' );
			$target.data( 'doc-scroll-top', '' );
		} else if ( $target.data( 'bdy-scroll-top' ) ) {
			document.body.scrollTop = $target.data( 'bdy-scroll-top' );
			$target.data( 'bdy-scroll-top', '' );
		}
	}

	$target.off().on( 'click', '.tve-leads-close', function () {
		close_it( $target );
	} );
	$target.find( '.tve_ea_thrive_leads_form_close' ).on( 'click', function () {
		close_it( $target );
	} );
	$target.on( 'switchstate', function ( e, $state ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		TL_Front.switch_slide_in_state.apply( TL_Front, args );
	} );
	ThriveGlobal.$j( document ).off( 'keyup.close-slidein' ).on( 'keyup.close-slidein', function ( e ) {
		if ( e.which == 27 ) {
			close_it( $target );
		}
	} );

	if ( TargetEvent && TargetEvent.preventDefault ) {
		TargetEvent.preventDefault();
		TargetEvent.stopPropagation();
	}
};

TL_Front.close_form = function ( element, trigger, action, config ) {
	var $element = ThriveGlobal.$j( element ),
		$parent = $element.parents( '.tve-leads-triggered' ), //every form has this class on its wrapper
		type = $parent.attr( 'data-tl-type' ); //some of them have its type in data-tl-type

	//if no type then identify it from class
	if ( type === undefined && $parent.hasClass( 'tve-leads-widget' ) ) {
		type = 'widget';
	} else if ( type === undefined && $parent.hasClass( 'tve-leads-post-footer' ) ) {
		type = 'post-footer';
	} else if ( type === undefined && $parent.hasClass( 'tve-leads-slide-in' ) ) {
		type = 'slide-in';
	} else if ( type === undefined && $parent.hasClass( 'tve-leads-in-content' ) ) {
		type = 'in-content';
	} else if ( type === undefined && $parent.hasClass( 'tve-leads-shortcode' ) ) {
		type = 'shortcode';
	} else if ( type === undefined && $parent.hasClass( 'tve-leads-greedy_ribbon' ) ) {
		type = 'greedy_ribbon';
	} else if ( type === undefined && $parent.hasClass( 'tve_p_lb_content' ) ) {
		TL_Front.close_lightbox();
		return false;
	} else if ( type === undefined && $parent.hasClass( 'tve-leads-screen-filler' ) ) {
		TL_Front.close_screen_filler( $parent );
		return false;
	}

	//remove this class just because it is added dynamically and maybe we want to trigger it again
	$parent.removeClass( 'tve-leads-triggered' );
	TL_Front.handle_typefocus( $parent, 'pause' );
	switch ( type ) {
		case 'ribbon':
			let $close = $parent.find( '.tve-ribbon-close' );
			if ( ! $close.length ) {
				$close = jQuery( '<span class="tve-ribbon-close" style="display: none"></span>' ).appendTo( $parent );
			}
			$close.trigger( 'click' );//there already exists a bind for close

			const $header = ThriveGlobal.$j( 'body' ).find( '.thrv_header.tve-scroll-sticky' );

			if ( $header.length && $parent.attr( 'data-position' ) === 'top' ) {
				const extraOffset = ( parseFloat( TCB_Front.inlineCssVariable( $header, '--tcb-header-extra-offset' ) ) - $parent.outerHeight( true ) ) + 'px'

				TCB_Front.inlineCssVariable( $header, '--tcb-header-extra-offset', extraOffset );
			}
			break;
		case 'slide-in':
			$parent.find( '.tve_ea_thrive_leads_form_close' ).trigger( 'click' );//there already exists a bind for close
			$parent.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
				var $this = ThriveGlobal.$j( this );
				$this.attr( 'data-src', $this.attr( 'src' ) );
				$this.attr( 'src', '' );
			} );
			break;
		case 'post-footer'://case able for PHP Insert form too
		case 'in-content':
		case 'shortcode':
			$parent.fadeOut( 200, function () {
				TL_Front.form_closed( type );
			} );
			break;
		case 'widget':
			$parent.parent().slideUp( 200 );//its parent is a section tag; see the function thrive_dynamic_sidebar_params()
			break;
		case 'greedy_ribbon':
			var $body = ThriveGlobal.$j( 'body' ),
				$window = ThriveGlobal.$j( window ),
				_tempMargin = $body.css( 'margin-top' );
			$body[ 0 ].style.removeProperty( 'margin-top' );
			$parent.find( '.thrv_responsive_video iframe, .thrv_custom_html_shortcode iframe, .thrv_responsive_video video' ).each( function () {
				var $this = ThriveGlobal.$j( this );
				$this.attr( 'data-src', $this.attr( 'src' ) );
				$this.attr( 'src', '' );
			} );
			$body.css( 'margin-top', _tempMargin );
			$window.scrollTop( 0 );
			$body.animate( {'margin-top': 0 + 'px'}, 300, 'linear', function () {
				TL_Front.form_closed( type );
			} ).removeClass( 'tve-tl-gr-anim' );

			break;
	}
};

TL_Front.form_closed = function ( type ) {
	if ( TL_Const.close_callbacks && TL_Const.close_callbacks[ type ] ) {
		ThriveGlobal.$j.each( TL_Const.close_callbacks[ type ], function ( i, f ) {
			if ( ThriveGlobal.$j.isFunction( f ) ) {
				f();
			}
		} );
	}
};

TL_Front.handle_typefocus = function ( element, action ) {
	element.find( '.tve_typefocus' ).each( function () {
		var $this = jQuery( this );
		$this.attr( 'data-typefocus', action );
	} );
};
