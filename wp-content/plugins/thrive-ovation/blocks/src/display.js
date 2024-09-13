import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { withSelect } from '@wordpress/data';
import { displayLogo } from './logos';
import { renderBlock, renderSidebar } from './utils';
/* global TVO_Data */

registerBlockType( 'thrive/ovation-display', {
	title: __( 'Display Testimonial', 'thrive-ovation' ),
	icon: displayLogo,
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
				'tvo_display',
				query
			),
		};
	} )( function( props ) {
		if ( props.attributes.previewImage ) {
			return [
				wp.element.createElement( 'img', {
					src: TVO_Data.display_preview,
				} ),
			];
		}

		if ( props.attributes.selectedBlock ) {
			return renderSidebar( props, 'Edit Testimonial display' );
		}
		return renderBlock(
			props,
			'Display Testimonial',
			'Setup your testimonial display element',
			displayLogo,
			'tvo_display'
		);
	} ),
	save: () => {
		return null;
	},
} );
