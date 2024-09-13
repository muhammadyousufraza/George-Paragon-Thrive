( function () {
	if ( window.TCB_Front ) {
		TCB_Front.course = require( './elements/course' );
		TCB_Front.certificate_verification = require( './elements/certificate-verification' );
		TCB_Front.assessment = require( './elements/assessment' );

		TCB_Front.course.init();
		TCB_Front.certificate_verification.init();
		TCB_Front.assessment.init();

		/**
		 * The course list class extends the post list class from TCB, so we do this check before initializing the course list functionality
		 * TCB_Front.PostList is undefined only if JS optimizations are enabled and there are no post lists or course lists on the page
		 */
		if ( typeof TCB_Front.PostList !== 'undefined' ) {
			TCB_Front.courseList = require( './elements/course-list' )( jQuery, TCB_Front );

			TCB_Front.courseList.initCourseLists();
		}

		if ( ! tve_frontend_options.is_editor_page ) {
			ThriveGlobal.$j( '[data-dynamic-link="tva_stripe_url"]' ).each( ( index, element ) => {
				if ( ! element.getAttribute( 'href' ) ) {
					element.classList.add( 'tcb-permanently-hidden' );
				}
			} )
		}
	}
} )( ThriveGlobal.$j );
