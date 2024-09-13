/** utility functions for head_css styles */
class Head_Css {

	/**
	 * Return rules for a specific element
	 * @param $element
	 * @returns {Array}
	 */
	static get_rules_for_element( $element ) {

		let selector = '',
			rules = [];

		if ( $element.attr( 'data-selector' ) ) {
			selector = $element.attr( 'data-selector' );
		} else {
			selector = $element.attr( 'data-css' );
		}

		_.each( TVE.stylesheet.cssRules, ( rule ) => {

			/* Safari does not support CSSMediaRule.conditionText */
			let condition_text = TVE.compat.conditionText( rule );

			/* if we find a media rule, that is not empty */
			if ( rule.type === CSSRule.MEDIA_RULE && condition_text && condition_text.length ) {
				/* we go through each rule from the media rule */
				_.each( rule.cssRules, r => {
					/* get only rules for this element that are not empty */
					if ( r.selectorText.indexOf( selector ) !== - 1 && r.styleMap.size > 0 ) {
						rules.push( r.cssText );
					}
				} );
			}
		} );

		return rules;
	}
}

module.exports = Head_Css;
