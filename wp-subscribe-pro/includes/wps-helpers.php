<?php
/**
 * Helper Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// ---------------- PLUGIN HELPERS -----------------------------------

/**
 * Enqueue popup css
 * @return void
 */
function wps_enqueue_popup_css() {
	wp_enqueue_style( 'wp-subscribe', wps()->plugin_url() . '/assets/css/wp-subscribe-form.css' );
	wp_enqueue_style( 'wp-subscribe-popup', wps()->plugin_url() . '/assets/css/wp-subscribe-popup.css' );
}

/**
 * Enqueue popup js
 * @return void
 */
function wps_enqueue_popup_js() {

	wp_enqueue_script( 'magnific-popup', wps()->plugin_url() . '/assets/js/magnificpopup.js', array('jquery') );
	wp_enqueue_script( 'jquery-cookie', wps()->plugin_url() . '/assets/js/jquery.cookie.js', array('jquery') );
	wp_enqueue_script( 'exitIntent', wps()->plugin_url() . '/assets/js/jquery.exitIntent.js', array('jquery') );

	wp_register_script('wp-subscribe', wps()->plugin_url() . '/assets/js/wp-subscribe-form.js', array('jquery'));
	wp_localize_script( 'wp-subscribe', 'wp_subscribe', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
	) );
	wp_enqueue_script( 'wp-subscribe' );
}

/**
 * Render html for the popup
 *
 * @param  array $options
 * @return void
 */
function wps_popup_html( $options = null, $type = 'popup' ) {

	// Options
	if ( $options == null ) {
		$options = wps_get_options();
	}

	// classes for responsive
	$responsive_class = '';
	foreach ( array( 300, 600, 900 ) as $breakpoint) {
		if ( $options['popup_width'] < $breakpoint ) {
			$responsive_class .= " lt_$breakpoint";
		}
	}

	if ( 'single_post' !== $type && ( ! isset( $options['preview'] ) || ! $options['preview'] ) ) {
		$responsive_class .= ' mfp-hide';
	}

	if ( 'single_post' !== $type ) {
		echo '<div id="wp_subscribe_popup" class="wp-subscribe-popup' . $responsive_class . '">';
	}

	if ( 'subscribe_form' == $options['popup_content'] ) {

		$new_options = array_merge( $options["{$type}_form_options"], $options["{$type}_form_labels"], $options["{$type}_form_colors"] );
		$new_options['service'] = $options["{$type}_form_options"]['service'];
		$new_options['include_name_field'] = $options["{$type}_form_options"]['include_name_field'];
		$new_options['thanks_page'] = isset( $options["{$type}_form_options"]['thanks_page'] ) ? $options["{$type}_form_options"]['thanks_page'] : '';
		$new_options['thanks_page_url'] = isset( $options["{$type}_form_options"]['thanks_page_url'] ) ? $options["{$type}_form_options"]['thanks_page_url'] : '';
		$new_options['popup_content'] = $options['popup_content'];
		$new_options['form_type'] = $type;
		if( isset( $options['before_widget'] ) ) {
			$new_options['before_widget'] = $options['before_widget'];
		}

		wps_the_form( $new_options );
	}
	elseif ( 'custom_html' == $options['popup_content'] ) {
		echo do_shortcode( $options['popup_custom_html'] );
	}
	elseif ( 'posts' == $options['popup_content'] ) {
		wps_get_related_posts();
	}

	if ( 'single_post' !== $type ) {
		echo '</div>';
	}
	?>

	<style type="text/css" id="popup-style-width">#wp_subscribe_popup { width: <?php echo $options['popup_width'] ?>px}</style>
	<style type="text/css" id="overlay-style-color">body > .mfp-bg {background: <?php echo $options['popup_overlay_color'] ?>}</style>
	<style type="text/css" id="overlay-style-opacity">body > .mfp-bg.mfp-ready {opacity: <?php echo $options['popup_overlay_opacity'] ?>}</style>

	<?php
}

/**
 * Generate the subscription form
 * @return void
 */
