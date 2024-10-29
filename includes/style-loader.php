<?php
function beam_checkout_load_custom_styles() {
	wp_register_style('beam_btn', plugins_url('styles.css',__FILE__ ));
	wp_enqueue_style('beam_btn');
}
