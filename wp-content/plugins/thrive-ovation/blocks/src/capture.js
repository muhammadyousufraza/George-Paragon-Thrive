import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { withSelect } from '@wordpress/data';
import { captureLogo } from './logos';
import { renderSidebar, renderBlock } from './utils';

/* global TVO_Data */

registerBlockType( 'thrive/ovation-capture', {
	title: __( 'Capture Testimonial', 'thrive-ovation' ),
	icon: captureLogo,
	description: __( 'Add testimonials to your content!', 'thrive-ovation' ),
	category: 'thrive',
	attributes: {
		selectedBlock: {
			type: 'number',
			default: 0,
		},
		previewImage: {
			type: 'boolean',
			default: false,
		},
	},
	example: {
		attributes: {
			previewImage: true,
		},
	},
	edit: withSelect( function( select ) {
		const query = {
			per_page: -1,
		};
		return {
			posts: select( 'core' ).getEntityRecords(
				'postType',
				'tvo_capture',
				query
			),
		};
	} )( function( props ) {
		if ( props?.attributes.previewImage ) {
			return [
				wp.element.createElement( 'img', {
					src: TVO_Data.capture_preview,
				} ),
			];
		}

		if ( props.attributes.selectedBlock ) {
			return renderSidebar( props, 'Edit Testimonial capture form' );
		}
		return renderBlock(
			props,
			'Capture Testimonial',
			'Setup your testimonial capture form',
			captureLogo,
			'tvo_capture'
		);
	} ),
	save: () => {
		return null;
	},
} );