function wps_the_form( $options = null ) {

	global $wp, $wp_subscribe_forms;

	// Options
	if ( null == $options ) {
		return;
	}

	// Enqueue script and styles
	wp_enqueue_style( 'wp-subscribe' );
	wp_enqueue_script( 'wp-subscribe' );

	$wp_subscribe_forms++;
	$service = wps_get_subscription_service( $options['service'] );
	$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
?>
	<?php if( isset( $options['before_widget'] ) ) : ?>
		<?php echo $options['before_widget'] ?>
	<?php else: ?>
		<div class="wp-subscribe-popup-form-wrapper">
	<?php endif; ?>

		<?php wps_the_form_css( $wp_subscribe_forms, $options ) ?>

		<div id="wp-subscribe" class="wp-subscribe-wrap wp-subscribe wp-subscribe-<?php echo $wp_subscribe_forms ?>" data-thanks_page="<?php echo absint( $options['thanks_page'] ) ?>" data-thanks_page_url="<?php echo esc_url( $options['thanks_page_url'] ) ?>" data-thanks_page_new_window="0">

			<h4 class="title"><?php echo wp_kses_post( $options['title'] )?></h4>

			<p class="text"><?php echo wp_kses_post( $options['text'] ) ?></p>

			<?php if( method_exists( $service, 'the_form' ) ) :
				$service->the_form( $wp_subscribe_forms, $options );
			else: ?>

			<form action="<?php echo $current_url ?>" method="post" class="wp-subscribe-form wp-subscribe-<?php echo $options['service'] ?>" id="wp-subscribe-form-<?php echo $wp_subscribe_forms ?>">

				<?php if( !empty( $options['include_name_field'] ) ) : ?>
					<input class="regular-text name-field" type="text" name="name" placeholder="<?php echo esc_attr( $options['name_placeholder'] ) ?>" required>
				<?php endif; ?>

				<input class="regular-text email-field" type="email" name="email" placeholder="<?php echo esc_attr( $options['email_placeholder'] ) ?>" required>

				<input type="hidden" name="form_type" value="<?php echo $options['form_type'] ?>">

				<input type="hidden" name="service" value="<?php echo $options['service'] ?>">

				<input type="hidden" name="widget" value="<?php echo isset( $options['widget_id'] ) ? $options['widget_id'] : '0'; ?>">

				<?php if( !empty( $options['consent_text'] ) ) : ?>
				<div class="wps-consent-wrapper">
					<label for="consent-field">
						<input class="consent-field" type="checkbox" name="consent" required>
						<?php _e( $options['consent_text'] ) ?>
					</label>
				</div>
				<?php endif; ?>
				<input class="submit" type="submit" name="submit" value="<?php echo esc_attr( $options['button_text'] ) ?>">

			</form>

			<?php endif; ?>

			<div class="wp-subscribe-loader">
				<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
					<path fill="<?php echo $options['title_color'] ?>" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
						<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"/>
					</path>
				</svg>
			</div>

			<?php if( !empty( $options['success_message'] ) ) {
				printf( '<p class="thanks">%s</p>', wp_kses_post( $options['success_message'] ) );
			} ?>

			<?php if( !empty( $options['error_message'] ) ) {
				printf( '<p class="error">%s</p>', wp_kses_post( $options['error_message'] ) );
			} ?>

			<div class="clear"></div>

			<p class="footer-text"><?php echo $options['footer_text'];?></p>

		</div>

	<?php if( isset( $options['after_widget'] ) ) : ?>
		<?php echo $options['after_widget'] ?>
	<?php else: ?>
		</div><!-- /form-wrapper -->
	<?php endif; ?>

<?php
}

/**
 * [wps_the_form_css description]
 * @param  [type] $id      [description]
 * @param  [type] $options [description]
 * @return [type]          [description]
 */
