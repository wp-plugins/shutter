<?php
/**
 * Single Gallery
 */
?>

<?php get_header('gallery'); ?>

<?php do_action('gallery_before_main_content'); ?>

<?php shutter_gallery_single_content(); ?>

<?php do_action('gallery_after_main_content'); ?>

<?php do_action('shutter_sidebar'); ?>

<?php get_footer('gallery'); ?>