const utils = require( '../util/editor' );

require( './sections' )

switch ( utils.getTemplateType() ) {
	case 'single':
		require( './single/_includes' )
		break;

	case 'list':
		require( './list/_includes' )
		break;
}