function wps_the_form_css( $id, $options, $type = 'subscribe_form' ) {

	$css = array();
	$id = "\n#wp-subscribe.wp-subscribe-{$id} ";

	if ( 'subscribe_form' == $type ) {
		$css[] = sprintf( '{background: %s}', $options['background_color'] );
		$css[] = sprintf( 'h4 {color: %s}', $options['title_color'] );
		$css[] = sprintf( 'p {color: %s}', $options['text_color'] );
		$css[] = sprintf( '.regular-text {background: %s; color: %s }', $options['field_background_color'], $options['field_text_color'] );
		$css[] = sprintf( '.wps-consent-wrapper label {color: %s }', $options['field_text_color'] );
		$css[] = sprintf( '.submit {background: %s; color: %s }', $options['button_background_color'], $options['button_text_color'] );
		$css[] = sprintf( '.thanks {color: %s; display: none}', $options['text_color'] );
		$css[] = sprintf( '.error {color: %s; display: none}', $options['text_color'] );
		$css[] = sprintf( '.footer-text {color: %s }', $options['footer_text_color'] );
	}
	else if ( 'posts' == $type ) {

		$id = "\n.popup-related-posts-wrapper ";

		$css[] = sprintf( '{background: %s}', $options['background_color'] );
		$css[] = sprintf( 'h3 {color: %s}', $options['title_color'] );
		$css[] = sprintf( 'p {color: %s}', $options['text_color'] );
		$css[] = sprintf( '.popup-related-posts {border-top-color: %s}', $options['line_color'] );
		$css[] = sprintf( '.popup-post-categories a {color: %s}', $options['text_color'] );
		$css[] = sprintf( 'h4 a {color: %s}', $options['text_color'] );
		$css[] = sprintf( '.popup-post-excerpt {color: %s}', $options['text_color'] );
	}
	?>
	<style>
		<?php echo $id . join( $id, $css ) ?>
	</style>
	<?php
}

/**
 * Render javascript for the popup
 * @return void
 */
function wps_popup_javascript() {

	$options = wps_get_options();
	$removal_delay = wps_get_popup_removal_delay();
?>
<script type="text/javascript">
	var wps_disabled = false;

	function wp_subscribe_popup() {
		<?php
		$mobile = isset( $options['popup_triggers']['hide_on_mobile'] ) ? $options['popup_triggers']['hide_on_mobile'] : '0';
		$screen = isset( $options['popup_triggers']['hide_on_screen'] ) ? $options['popup_triggers']['hide_on_screen'] : '400';
		if ( '1' == $mobile ) : ?>
		if( jQuery(window).width() < <?php echo esc_js( $screen ) ?> ) {
		 return;
		}
		<?php
		endif;
		?>
		if( wps_disabled || 1 == jQuery.cookie( 'wps_cookie_<?php echo $options['cookie_hash'] ?>' ) ) {
			return;
		}

		jQuery.magnificPopup.open({
			items: {
			  src: '#wp_subscribe_popup',
				type: 'inline'
			},
			removalDelay: <?php echo $removal_delay ?>,
			callbacks: {
				beforeOpen: function() {
					this.st.mainClass = 'animated <?php echo $options['popup_animation_in'] ?>';
				},
				beforeClose: function() {
					var $wrap = this.wrap,
						$bg = $wrap.prev(),
						$mfp = $wrap.add($bg);

					$mfp.removeClass('<?php echo $options['popup_animation_in'] ?>').addClass('<?php echo $options['popup_animation_out'] ?>');
				},
			},
		});

		jQuery.cookie(
			'wps_cookie_<?php echo $options['cookie_hash'] ?>',
			'1',
			{
				path: '/'
				<?php if ( $options['cookie_expiration'] ) { ?>, expires: <?php echo (int) $options['cookie_expiration'] ?><?php } ?>
			}
		);

		wps_disabled = true;

	} // end_js_popup

	<?php if ( $options['popup_triggers']['on_enter'] || $options['popup_triggers']['on_reach_bottom'] ) { ?>

	jQuery(window).on( 'load', function() {
		<?php if ( $options['popup_triggers']['on_enter'] ) { ?>
		wp_subscribe_popup();
		<?php } ?>

		<?php if ( (is_singular( 'post' ) || is_singular( 'page' ) ) && !empty( $options['popup_triggers']['on_reach_bottom'] ) ) { ?>
		if ( jQuery('#wp-subscribe-content-bottom').length ) {
			var content_bottom = Math.floor(jQuery('#wp-subscribe-content-bottom').offset().top);
			jQuery(window).scroll(function(event) {
				var viewport_bottom = jQuery(window).scrollTop() + jQuery(window).height();
				if ( viewport_bottom >= content_bottom ) {
					wp_subscribe_popup();
				}
			});
		}
		<?php } ?>
	});
	<?php } ?>

	jQuery(document).ready(function($) {
		<?php if ( $options['popup_triggers']['on_timeout'] ) { ?>
		setTimeout( wp_subscribe_popup, <?php echo 1000 * $options['popup_triggers']['timeout'] ?>);
		<?php } ?>

		<?php if ( $options['popup_triggers']['on_exit_intent'] ) { ?>
		$(document).exitIntent(wp_subscribe_popup);
		<?php } ?>
	});

</script>
<?php
}

