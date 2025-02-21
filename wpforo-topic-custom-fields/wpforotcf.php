<?php
/*
* Plugin Name: wpForo - Topic Custom Fields
* Plugin URI: https://wpforo.com
* Description: Allows to create topic custom fields with a form builder.
* Author: gVectors Team
* Author URI: https://gvectors.com/
* Version: 3.2.1
* Text Domain: wpforo_tcf
* Domain Path: /languages
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;
if( ! defined( 'WPFOROTCF_VERSION' ) ) define( 'WPFOROTCF_VERSION', '3.2.1' );
if( ! defined( 'WPFOROTCF_WPFORO_REQUIRED_VERSION' ) ) define( 'WPFOROTCF_WPFORO_REQUIRED_VERSION', '2.1.7' );

define( 'WPFOROTCF_DIR', rtrim( str_replace( '//', '/', dirname( __FILE__ ) ), '/' ) );
define( 'WPFOROTCF_URL', rtrim( plugins_url( '', __FILE__ ), '/' ) );
define( 'WPFOROTCF_FOLDER', rtrim( plugin_basename( dirname( __FILE__ ) ), '/' ) );
define( 'WPFOROTCF_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'plugins_loaded', function() { load_plugin_textdomain( 'wpforo_tcf', false, basename( dirname( __FILE__ ) ) . '/languages/' ); } );

require_once WPFOROTCF_DIR . "/includes/gvt-api-manager.php";
new GVT_API_Manager( __FILE__, 'wpforo-tcf', 'wpforo_settings_page_top' );

if( ! class_exists( 'wpForoTcf' ) ) {
	class wpForoTcf {
		private static $_instance      = null;
		public         $form;
		public         $list_table;
		private        $editable_props = [
			'isDefault'       => 0,
			'fieldKey'        => '',
			'name'            => '',
			'title'           => '',
			'label'           => '',
			'placeholder'     => '',
			'description'     => '',
			'html'            => '',
			'values'          => '',
			'fileSize'        => '',
			'fileExtensions'  => '',
			'minLength'       => 0,
			'maxLength'       => 0,
			'faIcon'          => '',
			'isRequired'      => 0,
			'isEditable'      => 1,
			'isLabelFirst'    => 0,
			'isMultiChoice'   => 0,
			'isSearchable'    => 1,
			'isOnlyForGuests' => 0,
		];

		public static function instance() {
			if( is_null( self::$_instance ) ) self::$_instance = new self();

			return self::$_instance;
		}

		private function __construct() {
			$this->includes();
			$this->init_hooks();

			$this->form = new wpForoTcf_Form();

			if( is_admin() ) {
				add_action( 'wpforo_after_init', [ $this, 'init_list_table' ] );
			}
		}

		private function includes() {
			require_once( WPFOROTCF_DIR . '/includes/class-forms.php' );
		}

		public function init_list_table() {
			if( preg_match( '#wpforo-(?:\d+-)?tcf#iu', (string) wpfval( $_GET, 'page' ) ) ) {
				require_once( WPFOROTCF_DIR . '/includes/form-listtable.php' );
				$this->list_table = new wpForoTcfFormListTable();
				$this->list_table->prepare_items();
			}
		}

		private function init_hooks() {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 1000 );
			//			add_filter('custom_menu_order',                   array($this, 'admin_menu_order'), 99);
			add_action( 'admin_footer', [ $this, 'add_to_footer' ] );
			add_filter( 'wpforo_post_after_init_fields', [ $this, 'after_init_fields' ], 99, 3 );
			add_filter( 'wpforo_get_topic_fields_structure', [ $this, 'get_topic_fields_structure' ], 99, 2 );

			add_action( 'wpforo_action_wpforotcf_add_form', [ $this, 'add_form' ] );
			add_action( 'wpforo_action_wpforotcf_edit_form', [ $this, 'edit_form' ] );
			add_action( 'wpforo_action_wpforotcf_clone_form', [ $this, 'clone_form' ] );
			add_action( 'wpforo_action_wpforotcf_delete_form', [ $this, 'delete_form' ] );

			//			ajax actions
			add_action( 'wp_ajax_wpforotcf_save_structure', [ $this, 'save_structure' ] );
			add_action( 'wp_ajax_wpforotcf_save_fields', [ $this, 'save_fields' ] );
			add_action( 'wp_ajax_wpforotcf_append_field_values', [ $this, 'append_field_values' ] );
		}

		public function admin_bar_menu( $wp_admin_bar ) {
			if( wpforo_current_user_is( 'admin' ) ) {
				$args = [
					'id'     => 'wpftcf-fields',
					'title'  => __( 'Topic Fields', 'wpforo_tcf' ),
					'href'   => admin_url( 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) ),
					'parent' => 'wpf-addons',
				];
				$wp_admin_bar->add_node( $args );
			}
		}

		public function admin_menu_order( $menu_order ) {
			$c = wpforo_prefix_slug( 'community' );
			global $submenu;
			$menu = isset( $submenu[ $c ] ) ? $submenu[ $c ] : '';
			if( $menu && is_array( $menu ) ) {
				$before_index = 0;
				$index        = 0;
				$item         = '';
				foreach( $menu as $k => $v ) {
					if( $v && is_array( $v ) && isset( $v[2] ) ) {
						if( $v[2] === wpforo_prefix_slug( 'moderations' ) ) {
							$before_index = $k;
						} else if( $v[2] === wpforo_prefix_slug( 'tcf' ) ) {
							$item  = $submenu[ $c ][ $k ];
							$index = $k;
						}
					}
				}
				if( $item ) {
					array_splice( $menu, $before_index + 1, 0, [ $before_index + 1 => $item ] );
					unset( $menu[ $index + 1 ] );
					$submenu[ $c ] = $menu;
				}
			}

			return $menu_order;
		}

		public function admin_page() {
			require_once( WPFOROTCF_DIR . "/includes/admin.php" );
		}

		public function after_init_fields( $fields, $type, $forum ) {
			WPF_TCF()->form->reset_current();
			WPF_TCF()->form->init_current( [ 'type' => $type, 'forumid' => (int) wpfval( $forum, 'forumid' ) ] );
			$current_form = $this->form->get_current();
			if( $current_form['fields'] ) {
				$all_groupids = WPF()->usergroup->get_usergroups( 'groupid' );
				$all_groupids = array_map( 'intval', $all_groupids );

				foreach( $fields as $ok => $ov ) {
					if( (int) wpfval( $ov, 'isRemovable' ) && ! wpfkey( $current_form['fields'], $ok ) ) {
						unset( $fields[ $ok ] );
					}
				}

				foreach( $current_form['fields'] as $k => $f ) {
					$f             = (array) $f;
					$f['fieldKey'] = $f['name'] = $k;
					if( wpfval( $fields, $k ) ) {
						$f            = array_intersect_key( $f, $this->editable_props );
						$f['canEdit'] = $f['canView'] = $all_groupids;
						$fields[ $k ] = array_merge( $fields[ $k ], $f );
					} else {
						$f['isDefault'] = 0;
						$f['canEdit']   = $f['canView'] = $all_groupids;
						$fields[ $k ]   = $f;
					}
                    $fields[ $k ]['formid'] = $current_form['formid'];
				}
			}

			return $fields;
		}

		public function get_topic_fields_structure( $fields, $forum ) {
			WPF_TCF()->form->init_current( [ 'forumid' => (int) wpfval( $forum, 'forumid' ) ] );
			$form = WPF_TCF()->form->get_current();
			if( $form['structure'] ) $fields = $form['structure'];

			return $fields;
		}

		public function add_form() {
			check_admin_referer( 'wpforotcf-add-form' );
			$formid = 0;
			if( ! empty( $_POST['form'] ) ) {
				if( $formid = WPF_TCF()->form->add( $_POST['form'] ) ) {
					WPF()->notice->add( 'Form Save Done', 'success' );
				} else {
					WPF()->notice->add( 'Form Save Error', 'error' );
				}
			}
			$redirect_to = ( $formid ? 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) . '&wpfaction=wpforotcf_save_form&formid=' . $formid : 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) );
			wp_redirect( admin_url( $redirect_to ) );
			exit();
		}

		public function edit_form() {
			check_admin_referer( 'wpforotcf-edit-form' );
			$formid = 0;
			if( ! empty( $_POST['form'] ) && ( $formid = (int) wpfval( $_POST, 'form', 'formid' ) ) ) {
				if( WPF_TCF()->form->edit( $_POST['form'], $formid ) ) {
					WPF()->notice->add( 'Form Save Done', 'success' );
				} else {
					WPF()->notice->add( 'Form Save Error', 'error' );
				}
			}
			$redirect_to = ( $formid ? 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) . '&wpfaction=wpforotcf_save_form&formid=' . $formid : 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) );
			wp_redirect( admin_url( $redirect_to ) );
			exit();
		}

		public function clone_form() {
			$formid = (int) wpfval( $_REQUEST, 'formid' );
			check_admin_referer( 'wpforotcf-clone-' . $formid );

			if( $form = WPF_TCF()->form->get_form( $formid ) ) {
				$form['title'] .= ' - ' . __( 'CLONE', 'wpforo_tcf' );
				$form['status'] = false;
				if( $formid = WPF_TCF()->form->add( $form ) ) {
					WPF()->notice->add( 'Form Clone Done', 'success' );
				} else {
					$formid = 0;
					WPF()->notice->add( 'Form Clone Error', 'error' );
				}
			} else {
				$formid = 0;
				WPF()->notice->add( 'Form Not Found', 'error' );
			}

			$redirect_to = ( $formid ? 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) . '&wpfaction=wpforotcf_save_form&formid=' . $formid : 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) );
			wp_redirect( admin_url( $redirect_to ) );
			exit();
		}

		public function delete_form() {
			$formid = (int) wpfval( $_REQUEST, 'formid' );
			check_admin_referer( 'wpforotcf-delete-' . $formid );

			if( ( $form = WPF_TCF()->form->get_form( $formid ) ) && ! ( $form['is_default'] && $form['status'] ) ) {
				if( WPF_TCF()->form->delete( $formid ) ) {
					WPF()->notice->add( 'Form Delete Done', 'success' );
				} else {
					WPF()->notice->add( 'Form Delete Error', 'error' );
				}
			} else {
				WPF()->notice->add( 'Form Not Found', 'error' );
			}

			wp_redirect( admin_url( 'admin.php?page=' . wpforo_prefix_slug( 'tcf' ) ) );
			exit();
		}

		public function save_structure() {
			check_ajax_referer( 'wpftcf-ajax-nonce', 'nonce' );
			if( $formid = (int) wpfval( $_POST, 'formid' ) ) {
				if( ! empty( $_POST['structure'] ) ) {
					$structure = wp_unslash( $_POST['structure'] );
					if( wpforo_is_json( $structure ) ) {
						$structure = json_decode( $structure, true );
						$this->form->edit( [ 'structure' => $structure ], $formid );
					}
				}
			}
			die;
		}

		public function save_fields() {
			check_ajax_referer( 'wpftcf-ajax-nonce', 'nonce' );
			if( $formid = (int) wpfval( $_POST, 'formid' ) ) {
				$args = [
					'fields'       => [],
					'fields_trash' => [],
				];
				if( ! empty( $_POST['fields'] ) ) {
					$fields = wp_unslash( $_POST['fields'] );
					if( wpforo_is_json( $fields ) ) {
						$fields         = json_decode( $fields, true );
						$fields         = array_map( [ $this, 'field_values_to_array' ], $fields );
						$args['fields'] = $fields;
					}
				}
				if( ! empty( $_POST['fields_trash'] ) ) {
					$fields_trash = wp_unslash( $_POST['fields_trash'] );
					if( wpforo_is_json( $fields_trash ) ) {
						$fields_trash         = json_decode( $fields_trash, true );
						$fields_trash         = array_map( [ $this, 'field_values_to_array' ], $fields_trash );
						$args['fields_trash'] = $fields_trash;
					}
				}
				$this->form->edit( $args, $formid );
			}
			die;
		}

		public function append_field_values() {
            $r = false;
			wpforo_verify_nonce('wpforotcf_append_field_values');

			$idata = [
				'formid'   => 0,
				'fieldKey' => '',
				'value'    => '',
			];

            $data = wpforo_array_args_cast_and_merge( $_POST, $idata );
            $data = array_intersect_key( $data, $idata );

            if( $form = $this->form->get_form( $data['formid'] ) ){
                $field = wpfval( $form, 'fields', $data['fieldKey'] );
                if( $field && array_intersect( WPF()->current_user_groupids, (array) wpfval( $field, 'UGroupIdsFrontAddNewDefaultValues' ) ) ){
	                if( !wpfkey($field, 'values') ) $field['values'] = [];
	                $field['values'][] = $data['value'];
	                $field['values'] = array_unique( $field['values'] );
	                $form['fields'][$data['fieldKey']] = $field;

	                $r = $this->form->edit( $form, $data['formid'] );
                }
            }

           if( $r ){
               wp_send_json_success();
           }else{
               wp_send_json_error();
           }
        }

		public function admin_enqueue_scripts() {
			wp_register_style( 'wpforo-tcf-font-awesome', WPFORO_URL . '/assets/css/font-awesome/css/fontawesome-all.min.css', false, '5.15.3' );
			wp_register_style( 'wpforo-tcf-css', WPFOROTCF_URL . '/assets/css/tcf.css', [], WPFOROTCF_VERSION );
			wp_register_script( 'wpforo-tcf-js', WPFOROTCF_URL . '/assets/js/tcf.js', [ 'jquery-ui-sortable' ], WPFOROTCF_VERSION, true );

			wp_register_style( 'wpforo-tcf-iconpicker-css', WPFOROTCF_URL . '/assets/third-party/iconpicker/fontawesome-iconpicker.min.css', [ 'wpforo-tcf-font-awesome' ], '2.0.0' );
			wp_register_script( 'wpforo-tcf-iconpicker-js', WPFOROTCF_URL . '/assets/third-party/iconpicker/fontawesome-iconpicker.js', [ 'jquery' ], '2.0.0', true );

			if( wpfval( $_GET, 'page' ) === wpforo_prefix_slug( 'tcf' ) ) {
				WPF_TCF()->form->init_current();
				$current_form               = WPF_TCF()->form->get_current();
				$current_form['ajax_nonce'] = wp_create_nonce( 'wpftcf-ajax-nonce' );
				wp_enqueue_style( 'wpforo-tcf-css' );
				wp_enqueue_script( 'wpforo-tcf-js' );
				wp_enqueue_style( 'wpforo-tcf-font-awesome' );
				wp_localize_script( 'wpforo-tcf-js', 'wpftcf_current_form', $current_form );
                wp_localize_script( 'wpforo-tcf-js', 'wpforotcf', [
                    'ajax_url' => wpforo_get_ajax_url(),
                    'usergroups' => WPF()->settings->get_variants_usergroups([4]),
                ] );
				$tab = wpfval( $_GET, 'tab' );
				if( $tab === 'fields' ) {
					add_thickbox();
					wp_enqueue_style( 'wpforo-tcf-iconpicker-css' );
					wp_enqueue_script( 'wpforo-tcf-iconpicker-js' );

					$wpftcf       = array_merge( WPF()->post->get_fields(), $current_form['fields_trash'] );
					$wpftcf       = array_map( [ $this, 'field_values_to_string' ], $wpftcf );
					$wpftcf_trash = $current_form['fields_trash'];
					$wpftcf_trash = array_map( [ $this, 'field_values_to_string' ], $wpftcf_trash );
					wp_localize_script( 'wpforo-tcf-js', 'wpftcf', $wpftcf );
					wp_localize_script( 'wpforo-tcf-js', 'wpftcf_trash', $wpftcf_trash );
					wp_localize_script( 'wpforo-tcf-js', 'wpftcf_default_field', WPF()->form->default );
				}
			}
		}

		private function field_values_to_string( $field ) {
			if( $values = wpfval( $field, 'values' ) ) {
				if( is_array( $values ) ) {
					if( $field['type'] === 'select' ) {
						$string = '';
						foreach( $field['values'] as $k => $v ) {
							if( is_array( $v ) ) {
								$string .= "\n[optgroup=$k]\n" . implode( "\n", $v ) . "\n[/optgroup]";
							} else {
								$string .= "\n$v";
							}
						}
						$field['values'] = trim( $string );
					} else {
						$field['values'] = implode( "\n", $values );
					}
				}
			}

			return $field;
		}

		private function field_values_to_array( $field ) {
			if( $values = wpfval( $field, 'values' ) ) {
				if( is_scalar( $values ) ) {
					$field['values'] = explode( "\n", $values );
					if( $field['type'] === 'select' ) {
						$v        = [];
						$optgroup = '';
						foreach( $field['values'] as $value ) {
							if( preg_match( '#^ *\[optgroup *= *([^=\]]+?) *] *$#iu', (string) $value, $m ) ) {
								$optgroup       = $m[1];
								$v[ $optgroup ] = [];
							} elseif( preg_match( '#^ *\[ */ *optgroup *] *$#iu', (string) $value, $m ) ) {
								$optgroup = '';
							} else {
								if( $optgroup ) {
									$v[ $optgroup ][] = $value;
								} else {
									$v[] = $value;
								}
							}
						}
						$field['values'] = $v;
					}
				}
			}

			return $field;
		}

		public function print_field( $field, $type = 'topic', $action_buttons = false ) {
			$field = WPF()->post->get_field( $field, $type );
			if( ! $field ) return;
			$field = WPF()->post->fix_field( $field );
			printf(
				'<div id="%1$s" class="wpftcf-field %5$s %8$s" title="%7$s">
                    <div class="wpftcf-info">
                        <div class="wpftcf-field-ico">%2$s</div>
                        <div class="wpftcf-field-label"><span>%3$s  %6$s %9$s</span></div>
                    </div>
                    <div class="wpftcf-field-action-buttons">%4$s</div>
                </div>',
				$field['fieldKey'],
				! empty( $field['faIcon'] ) ? '<i class="' . $field['faIcon'] . '"></i>' : '',
				$field['label'] ?: ( $field['title'] ?: $field['fieldKey'] ),
				$action_buttons ? '<div class="wpftcf-action-edit"><span class="dashicons dashicons-edit"></span></div>
                    <div class="wpftcf-action-delete"><span class="dashicons dashicons-trash"></span></div>' : '',
				in_array( $field['fieldKey'], [ 'title', 'body' ] ) ? 'wpftcf-cant-be-inactive' : '',
				intval( $field['isOnlyForGuests'] ) ? '&nbsp;|&nbsp;<span class="wpftcf-guests-only" title="This field will be shown only for guests.">Guests Only</span>' : '',
				intval( $field['isOnlyForGuests'] ) ? 'This field will be shown only for guests.' : $field['description'],
				intval( $field['isRemovable'] ) ? '' : 'wpftcf-cant-be-removed',
				intval( $field['isRequired'] ) ? '&nbsp;<span class="wpftcf-required-asterisk" title="This field is required">*</span>' : ''
			);
		}

		public function add_to_footer() { ?>
            <div id="wpftcf-blank-field" style="display: none;">
                <div id="blank" class="wpftcf-field">
                    <div class="wpftcf-info">
                        <div class="wpftcf-field-ico"></div>
                        <div class="wpftcf-field-label"><span></span></div>
                    </div>
                    <div class="wpftcf-field-action-buttons">
                        <div class="wpftcf-action-edit"><span class="dashicons dashicons-edit"></span></div>
                        <div class="wpftcf-action-delete"><span class="dashicons dashicons-trash"></span></div>
                    </div>
                </div>
            </div>
            <div id="wpftcf-blank-row" style="display: none;">
                <div class="wpftcf-row" data-cols="1">
                    <div class="wpftcf-row-panel">
                        <div class="wpftcf-row-panel-title">- <?php _e( 'row', 'wpforo_tcf' ) ?> -</div>
                        <div class="wpftcf-row-panel-actions">
                            <div class="wpftcf-del-row" title="<?php _e( 'Delete This Row', 'wpforo_tcf' ) ?>">
                                <span class="dashicons dashicons-dismiss"></span>
                            </div>
                        </div>
                    </div>
                    <div class="wpftcf-row-body">
                        <div class="wpftcf-sortable wpftcf-sortable-cols">
                            <div class="wpftcf-col">
                                <div class="wpftcf-col-panel">
                                    <div class="wpftcf-col-panel-title">- <?php _e( 'col', 'wpforo_tcf' ) ?> -</div>
                                    <div class="wpftcf-col-panel-actions">
                                        <div class="wpftcf-del-col" title="<?php _e( 'Delete This Column', 'wpforo_tcf' ) ?>">
                                            <span class="dashicons dashicons-dismiss"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpftcf-place wpftcf-fields wpftcf-sortable wpftcf-sortable-fields"></div>
                            </div>
                        </div>
                        <div class="wpftcf-add-new-col" title="<?php _e( 'Add New Column', 'wpforo_tcf' ) ?>">
                            <span class="dashicons dashicons-plus-alt2"></span>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	if( ! function_exists( 'WPF_TCF' ) ) {
		function WPF_TCF() {
			return wpForoTcf::instance();
		}
	}

	require_once( WPFOROTCF_DIR . "/includes/functions.php" );
}
