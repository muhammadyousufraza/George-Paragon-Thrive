<?php
$c = get_option( 'tcm_color_picker_value', '#03a9f4' );
?>
<style>

    /* for unique landing page accent color values,  put any new css added here inside tcb-bridge/js/editor */

    /* accent color */
    #thrive-comments .tcm-color-ac,
    #thrive-comments .tcm-color-ac span {
        color: <?php echo $c; ?>;
    }

    /* accent color background */
    #thrive-comments .tcm-background-color-ac,
    #thrive-comments .tcm-background-color-ac-h:hover span,
    #thrive-comments .tcm-background-color-ac-active:active {
        background-color: <?php echo $c; ?>
    }

    /* accent color border */
    #thrive-comments .tcm-border-color-ac {
        border-color: <?php echo $c; ?>;
        outline: none;
    }

    #thrive-comments .tcm-border-color-ac-h:hover {
        border-color: <?php echo $c; ?>;
    }

    #thrive-comments .tcm-border-bottom-color-ac {
        border-bottom-color: <?php echo $c; ?>;
    }

    /* accent color fill*/
    #thrive-comments .tcm-svg-fill-ac {
        fill: <?php echo $c; ?>;
    }

    /* accent color for general elements */

    /* inputs */
    #thrive-comments textarea:focus,
    #thrive-comments input:focus {
        border-color: <?php echo $c; ?>;
        box-shadow: inset 0 0 3px<?php echo $c; ?>;
    }

    /* links */
    #thrive-comments a {
        color: <?php echo $c; ?>;
    }

    /*
	* buttons and login links
	* using id to override the default css border-bottom
	*/
    #thrive-comments button,
    #thrive-comments #tcm-login-up,
    #thrive-comments #tcm-login-down {
        color: <?php echo $c; ?>;
        border-color: <?php echo $c; ?>;
    }

    /* general buttons hover and active functionality */
    #thrive-comments button:hover,
    #thrive-comments button:focus,
    #thrive-comments button:active {
        background-color: <?php echo $c; ?>
    }

</style>