/**
 * Get related posts as the content
 * @param  [type] $options [description]
 * @return [type]          [description]
 */
function wps_get_related_posts( $options = null ) {

	if ( is_null( $options ) ) {
		$options = wps_get_options();
	}

	wps_the_form_css( '', $options['popup_posts_colors'], 'posts' );
?>
	<div class="popup-related-posts-wrapper popup-content">

		<h3><?php echo wp_kses_post( $options['popup_posts_labels']['title'] ) ?></h3>

		<p><?php echo wp_kses_post( $options['popup_posts_labels']['text'] ) ?></p>

		<div class="popup-related-posts">
		<?php
			$posts_query = false;
			if ( is_singular('post') ) {

				// get related posts by tags
				$tags = get_the_tags( get_the_ID() );
				if ( !empty( $tags ) ) {
				    $args = array(
				    	'tag__in'             => wp_list_pluck( $tags, 'term_id' ),
				        'post__not_in'        => array( get_the_ID() ),
				        'posts_per_page'      => 3,
				        'ignore_sticky_posts' => 1,
				        'orderby'             => 'rand',
				        'post_status'         => 'publish'
				    );

				    $posts_query = new WP_Query( $args );
					if( !$posts_query->have_posts() ) {
				    	$posts_query = false;
				    }
				}

				// if there are no posts, get related posts by categories
				if ( !$posts_query ) {

					$categories = get_the_category( get_the_ID() );
					if ( !empty( $categories ) ) {
		                $args = array(
		                	'category__in'        => wp_list_pluck( $categories, 'term_id' ),
		                    'post__not_in'        => array( get_the_ID() ),
		                    'posts_per_page'      => 3,
		                    'ignore_sticky_posts' => 1,
		                    'orderby'             => 'rand',
				        	'post_status'         => 'publish'
		                );

						$posts_query = new WP_Query( $args );
						if( !$posts_query->have_posts() ) {
					    	$posts_query = false;
					    }
					}
				}
			}

			// still no posts or not singular: show random
			if ( !$posts_query ) {
				$args = array(
		            'posts_per_page'      => 3,
		            'ignore_sticky_posts' => 1,
		            'orderby'             => 'rand',
		        	'post_status'         => 'publish'
		        );
		        $posts_query = new WP_Query( $args );
			}

			if( $posts_query->have_posts() ) :

				while( $posts_query->have_posts() ) : $posts_query->the_post();
			?>
					<div class="popup-related-post">
						<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>" rel="nofollow" class="popup-post-thumbnail">
							<?php the_post_thumbnail( 'widgetfull' ) ?>
			            </a>
			        	<?php endif; ?>

						<?php if ( $options['popup_posts_meta']['category'] ) : ?>
						<div class="popup-post-categories">
						<?php $categories = get_the_category(); $i = 0;
							foreach( $categories as $cat ) : $i++;
								if ( $i > 3 ) {
									break; // show first 3 categories
								}
						?>
							<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ) ?>" title="<?php esc_attr( sprintf( __( "View all posts in %s", 'wp-subscribe' ), $cat->name ) ) ?>"><?php echo $cat->cat_name ?></a>
						<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<h4>
							<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>"><?php the_title() ?></a>
						</h4>

						<?php if ( $options['popup_posts_meta']['excerpt'] ) :

							$strlen = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
							$excerpt = get_the_excerpt();

							if ( $strlen( $excerpt ) > 80 ) {
								$substr = function_exists('mb_substr') ? 'mb_substr' : 'substr';
								$excerpt = $substr( $excerpt, 0, 80 ) . '&hellip;';
							}
						?>
						<p class="popup-post-excerpt">
							<?php echo $excerpt; ?>
						</p>
						<?php endif; ?>

					</div>

			<?php
				endwhile;

			endif;
			?>
		</div>

		<div class="clear"></div>

	</div>
