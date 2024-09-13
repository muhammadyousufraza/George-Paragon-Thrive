<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Register a new condition entity
 *
 * @param \TCB\ConditionalDisplay\Entity|string $entity
 */
function tve_register_condition_entity( $entity ) {
	TCB\ConditionalDisplay\Entity::register( $entity );
}

/**
 * Register a new condition field
 *
 * @param \TCB\ConditionalDisplay\Field|string $field
 */
function tve_register_condition_field( $field ) {
	TCB\ConditionalDisplay\Field::register( $field );
}

/**
 * Register a new condition
 *
 * @param \TCB\ConditionalDisplay\Condition|string $condition
 */
function tve_register_condition( $condition ) {
	TCB\ConditionalDisplay\Condition::register( $condition );
}
