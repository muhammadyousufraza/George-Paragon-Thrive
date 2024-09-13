const utils = require( './utils' ),
	component = require( './component' ),
	constants = require( './constants' ),
	content = require( './content' ),
	editMode = require( './edit-mode' );

/* initialize the hooks */
const files = {
	cloud: require( './hooks/cloud' ),
	css: require( './hooks/css' ),
	dynamicImage: require( './hooks/dynamic-image' ),
	general: require( './hooks/general' ),
	inlineShortcodes: require( './hooks/inline-shortcodes' ),
	sync: require( './hooks/sync' ),
};

/* change priorities for hooks that we want executed earlier / later ( default is 10 ) */
const priorities = {
	'tcb_head_css_prefix': 9,
};
/* For each file that contains hooks, add the actions and filters. */
_.each( files, file => {
	TVE.addHooks( file, priorities );
} );

constants.htmlChangeActions.forEach( action => TVE.add_action( action, content.checkForDisplayTestimonialsSync ) );

if ( typeof TVE.displayTestimonials === 'undefined' ) {
	TVE.displayTestimonials = {
		FLAGS: {
			IS_SYNCING: false,
		},
	};
}

module.exports = {
	component,
	constants,
	content,
	editMode,
	utils,
};