<?php
}

/**
 * Get widget setting by id
 * @param  int $widget_id
 * @return mixed
 */
function wps_get_widget_settings( $widget_id ) {

	$options = array();
	global $wp_registered_widgets;

    if ( isset( $wp_registered_widgets ) && isset( $wp_registered_widgets[$widget_id] ) ) {

        $widget = $wp_registered_widgets[$widget_id];
        $settings = $widget['callback'][0]->get_settings();

        if ( isset( $settings[$widget['params'][0]['number']] ) ) {
            $options = $settings[$widget['params'][0]['number']];
        }
    }

    return $options;
}


// ---------------- STRING HELPERS ---------------------------------

/**
 * Check if the string begins with the given value
 *
 * @param  string	$needle   The sub-string to search for
 * @param  string	$haystack The string to search
 *
 * @return bool
 */
function wps_str_start_with( $needle, $haystack ) {
	return substr_compare( $haystack, $needle, 0, strlen( $needle ) ) === 0;
}

/**
 * Check if the string contains the given value
 *
 * @param  string	$needle   The sub-string to search for
 * @param  string	$haystack The string to search
 *
 * @return bool
 */
function wps_str_contains( $needle, $haystack ) {
	return strpos( $haystack, $needle ) !== false;
}


// ---------------- HTML HELPERS ---------------------------------

/**
 * Output select field html
 *
 * @param  array  $args
 *
 * @return void
 */
function wps_field_select( $args = array() ) {

	extract( wp_parse_args( $args, array(
		'class' => 'widefat'
	) ) );
	if ( ! array_key_exists( $value, $options ) ) {
		$options[$value] = $value;
	}
	?>
	<select class="<?php echo esc_attr( $class ) ?>" id="<?php echo esc_attr( $id ) ?>" name="<?php echo esc_attr( $name ) ?>">

		<?php foreach ( $options as $key => $text ) : ?>
			<option value="<?php echo esc_attr( $key ) ?>"<?php selected( $key, $value ) ?>>
				<?php echo esc_html( $text ) ?>
			</option>
		<?php endforeach ?>
	</select>
	<?php
}

/**
 * Output text field html
 *
 * @param  array  $args
 *
 * @return void
 */
function wps_field_text( $args = array() ) {

	extract( wp_parse_args( $args, array(
		'class' => 'widefat'
	) ) );
	?>
	<input class="<?php echo esc_attr( $class ) ?>" id="<?php echo esc_attr( $id ) ?>" name="<?php echo esc_attr( $name ) ?>" type="text" value="<?php echo esc_attr( $value ) ?>"<?php if( isset( $data_id ) ) { printf( 'data-id="%s"', $data_id ); } ?>>
	<?php
}

/**
 * Output textarea field html
 *
 * @param  array  $args
 *
 * @return void
 */
function wps_field_textarea( $args = array() ) {

	extract( wp_parse_args( $args, array(
		'class' => 'widefat'
	) ) );
	?>
	<textarea class="<?php echo esc_attr( $class ) ?>" id="<?php echo esc_attr( $id ) ?>" name="<?php echo esc_attr( $name ) ?>" <?php if( isset( $data_id ) ) { printf( 'data-id="%s"', $data_id ); } ?>><?php echo esc_textarea( $value ) ?></textarea>
	<?php
}

