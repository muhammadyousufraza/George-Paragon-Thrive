<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<style>
    .tar-new-block-container {
        background-color: #f1f1f1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 15px;
        font-family: Roboto, sans-serif;
        font-size: 16px !important;
        color: #565a5f !important;
        letter-spacing: -0.12px;
        border: 15px solid #fff;

    }

    .tar-block-title h2 {
        font-size: 24px !important;
        color: #171b1b !important;
        opacity: 0.5;
        font-family: Roboto, sans-serif;
    }

    .tar-new-block-description {
        color: #565a5f;
        font-family: Roboto, sans-serif;
    }

    #tar-logo {
        opacity: 0.1;
    }
    .mb-10 {
        margin-bottom: 10px;
    }

    .sym-new-container {
        border: solid 1px #ebebeb;
    }
</style>
<div class="tar-new-block-container">
	<svg width="82" height="84" viewBox="0 0 82 84" id="tar-logo" class="mb-10">
		<defs>
			<path id="bqp31b3asa" d="M0.476 0.473L65.942 0.473 65.942 80.475 0.476 80.475z"></path>
		</defs>
		<g fill="none" fill-rule="evenodd">
			<g>
				<g>
					<g transform="translate(-445 -188) translate(445 188) translate(16)">
						<path fill="#434343" d="M65.942 80.475h-.932c-10.17 0-19.239-6.417-22.641-16.018L28.638 25.721 20.41 48.926c-2.23.393-4.27.806-5.524 1.078-2.902.607-6.712 1.46-9.536 2.588-1.666.67-3.278 1.335-4.875 2.086L19.687.473h17.9l28.355 80.002z" mask="url(#chmlltq7cb)"></path>
					</g>
					<path fill="#58A245"
						  d="M48 52.836c-.852.014-1.177.126-1.785.365-.697.26-1.415.74-1.958 1.215l-1.33 1.265c-.126.124-.125.147-.219.267-2.479 3.064-3.035 3.554-4.983 7.434-.907 1.81-1.945 3.502-3.066 5.17-.771 1.136-1.475 2.284-2.349 3.348-.086.11-.146.16-.232.258-.537.665-1.23 1.263-1.872 1.836-.43.385-.964.755-1.457 1.086-.424.269-.831.566-1.304.812l-.694.37c-.15.087-.196.136-.347.207-.111.05-.225.113-.335.163-.135.061-.26.099-.4.162-2.753 1.375-6.385 2.271-9.53 2.644l-1.937.235c-.563.084-2.103.158-3.517.181-1.18.02-2.268 0-2.619-.063l.215-.542c.456-1.025.969-2.027 1.527-2.994.098-.155.144-.288.256-.457l1.134-1.87 2.837-3.884 2.582-2.664c.073-.072.11-.121.185-.196.11-.096.124-.084.224-.17l.603-.513c.087-.059.123-.095.224-.158.16-.122.23-.194.393-.317l2.748-1.876c.919-.602 1.91-1.152 2.854-1.696l2.975-1.593c1.117-.554 3.04-1.552 4.098-2.002l3.106-1.43c-.45.049-1.033.305-1.456.454-.5.167-.935.316-1.435.48l-2.824 1.002c-1.381.468-2.75 1.088-4.09 1.624l-2.6 1.156c-.968.397-2.485 1.18-3.463 1.73-1.103.606-2.142 1.317-3.223 1.955l-3.598 2.51c-.087.065-.174.122-.26.187L9.04 70.244c-.604.62-1.318 1.216-1.885 1.871-.098.121-.098.146-.221.268-.529.538-.992 1.14-1.448 1.735-.671.895-1.294 1.825-1.866 2.778l-.947 1.775C2.3 79.419.758 83.005.687 83.998L.573 84c-.003-.13-.43-1.76-.553-3.165L0 79.671c0-.033.01-.071.008-.117.1-.757.08-1.257.24-2.218.39-2.647 1.544-5.32 3.047-7.572l1.541-2.175c.43-.569 2.099-2.427 2.595-2.803.482-.38.91-.949 1.429-1.33.223-.167.445-.402.692-.586.258-.193.444-.377.704-.562l1.472-1.076c1.496-1.077 3.104-2.034 4.77-2.865 1.777-.879 3.542-1.6 5.394-2.327 2.414-.945 5.668-1.657 8.154-2.166 2.621-.56 9.261-1.804 12.456-1.856l.686-.012c1.655-.028 4.21.003 4.812.83"
						  transform="translate(-445 -188) translate(445 188)"></path>
				</g>
			</g>
		</g>
	</svg>
	<div class="tar-block-title"><h2 class="mb-10">Thrive Block</h2></div>
	<div class="tar-new-block-description"><?php echo esc_html__( 'Currently this block has no content.', 'thrive-cb' ); ?></div>
	<div class="tvo-new-block-description mb-10"><?php echo esc_html__( 'It will update once your block has been saved in Architect.', 'thrive-cb' ); ?></div>
</div>
