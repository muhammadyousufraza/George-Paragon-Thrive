/* global TQB_Data */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, PanelRow } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import tqbLogo from './logo';
import { tveOuterHeight, getPreviewLink, renderSelect } from './utils';

const createElement = wp.element.createElement;

registerBlockType( 'thrive/quiz-block', {
	title: __( 'Thrive Quiz', 'thrive-quiz-builder' ),
	icon: tqbLogo,
	description: __( 'Add Thrive Quizzes!', 'thrive-quiz-builder' ),
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
				'tqb_quiz',
				query
			),
		};
	} )( function( props ) {
		const postId = props.attributes.selectedBlock,
			className = props.className;
		if ( props.attributes.previewImage ) {
			return [
				wp.element.createElement( 'img', {
					src: TQB_Data.block_preview,
				} ),
			];
		}
		if ( ! props.posts ) {
			return __( 'Loading …', 'thrive-quiz-builder' );
		}
		const options = [
			{
				value: 0,
				label: __( '-- Select a quiz --', 'thrive-quiz-builder' ),
			},
		];

		props.posts.forEach( ( post ) => {
			options.push( { value: post.id, label: post.title.raw } );
		} );

		const selectedPost = props.posts.find(
			( post ) =>
				Number( post.id ) === Number( props.attributes.selectedBlock )
		);
		if ( selectedPost ) {
			props.setAttributes( {
				blockTitle: selectedPost.title.raw,
			} );
		}

		if ( postId ) {
			const editLink = TQB_Data.edit_url + postId,
				previewLink = getPreviewLink( TQB_Data.preview_url, postId );

			return [
				createElement(
					InspectorControls,
					null,

					createElement(
						PanelBody,
						{
							title: __( 'Quiz settings', 'thrive-quiz-builder' ),
							initialOpen: true,
						},
						createElement(
							PanelRow,
							{},
							createElement(
								'a',
								{
									class: 'tqb-sidebar-edit-button',
									href: editLink,
									target: '_blank',
								},
								__( 'Edit quiz', 'thrive-quiz-builder' )
							)
						),
						createElement(
							PanelRow,
							{},
							renderSelect( options, props, 'Change quiz' )
						)
					)
				),
				createElement( 'iframe', {
					src: previewLink,
					id: `tqb-block-${ postId }`,
					class: `${ className } architect-block-preview tqb-block-${ postId }`,
					scrolling: 'no',
					onLoad() {
						const iframes = document.getElementsByClassName(
							`tqb-block-${ postId }`
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
							}, 3000 );

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
					class: 'tqb-new-block-container',
				},
				tqbLogo(),
				createElement(
					'div',
					{
						class: 'tqb-block-title',
					},
					createElement( 'h2', {}, 'Thrive Quiz' )
				),
				createElement(
					'div',
					{ class: 'tqb-block-content' },
					createElement(
						'div',
						{
							class: 'tqb-new-block-description',
						},
						__( 'Choose your Thrive Quiz', 'thrive-quiz-builder' )
					),
					renderSelect( options, props, '' )
				),
				createElement(
					'div',
					{
						class: 'tqb-go-dash',
					},
					__( 'Go to the ', 'thrive-quiz-builder' ),
					createElement(
						'a',
						{
							href: TQB_Data.dashboard_url,
							target: '_blank',
						},
						__( 'Quizzes Dashboard', 'thrive-quiz-builder' )
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
