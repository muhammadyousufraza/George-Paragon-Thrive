<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\TTB\Check;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$allow_skin_reset = Check::is_end_user_site();
?>

<style>
    .ttb-container {
        margin: 24px auto;
        width: 680px;
        box-sizing: border-box;
        padding: 25px 90px 35px;
        background: white;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        position: relative;
    }

    .ttb-container h1 {
        margin: 0 0 30px;
    }

    .wp-core-ui .ttb-container .button {
        color: #fff;
        background-color: orange;
        border: none;
    }

    .red-btn {
        background-color: red !important;
    }

    .blue-btn {
        background-color: darkblue !important;
    }

    .green-btn {
        background-color: darkgreen !important;
    }

    .ttb-center {
        text-align: center;
    }

    .ttb-mb30 {
        margin-bottom: 30px;
    }
</style>

<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Reset user learned lessons', 'thrive-apprentice' ); ?></h1>
	<p><?php echo __( 'Use the button below to reset the logged in user learned lessons.', 'thrive-apprentice' ); ?></p>
	<p class="ttb-mb30"><strong><?php echo __( "Warning: Resetting the learned lessons will remove all progress the currently logged user has made for content controlled by Thrive Apprentice and cannot be undone!", 'thrive-apprentice' ); ?></strong></p>

	<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you sure you want to reset the progress?', 'thrive-apprentice' ); ?></strong></p>

	<div class="ttb-center">
		<button data-action="tva_progress_reset" class="button ttb-action-button delete-theme">
			<?php echo __( 'Remove logged in user data from Apprentice', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>
<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Toggle demo content', 'thrive-apprentice' ); ?></h1>
	<p><?php echo __( 'Use the options bellow to toggle demo content data.', 'thrive-apprentice' ); ?></p>
	<p class="ttb-mb30"><strong><?php echo __( "Warning: By removing demo content from this website the wizard might not work as expected.", 'thrive-apprentice' ); ?></strong></p>
	<div class="ttb-center">
		<button data-action="tva_remove_demo_content" class="button ttb-action-button delete-theme">
			<?php echo __( 'Remove demo content', 'thrive-apprentice' ); ?>
		</button>
		<button data-action="tva_create_demo_content" class="button ttb-action-button delete-theme">
			<?php echo __( 'Re-create demo content', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>
<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Reset assessments', 'thrive-apprentice' ); ?></h1>
	<p><?php echo __( 'Use the options below to reset assessments.', 'thrive-apprentice' ); ?></p>
	<p class="ttb-mb30"><strong><?php echo __( "Warning: By removing course assessments this will remove user assessments too.", 'thrive-apprentice' ); ?></strong></p>
	<div class="ttb-center">
		<button data-action="tva_remove_user_assessments" class="button ttb-action-button delete-theme">
			<?php echo __( 'Remove user assessments', 'thrive-apprentice' ); ?>
		</button>
		<button data-action="tva_remove_course_assessments" class="button ttb-action-button delete-theme">
			<?php echo __( 'Remove course assessments', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>

<div class="ttb-container theme-overlay">
	<h1 class="ttb-center">Missing members</h1>
	<p class="ttb-center"><?php echo __( 'Try to import members that don\'t show up in the Members section', 'thrive-apprentice' ); ?></p>
	<div class="ttb-center">
		<button data-action="tva_fix_members" class="button ttb-action-button green-btn ">
			<?php echo __( 'Fix missing members', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>

<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Reset Stripe', 'thrive-apprentice' ); ?></h1>
	<p class="ttb-mb30"><strong><?php echo __( "Warning: By removing Stripe data it will remove the API connection too and the whole set-up needs to be re-done.", 'thrive-apprentice' ); ?></strong></p>
	<div class="ttb-center">
		<button data-action="tva_reset_stripe" class="button ttb-action-button delete-theme">
			<?php echo __( 'Remove Stripe', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>
<?php if ( $allow_skin_reset ): ?>
	<div class="ttb-container theme-overlay">
		<h1 class="ttb-center"><?php echo __( 'Reset sanity check skins', 'thrive-apprentice' ); ?></h1>
		<p><?php echo __( 'Use the button below the sanity check option from database', 'thrive-apprentice' ); ?></p>
		<p class="ttb-mb30"><strong><?php echo __( "Warning: The system will run the sanity check function for all apprentice skins", 'thrive-apprentice' ); ?></strong></p>

		<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you want to remove the sanity check option?', 'thrive-apprentice' ); ?></strong></p>

		<div class="ttb-center">
			<button data-action="tva_skin_sanity_check_reset" class="button ttb-action-button delete-theme blue-btn">
				<?php echo __( 'Reset the sanity check option', 'thrive-apprentice' ); ?>
			</button>
		</div>
	</div>
	<div class="ttb-container theme-overlay">
		<h1 class="ttb-center"><?php echo __( 'Reset apprentice skins', 'thrive-apprentice' ); ?></h1>
		<p><?php echo __( 'Use the button below to remove all apprentice skins.', 'thrive-apprentice' ); ?></p>
		<p class="ttb-mb30"><strong><?php echo __( "Warning: This option will remove all cloud apprentice skins and will activate the Legacy Skin. It cannot be undone!", 'thrive-apprentice' ); ?></strong></p>

		<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you sure you want to reset the skin data?', 'thrive-apprentice' ); ?></strong></p>

		<div class="ttb-center">
			<button data-action="tva_skin_reset" class="button ttb-action-button delete-theme red-btn">
				<?php echo __( 'Remove all skin data from Thrive Apprentice', 'thrive-apprentice' ); ?>
			</button>
		</div>
	</div>
<?php endif; ?>
<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Reset access history', 'thrive-apprentice' ); ?></h1>
	<p><?php echo __( 'Use the button below to remove all access history data', 'thrive-apprentice' ); ?></p>
	<p class="ttb-mb30"><strong><?php echo __( "Warning: This option will remove all data stored inside the access history table. Also it will remove the flags store in the database so the migration can run again.", 'thrive-apprentice' ); ?></strong></p>

	<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you sure you want to remove all access history data?', 'thrive-apprentice' ); ?></strong></p>

	<div class="ttb-center">
		<button data-action="tva_access_history_remove" class="button ttb-action-button">
			<?php echo __( 'Remove all access history data from Thrive Apprentice', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>
<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Index on History Table', 'thrive-apprentice' ); ?></h1>
	<p><?php echo __( 'Use the button below try to add index to the access_history table for columns: user_id, product_id, course_id', 'thrive-apprentice' ); ?></p>
	<p class="ttb-mb30"><strong><?php echo __( 'Please note that the script checks if the index exists before adding the script', 'thrive-apprentice' ); ?></strong></p>

	<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you sure you want to try to add the index?', 'thrive-apprentice' ); ?></strong></p>

	<div class="ttb-center">
		<button data-action="tva_access_history_index" class="button ttb-action-button">
			<?php echo __( 'Try to add index', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>

<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Reporting events logs', 'thrive-apprentice' ); ?></h1>

	<p class="ttb-center"><?php echo __( 'Use the button below to generate reporting events data', 'thrive-apprentice' ); ?></p>

	<div class="ttb-center">
		<button data-action="tva_reporting_logs_generate" data-text-response="Data generated -> check reports" class="button ttb-action-button green-btn">
			<?php echo __( 'Generate reporting events', 'thrive-apprentice' ); ?>
		</button>
	</div>

	<br>
	<hr>

	<p class="ttb-center"><?php echo __( 'Use the button below to remove all reporting events data', 'thrive-apprentice' ); ?></p>

	<div class="ttb-center">
		<button data-action="tva_reporting_logs_remove" data-text-response="Data removed -> check reports" class="button ttb-action-button red-btn">
			<?php echo __( 'Remove all reporting events from Thrive Apprentice', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>

<div class="ttb-container theme-overlay">
	<div class="ttb-center">
		<button data-action="tva_reset_verification_page" class="button ttb-action-button red-btn">
			<?php echo __( 'Reset the verification page', 'thrive-apprentice' ); ?>
		</button>
	</div>
</div>

<?php if ( 1 === 2 ): //Hide this for now?>
	<div class="ttb-container theme-overlay">
		<h1 class="ttb-center"><?php echo __( 'Reset products created from migration', 'thrive-apprentice' ); ?></h1>
		<p><?php echo __( 'Use the button below to reset all products created from migration.', 'thrive-apprentice' ); ?></p>
		<p class="ttb-mb30"><strong><?php echo __( "Warning: This option will remove all products created from migration and try to re-create new ones from the existing protected courses", 'thrive-apprentice' ); ?></strong></p>

		<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you sure you want to reset the products?', 'thrive-apprentice' ); ?></strong></p>

		<div class="ttb-center">
			<button data-action="tva_products_reset" class="button ttb-action-button delete-theme delete-theme">
				<?php echo __( 'Remove all migrated products from Thrive Apprentice', 'thrive-apprentice' ); ?>
			</button>
		</div>
	</div>
<?php endif; ?>
<script type="text/javascript">
	( function ( $ ) {
		$( '.ttb-action-button' ).click( function () {
			$( this ).css( 'opacity', 0.3 );

			const afterText = this.dataset.textResponse ?? 'Done - do it again?';

			$.ajax( {
					url: ajaxurl,
					type: 'post',
					data: {
						action: this.dataset.action
					}
				}
			).success( () => $( this ).css( { 'opacity': 1, 'background-color': 'green' } ).text( afterText )
			).always( response => console.warn( response ) )
		} );
	} )( jQuery )
</script>
