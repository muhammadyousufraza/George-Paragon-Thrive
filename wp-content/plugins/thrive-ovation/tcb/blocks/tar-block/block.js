import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { withSelect } from '@wordpress/data';
import tarLogo from './logo';
import previewBlock from './block-preview';
import renderAddBlock from './add-block';
import { maxNrOfPosts } from './utils';

/* global TCB_Post_Edit_Data */
/* global TAR_Block */

registerBlockType( 'thrive/architect-block', {
	title: __( 'Thrive Architect Block', 'thrive-cb' ),
	icon: tarLogo,
	description: __(
		'Add Thrive templates, symbols and blocks to your content!',
		'thrive-cb'
	),
	category: 'thrive',
	attributes: {
		selectedBlock: {
			type: 'number',
			default: 0,
		},
		blockTitle: {
			type: 'string',
			default: '',
		},
		searchText: {
			type: 'string',
			default: '',
		},
		searchBlockSel: {
			type: 'string',
			default: '',
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

	edit: withSelect( function( select, props ) {
		const searchTextSel = props.attributes.searchBlockSel,
			query = {
				per_page: maxNrOfPosts(),
				search: searchTextSel,
				tcb_symbols_tax_exclude: TCB_Post_Edit_Data.sections_tax_terms.map(
					( term ) => term.term_id
				),
			};
		return {
			posts: select( 'core' ).getEntityRecords(
				'postType',
				'tcb_symbol',
				query
			),
		};
	} )( function( props ) {
		if ( props.attributes.previewImage ) {
			return [
				wp.element.createElement( 'img', {
					src: TAR_Block.block_preview,
				} ),
			];
		}

		if ( props.attributes.selectedBlock ) {
			return previewBlock( props );
		}

		return renderAddBlock( props );
	} ),
	// How our block renders on the frontend
	save: () => {
		return null;
	},
} );
