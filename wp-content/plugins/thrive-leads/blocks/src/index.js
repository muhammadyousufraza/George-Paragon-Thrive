/* global TL_Data */
import {__} from '@wordpress/i18n';
import {registerBlockType} from '@wordpress/blocks';
import {PanelBody, PanelRow} from '@wordpress/components';
import {withSelect} from '@wordpress/data';
import {InspectorControls} from '@wordpress/block-editor';
import tlLogo from './logo';
import {tveOuterHeight, getPreviewLink, renderSelect} from './utils';

const createElement = wp.element.createElement;

registerBlockType( 'thrive/leads-block', {
	title: __( 'Thrive Leads Shortcode', 'thrive-leads' ),
	icon: tlLogo,
	description: __( 'Add Thrive Lead Form to your content!', 'thrive-leads' ),
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

	edit: withSelect( function ( select ) {
		const query = {
			per_page: - 1,
		};
		return {
			posts: select( 'core' ).getEntityRecords(
				'postType',
				'tve_lead_shortcode',
				query
			),
		};
	} )( function ( props ) {
		const postId = props.attributes.selectedBlock,
			className = props.className;

		let previewLink = props.attributes.previewLink;

		if ( props.attributes.previewImage ) {
			return [
				wp.element.createElement( 'img', {
					src: TL_Data.block_preview,
				} ),
			];
		}


		if ( ! props.posts ) {
			return __( 'Loading …', 'thrive-leads' );
		}
		const options = [
			{
				value: 0,
				label: __( '-- Select a lead shortcode --', 'thrive-leads' ),
			},
		];

		props.posts.forEach( ( post ) => {
			options.push( {value: post.id, label: post.title.raw} );
		} );

		const selectedPost = props.posts.find(
			( post ) =>
				Number( post.id ) === Number( props.attributes.selectedBlock )
		);
		if ( selectedPost ) {
			props.setAttributes( {
				previewLink: selectedPost.preview_variation,
			} );
		}

		if ( postId ) {
			if ( previewLink ) {
				previewLink = getPreviewLink( previewLink );
			}

			return [
				createElement(
					InspectorControls,
					null,

					createElement(
						PanelBody,
						{
							title: __( 'Block settings', 'thrive-leads' ),
							initialOpen: true,
						},
						createElement(
							PanelRow,
							{},
							createElement(
								'a',
								{
									class: 'tl-sidebar-edit-button',
									href: TL_Data.edit_url + postId,
									target: '_blank',
								},
								__( 'Edit Thrive Leads shortcode', 'thrive-leads' )
							)
						),
						createElement(
							PanelRow,
							{},
							renderSelect(
								options,
								props,
								__( 'Change Thrive Leads shortcode', 'thrive-leads' )
							)
						)
					)
				),
				createElement( 'iframe', {
					src: previewLink,
					id: `tl-block-${postId}`,
					class: `${className} architect-block-preview tl-block-${postId}`,
					scrolling: 'no',
					onLoad() {
						const iframes = document.getElementsByClassName(
							`tl-block-${postId}`
						);

						/**
						 * in case of duplicate / copy-paste
						 */
						Array.prototype.forEach.call( iframes, ( iframe ) => {
							const iframeDocument = iframe.contentDocument,
								setHeight = () => {
									const height = tveOuterHeight(
										iframeDocument.body
									);
									iframe.style.setProperty(
										'height',
										`${height}px`
									);
									iframe.parentNode.style.setProperty(
										'height',
										`${height}px`
									);
								};

							iframe.style.setProperty(
								'pointer-events',
								'none'
							);
							setHeight();

							setTimeout( () => {
								setHeight();
							}, 2000 );

							if ( iframe.contentWindow.TVE_Dash ) {
								iframe.contentWindow.TVE_Dash.forceImageLoad(
									iframeDocument
								);
							}
						} );
					},
				} ),
			];
		}
		return [
			createElement(
				'div',
				{
					class: 'tl-new-block-container',
				},
				tlLogo(),
				createElement(
					'div',
					{
						class: 'tl-block-title',
					},
					createElement( 'h2', {}, 'Thrive Leads Shortcode' )
				),
				createElement(
					'div',
					{class: 'tl-block-content'},
					createElement(
						'div',
						{
							class: 'tl-new-block-description',
						},
						__( 'Choose your Thrive Leads Forms', 'thrive-leads' )
					),
					renderSelect( options, props, '' )
				),
				createElement(
					'div',
					{
						class: 'tl-go-dash',
					},
					__( 'Go to the ', 'thrive-leads' ),
					createElement(
						'a',
						{
							href: TL_Data.dashboard_url,
							target: '_blank',
						},
						__( 'Leads Shortcodes Dashboard', 'thrive-leads' )
					)
				)
			),
		];
	} ),
	// How our block renders on the frontend
	save: () => {
		return null;
	},
} );
