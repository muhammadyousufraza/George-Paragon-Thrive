<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-shop-component" class="tve-component" data-view="Shop">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Shop Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="center-xs col-xs-12 mb-10">
			<button class="tve-button orange click" data-fn="editProducts">
				<?php echo esc_html__( 'Edit Design', 'thrive-cb' ); ?>
			</button>
		</div>
		<div class="hide-tablet hide-mobile">
			<hr>
			<div class="tve-control" data-view="Limit"></div>
			<div class="tve-control" data-view="Columns"></div>
			<div class="tve-control" data-view="OrderBy"></div>
			<div class="tve-control" data-view="Order"></div>

			<hr>

			<div class="tve-control" data-view="result-count-visibility"></div>
			<div class="tve-control" data-view="catalog-ordering-visibility"></div>
			<div class="tve-control" data-view="sale-flash-visibility"></div>
			<div class="tve-control" data-view="title-visibility"></div>
			<div class="tve-control" data-view="rating-visibility"></div>
			<div class="tve-control" data-view="price-visibility"></div>
			<div class="tve-control" data-view="cart-visibility"></div>
			<div class="tve-control" data-view="pagination-visibility"></div>

			<hr>

			<div class="tve-control" data-view="Alignment"></div>
			<div class="tve-control" data-view="ImageSize"></div>

			<div class="tve-advanced-controls">
				<div class="dropdown-header" data-prop="advanced">
				<span class="mb-5">
					<?php echo esc_html__( 'Filter Products', 'thrive-cb' ); ?>
				</span>
				</div>
				<div class="dropdown-content pt-0">
					<div class="ids-container mb-5">
						<label for="tcb-woo-shop-ids-select" class="tcb-shop-operator-label">
							<?php echo esc_html__( 'Products', 'thrive-cb' ); ?>
						</label>
						<select id="tcb-woo-shop-ids-select" class="tcb-woo-select"></select>
					</div>
					<hr class="mt-10">

					<div class="category-container mb-5">
						<div class="tve-control" data-view="cat_operator"></div>
						<label for="tcb-woo-shop-category-select" class="tcb-shop-operator-label">
							<?php echo esc_html__( 'Categories', 'thrive-cb' ); ?>
						</label>
						<select id="tcb-woo-shop-category-select" class="tcb-woo-select"></select>
					</div>

					<hr class="mt-10">

					<div class="tags-container mb-5">
						<div class="tve-control" data-view="tag_operator"></div>
						<label for="tcb-woo-shop-tag-select" class="tcb-shop-operator-label">
							<?php echo esc_html__( 'Tags', 'thrive-cb' ); ?>
						</label>
						<select id="tcb-woo-shop-tag-select" class="tcb-woo-select"></select>
					</div>

					<hr class="taxonomy-separator mt-10">
					<div class="tve-control" data-view="taxonomy"></div>

					<div class="terms-container ">
						<div class="tve-control" data-view="terms_operator"></div>
						<div class="mb-5">
							<label for="tcb-woo-shop-terms-select" class="tcb-save-as-label">
								<?php echo esc_html__( 'Terms', 'thrive-cb' ); ?>
							</label>
							<select id="tcb-woo-shop-terms-select" class="tcb-woo-select"></select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