/**
 * Output editor field html
 *
 * @param  array  $args
 *
 * @return void
 */
function wps_field_editor( $args = array() ) {

	extract( wp_parse_args( $args, array(
		'class' => 'widefat'
	) ) );
	$args = array(
		'teeny' => true,
		'editor_class' => esc_attr( $class ),
		'textarea_name' => esc_attr( $name ),
		'quicktags' => false,
		'media_buttons' => false
	);
	wp_editor( $value, esc_attr( $id ), $args );

}

/**
 * Output hidden field html
 *
 * @param  array  $args
 *
 * @return void
 */
function wps_field_hidden( $args = array() ) {

	extract( $args );
	?>
	<input id="<?php echo esc_attr( $id ) ?>" name="<?php echo esc_attr( $name ) ?>" type="hidden" value="<?php echo esc_attr( $value ) ?>"<?php if( isset( $data_id ) ) { printf( 'data-id="%s"', $data_id ); } ?>>
	<?php
}

/**
 * Get animation select
 * @param  string $id
 * @param  string $name
 * @return void
 */
function wps_get_animations( $id = '', $name = '', $value = '' ) {
	if ( strpos( $id, '_out' ) === false ) {
		$animations = array(
			'0' => esc_html__( 'No Animation', 'wp-subscribe' ),
			esc_html__( 'Attention Seekers', 'wp-subscribe' ) => array(
				'bounce'     => esc_html__( 'bounce', 'wp-subscribe' ),
				'flash'      => esc_html__( 'flash', 'wp-subscribe' ),
				'pulse'      => esc_html__( 'pulse', 'wp-subscribe' ),
				'rubberBand' => esc_html__( 'rubberBand', 'wp-subscribe' ),
				'shake'      => esc_html__( 'shake', 'wp-subscribe' ),
				'swing'      => esc_html__( 'swing', 'wp-subscribe' ),
				'tada'       => esc_html__( 'tada', 'wp-subscribe' ),
				'wobble'     => esc_html__( 'wobble', 'wp-subscribe' ),
			),
			esc_html__( 'Bouncing Entrances', 'wp-subscribe' ) => array(
				'bounceIn'      => esc_html__( 'bounceIn', 'wp-subscribe' ),
				'bounceInDown'  => esc_html__( 'bounceInDown', 'wp-subscribe' ),
				'bounceInLeft'  => esc_html__( 'bounceInLeft', 'wp-subscribe' ),
				'bounceInRight' => esc_html__( 'bounceInRight', 'wp-subscribe' ),
				'bounceInUp'    => esc_html__( 'bounceInUp', 'wp-subscribe' ),
			),
			esc_html__( 'Fading Entrances', 'wp-subscribe' ) => array(
				'fadeIn'         => esc_html__( 'fadeIn', 'wp-subscribe' ),
				'fadeInDown'     => esc_html__( 'fadeInDown', 'wp-subscribe' ),
				'fadeInDownBig'  => esc_html__( 'fadeInDownBig', 'wp-subscribe' ),
				'fadeInLeft'     => esc_html__( 'fadeInLeft', 'wp-subscribe' ),
				'fadeInLeftBig'  => esc_html__( 'fadeInLeftBig', 'wp-subscribe' ),
				'fadeInRight'    => esc_html__( 'fadeInRight', 'wp-subscribe' ),
				'fadeInRightBig' => esc_html__( 'fadeInRightBig', 'wp-subscribe' ),
				'fadeInUp'       => esc_html__( 'fadeInUp', 'wp-subscribe' ),
				'fadeInUpBig'    => esc_html__( 'fadeInUpBig', 'wp-subscribe' ),
			),
			esc_html__( 'Flippers', 'wp-subscribe' ) => array(
				'flipInX' => esc_html__( 'flipInX', 'wp-subscribe' ),
				'flipInY' => esc_html__( 'flipInY', 'wp-subscribe' ),
			),
			esc_html__( 'Lightspeed', 'wp-subscribe' ) => array(
				'lightSpeedIn' => esc_html__( 'lightSpeedIn', 'wp-subscribe' ),
			),
			esc_html__( 'Rotating Entrances', 'wp-subscribe' ) => array(
				'rotateIn'          => esc_html__( 'rotateIn', 'wp-subscribe' ),
				'rotateInDownLeft'  => esc_html__( 'rotateInDownLeft', 'wp-subscribe' ),
				'rotateInDownRight' => esc_html__( 'rotateInDownRight', 'wp-subscribe' ),
				'rotateInUpLeft'    => esc_html__( 'rotateInUpLeft', 'wp-subscribe' ),
				'rotateInUpRight'   => esc_html__( 'rotateInUpRight', 'wp-subscribe' ),
			),
			esc_html__( 'Roll', 'wp-subscribe' ) => array(
				'rollIn' => esc_html__( 'rollIn', 'wp-subscribe' ),
			),
			esc_html__( 'Zoom Entrances', 'wp-subscribe' ) => array(
				'zoomIn'      => esc_html__( 'zoomIn', 'wp-subscribe' ),
				'zoomInDown'  => esc_html__( 'zoomInDown', 'wp-subscribe' ),
				'zoomInLeft'  => esc_html__( 'zoomInLeft', 'wp-subscribe' ),
				'zoomInRight' => esc_html__( 'zoomInRight', 'wp-subscribe' ),
				'zoomInUp'    => esc_html__( 'zoomInUp', 'wp-subscribe' ),
			),
			esc_html__( 'Slide Entrances', 'wp-subscribe' ) => array(
				'slideInDown'  => esc_html__( 'slideInDown', 'wp-subscribe' ),
				'slideInLeft'  => esc_html__( 'slideInLeft', 'wp-subscribe' ),
				'slideInRight' => esc_html__( 'slideInRight', 'wp-subscribe' ),
				'slideInUp'    => esc_html__( 'slideInUp', 'wp-subscribe' ),
			),
		);
	} else {
		$animations = array(
			// Exits.
			esc_html__( 'Bouncing Exits', 'wp-subscribe' ) => array(
				'bounceOut'      => esc_html__( 'bounceOut', 'wp-subscribe' ),
				'bounceOutDown'  => esc_html__( 'bounceOutDown', 'wp-subscribe' ),
				'bounceOutLeft'  => esc_html__( 'bounceOutLeft', 'wp-subscribe' ),
				'bounceOutRight' => esc_html__( 'bounceOutRight', 'wp-subscribe' ),
				'bounceOutUp'    => esc_html__( 'bounceOutUp', 'wp-subscribe' ),
			),
			esc_html__( 'Fading Exits', 'wp-subscribe' ) => array(
				'fadeOut'         => esc_html__( 'fadeOut', 'wp-subscribe' ),
				'fadeOutDown'     => esc_html__( 'fadeOutDown', 'wp-subscribe' ),
				'fadeOutDownBig'  => esc_html__( 'fadeOutDownBig', 'wp-subscribe' ),
				'fadeOutLeft'     => esc_html__( 'fadeOutLeft', 'wp-subscribe' ),
				'fadeOutLeftBig'  => esc_html__( 'fadeOutLeftBig', 'wp-subscribe' ),
				'fadeOutRight'    => esc_html__( 'fadeOutRight', 'wp-subscribe' ),
				'fadeOutRightBig' => esc_html__( 'fadeOutRightBig', 'wp-subscribe' ),
				'fadeOutUp'       => esc_html__( 'fadeOutUp', 'wp-subscribe' ),
				'fadeOutUpBig'    => esc_html__( 'fadeOutUpBig', 'wp-subscribe' ),
			),
			esc_html__( 'Flipper Exits', 'wp-subscribe' ) => array(
				'flipOutX' => esc_html__( 'flipOutX', 'wp-subscribe' ),
				'flipOutY' => esc_html__( 'flipOutY', 'wp-subscribe' ),
			),
			esc_html__( 'Lightspeed Exit', 'wp-subscribe' ) => array(
				'lightSpeedOut' => esc_html__( 'lightSpeedOut', 'wp-subscribe' ),
			),
			esc_html__( 'Rotating Exits', 'wp-subscribe' ) => array(
				'rotateOut'          => esc_html__( 'rotateOut', 'wp-subscribe' ),
				'rotateOutDownLeft'  => esc_html__( 'rotateOutDownLeft', 'wp-subscribe' ),
				'rotateOutDownRight' => esc_html__( 'rotateOutDownRight', 'wp-subscribe' ),
				'rotateOutUpLeft'    => esc_html__( 'rotateOutUpLeft', 'wp-subscribe' ),
				'rotateOutUpRight'   => esc_html__( 'rotateOutUpRight', 'wp-subscribe' ),
			),
			esc_html__( 'Roll Exit', 'wp-subscribe' ) => array(
				'rollOut' => esc_html__( 'rollOut', 'wp-subscribe' ),
			),
			esc_html__( 'Zoom Exits', 'wp-subscribe' ) => array(
				'zoomOut'      => esc_html__( 'zoomOut', 'wp-subscribe' ),
				'zoomOutDown'  => esc_html__( 'zoomOutDown', 'wp-subscribe' ),
				'zoomOutLeft'  => esc_html__( 'zoomOutLeft', 'wp-subscribe' ),
				'zoomOutRight' => esc_html__( 'zoomOutRight', 'wp-subscribe' ),
				'zoomOutUp'    => esc_html__( 'zoomOutUp', 'wp-subscribe' ),
			),
			esc_html__( 'Slide Exits', 'wp-subscribe' ) => array(
				'slideOutDown'  => esc_html__( 'slideOutDown', 'wp-subscribe' ),
				'slideOutLeft'  => esc_html__( 'slideOutLeft', 'wp-subscribe' ),
				'slideOutRight' => esc_html__( 'slideOutRight', 'wp-subscribe' ),
				'slideOutUp'    => esc_html__( 'slideOutUp', 'wp-subscribe' ),
			),
		);
	}

	printf( '<select id="%1$s" name="%2$s">', $id, $name );
		wps_print_select_options( $animations, $value );
	echo '</select>';
}

