import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { withSelect } from '@wordpress/data';
import { captureLogo } from './logos';
import {renderSidebar, renderBlock, renderFrame} from './utils';

/* global TVO_Data */

registerBlockType( 'thrive/ovation-block', {
    title: __( 'Thrive Ovation', 'thrive-ovation' ),
    icon: captureLogo,
    description: __( 'Add Thrive testimonial capture forms or display testimonials', 'thrive-ovation' ),
    category: 'thrive',
    capture: [],
    display: [],
    attributes: {
        selectedBlock: {
            type: 'number',
            default: 0,
        },
        type:{
            type: 'string',
            default: null
        }
    },
    example: {
        attributes: {
            previewImage: true,
        },
    },
    edit: withSelect( function( select, props ) {
        const searchTextSel = props.attributes.searchBlockSel,
            query = {
            search: searchTextSel,
            per_page: -1,
        };

        return {
            capture: select( 'core' ).getEntityRecords(
                'postType',
                'tvo_capture_post',
                query
            ),
            display: select( 'core' ).getEntityRecords(
                'postType',
                'tvo_display_post',
                query
            )
        };
    } )( function( props ) {
        if( ! props.capture ){
            return '';
        }
        if ( props.attributes.selectedBlock ) {
            return renderSidebar( props, 'Edit with Thrive Ovation' );
        }
        return renderFrame(
            props,
            'Thrive Ovation',
            __( 'Select your Thrive Ovation capture form or testimonial display', 'thrive-ovation' ),
            captureLogo,
        );
    } ),
    save: () => {
        return null;
    },
} );
