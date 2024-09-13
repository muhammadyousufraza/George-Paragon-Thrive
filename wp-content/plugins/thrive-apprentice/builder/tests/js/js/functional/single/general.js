QUnit.module( 'General Single', {} );

QUnit.test( 'General checks', ( assert ) => {
	assert.true( TVE.Theme.template.isSingular(), 'This is a singular template' );
} );