function wps_print_select_options( $options, $value ) {

	foreach( $options as $key => $text ) {

		if( is_array( $text ) ) {
			printf( '<optgroup label="%s">', $key );
				wps_print_select_options( $text, $value );
			echo '</optgroup>';
		}
		else {
			printf(
				'<option value="%1$s"%3$s>%2$s</option>',
				$key, $text,
				selected( $value, $key, false )
			);
		}
	}
}

// ---------------- SERVICE HELPERS ---------------------------------

/**
 * Get subscription service info
 *
 * @param  string	$id
 * @return string
 */
function wps_get_subscription_info( $id ) {

	$services = wps_get_mailing_services();

	return isset( $services[$id] ) ? $services[$id] : null;
}

/**
 * Get subscription service class instance
 *
 * @param  string 	$id
 * @return object
 */
function wps_get_subscription_service( $id ) {

	$info = wps_get_subscription_info( $id );

	if( is_null( $info ) ) {
		return;
	}

	return new $info['class']( $info );
}

/**
 * Get service list stored in db as trasient
 *
 * @param  string 	$name
 * @return array
 */
function wps_get_service_list( $name = '' ) {

	if( !$name ) {
		return;
	}

	$list = get_option( 'mts_wps_'. $name . '_lists' );

	// No option set, move data from transient if available ( v1.5.14 and below )
	if ( !$list && false !== ( $old_list = get_transient( 'mts_wps_'. $name . '_lists' ) ) ) {

		update_option( 'mts_wps_'. $name . '_lists', $old_list );
		delete_transient( 'mts_wps_'. $name . '_lists' );
		$list = $old_list;
	}

	return empty( $list ) ? array() : $list;
}
