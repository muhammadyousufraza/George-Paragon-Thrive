import {__} from '@wordpress/i18n';
import {registerBlockType} from '@wordpress/blocks';
import {PanelBody, PanelRow} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import ultLogo from './logo';

import {
	tveOuterHeight,
	getPreviewLink,
	renderCampaignSelect,
	renderDesignSelect,
} from './utils';

/* global TVU_Data */

const createElement = wp.element.createElement;

registerBlockType( 'thrive/ultimatum-block', {
	title: __( 'Thrive Ultimatum Countdown', 'thrive-ult' ),
	icon: ultLogo,
	description: __( 'Add Thrive Ultimatum countdown!', 'thrive-ult' ),
	category: 'thrive',
	attributes: {
		selectedCampaign: {
			type: 'number',
			default: 0,
		},
		selectedDesign: {
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
	edit: ( props ) => {
		const campaign = props.attributes.selectedCampaign,
			selectedDesign = props.attributes.selectedDesign,
			className = props.className;

		/**
		 * Render for the block preview while hovering the block
		 */
		if ( props.attributes.previewImage ) {
			return [
				wp.element.createElement( 'img', {
					src: TVU_Data.block_preview,
				} ),
			];
		}

		const campaignOptions = [
				{
					value: 0,
					label: __( '-- Select a campaign --', 'thrive-ult' ),
				},
			],
			designOptions = [
				{
					value: 0,
					label: __( '-- Select shortcode --', 'thrive-ult' ),
				},
			];

		Object.keys( TVU_Data.campaigns ).forEach( ( campaignID ) => {
			campaignOptions.push( {
				value: parseInt( campaignID ),
				label: TVU_Data.campaigns[ campaignID ].post_title,
			} );
		} );

		if ( campaign && TVU_Data.campaigns[ campaign ] ) {
			Object.keys( TVU_Data.campaigns[ campaign ].designs ).forEach(
				( designID ) => {
					designOptions.push( {
						value: parseInt(
							TVU_Data.campaigns[ campaign ].designs[ designID ]
								.id
						),
						label:
						TVU_Data.campaigns[ campaign ].designs[ designID ]
							.post_title,
					} );
				}
			);
		}

		/**
		 * render preview & setting options while the block is already selected
		 */
		if ( selectedDesign && campaign ) {
			let designData;
			if ( TVU_Data.campaigns[ campaign ] && TVU_Data.campaigns[ campaign ].designs ) {
				Object.keys( TVU_Data.campaigns[ campaign ].designs ).forEach(
					( designID ) => {
						if (
							parseInt(
								TVU_Data.campaigns[ campaign ].designs[ designID ]
									.id
							) === selectedDesign
						) {
							designData =
								TVU_Data.campaigns[ campaign ].designs[ designID ];
						}
					}
				);
			}

			const editLink = designData ? designData.tcb_edit_url : '';
			let previewLink = designData ? designData.tcb_preview_url : '';
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
							title: __( 'Block settings', 'thrive-ult' ),
							initialOpen: true,
						},
						createElement(
							PanelRow,
							{},

							createElement(
								'a',
								{
									class: 'tvu-sidebar-edit-button',
									href: editLink,
									target: '_blank',
								},
								__( 'Edit Ultimatum timer', 'thrive-ult' )
							)
						),
						createElement(
							PanelRow,
							{},
							renderCampaignSelect(
								campaignOptions,
								props,
								'Change Ultimatum campaign'
							)
						),
						createElement(
							PanelRow,
							{},
							renderDesignSelect(
								designOptions,
								props,
								'Change Ultimatum shortcode'
							)
						)
					)
				),
				createElement( 'iframe', {
					src: previewLink,
					id: `tvu-block-${selectedDesign}`,
					class: `${className} tvu-block-preview tvu-block-${selectedDesign}`,
					scrolling: 'no',
					onLoad() {
						const iframes = document.getElementsByClassName(
							`tvu-block-${selectedDesign}`
						);

						/**
						 * in case of duplicate / copy-paste
						 */
						Array.prototype.forEach.call( iframes, iframe => {
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
							if ( iframe.contentWindow.TVE_Dash ) {
								iframe.contentWindow.TVE_Dash.forceImageLoad(
									iframeDocument
								);
							}

							setTimeout( () => {
								setHeight()
							}, 3000 );
						} );
					},
				} ),
			];
		}

		/**
		 * Render add block functionality
		 */
		return [
			createElement(
				'div',
				{
					class: 'tvu-new-block-container',
				},
				ultLogo(),
				createElement(
					'div',
					{
						class: 'tvu-block-title',
					},
					createElement( 'h2', {}, 'Thrive Ultimatum Shortcode' )
				),
				createElement(
					'div',
					{
						class: 'tvu-new-block-description',
					},
					__( 'Choose your Thrive Ultimatum Shortcode', 'thrive-ult' )
				),
				createElement(
					'div',
					{class: 'tvu-block-content'},
					renderCampaignSelect( campaignOptions, props ),
					renderDesignSelect( designOptions, props )
				),
				createElement(
					'div',
					{
						class: 'tvu-go-dash',
					},
					__( 'Go to the ', 'thrive-ult' ),
					createElement(
						'a',
						{
							href: TVU_Data.dashboard_url,
							target: '_blank',
						},
						__( 'Ultimatum Shortcodes Dashboard', 'thrive-ult' )
					)
				)
			),
		];
	}, // How our block renders on the frontend
	save: () => {
		return null;
	},
} );
