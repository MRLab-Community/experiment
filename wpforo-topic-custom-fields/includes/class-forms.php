<?php
if( ! class_exists( 'wpForoTcf_Form' ) ) {
	class wpForoTcf_Form {
		private $form;
		private $default;

		public function __construct() {
			$this->init_defaults();
			$this->reset_current();
			add_action( 'wpforo_after_init', [ $this, 'init' ] );
		}

		public function init() {
			add_action( 'wpforotcf_after_form_add', [ $this, 'after_form_save' ] );
			add_action( 'wpforotcf_after_form_edit', [ $this, 'after_form_save' ] );
		}

		private function init_defaults() {
			$this->default                  = new stdClass();
			$this->default->form            = [
				'formid'       => 0,
				'title'        => '',
				'type'         => 'topic',
				'structure'    => [],
				'fields'       => [],
				'fields_trash' => [],
				'forumids'     => [],
				'groupids'     => [],
				'locale'       => '',
				'is_default'   => false,
				'status'       => true,
			];
			$this->default->form_format     = [
				'formid'       => '%d',
				'title'        => '%s',
				'type'         => '%s',
				'structure'    => '%s',
				'fields'       => '%s',
				'fields_trash' => '%s',
				'forumids'     => '%s',
				'groupids'     => '%s',
				'locale'       => '%s',
				'is_default'   => '%d',
				'status'       => '%d',
			];
			$this->default->sql_select_args = [
				'title_like'       => null,
				'title_notlike'    => null,
				'locale_like'      => null,
				'locale_notlike'   => null,
				'locale_empty'     => null,
				'is_default'       => null,
				'status'           => null,
				'type_include'     => [],
				'type_exclude'     => [],
				'formid_include'   => [],
				'formid_exclude'   => [],
				'forumids_include' => [],
				'forumids_exclude' => [],
				'forumids_empty'   => null,
				'groupids_include' => [],
				'groupids_exclude' => [],
				'groupids_empty'   => null,
				'orderby'          => null,
				'offset'           => null,
				'row_count'        => null,
			];
		}

		/**
		 * @param $form
		 *
		 * @return array
		 */
		private function encode( $form ) {
			$form                 = $this->decode( $form );
			$form['structure']    = json_encode( $form['structure'] );
			$form['fields']       = json_encode( $form['fields'] );
			$form['fields_trash'] = json_encode( $form['fields_trash'] );
			$form['forumids']     = implode( ',', $form['forumids'] );
			$form['groupids']     = implode( ',', $form['groupids'] );
			$form['is_default']   = intval( $form['is_default'] );
			$form['status']       = intval( $form['status'] );

			return $form;
		}

		/**
		 * @param $form
		 *
		 * @return array
		 */
		public function decode( $form ) {
			$form                 = array_merge( $this->default->form, (array) $form );
			$form['formid']       = intval( $form['formid'] );
			$form['title']        = trim( strip_tags( (string) $form['title'] ) );
			$form['structure']    = (array) ( ( wpforo_is_json( $form['structure'] ) ) ? json_decode( $form['structure'], true ) : $form['structure'] );
			$form['fields']       = array_filter( (array) ( ( wpforo_is_json( $form['fields'] ) ) ? json_decode( $form['fields'], true ) : $form['fields'] ) );
			$form['fields_trash'] = array_filter( (array) ( ( wpforo_is_json( $form['fields_trash'] ) ) ? json_decode( $form['fields_trash'], true ) : $form['fields_trash'] ) );
			$form['forumids']     = array_unique( array_filter( array_map( 'intval', (array) ( ( is_scalar( $form['forumids'] ) ) ? explode( ',', $form['forumids'] ) : $form['forumids'] ) ) ) );
			$form['groupids']     = array_unique( array_filter( array_map( 'intval', (array) ( ( is_scalar( $form['groupids'] ) ) ? explode( ',', $form['groupids'] ) : $form['groupids'] ) ) ) );
			$form['is_default']   = (bool) intval( $form['is_default'] );
			if( $form['is_default'] ) $form['forumids'] = $form['groupids'] = [];
			$form['status'] = (bool) intval( $form['status'] );
			$form['type']   = trim( strip_tags( (string) $form['type'] ) );
			if( ! $form['type'] ) $form['type'] = 'topic';
			$form['locale'] = trim( strip_tags( (string) $form['locale'] ) );
			if( ! $form['locale'] ) $form['locale'] = WPF()->locale;

			return $form;
		}

		/**
		 * @param $form
		 *
		 * @return false|int
		 */
		public function add( $form ) {
			$form = $this->encode( $form );
			unset( $form['formid'] );
			$form = wpforo_array_ordered_intersect_key( $form, $this->default->form_format );
			if( WPF()->db->insert(
				WPF()->tables->forms,
				$form,
				wpforo_array_ordered_intersect_key( $this->default->form_format, $form )
			) ) {
				$form['formid'] = WPF()->db->insert_id;

				do_action( 'wpforotcf_after_form_add', $form['formid'] );

				return $form['formid'];
			}

			return false;
		}

		/**
		 * @param $fields
		 * @param $formid
		 *
		 * @return bool
		 */
		public function edit( $fields, $formid ) {
			if( ! $formid = intval( $formid ) ) return false;

			$fields = wpforo_array_ordered_intersect_key( $fields, $this->default->form_format );
			if( false !== WPF()->db->update(
					WPF()->tables->forms, wpforo_array_ordered_intersect_key( $this->encode( $fields ), $fields ), [ 'formid' => $formid ], wpforo_array_ordered_intersect_key( $this->default->form_format, $fields ), [ '%d' ]
				) ) {
				do_action( 'wpforotcf_after_form_edit', $formid );

				return true;
			}

			return false;
		}

		public function after_form_save( $formid ) {
			if( $form = $this->get_form( $formid ) ) {
				if( $form['status'] && $form['is_default'] ) {
					$sql = "UPDATE `" . WPF()->tables->forms . "` SET `status` = 0 WHERE `is_default` = 1 AND `locale` = %s AND `formid` <> %d";
					WPF()->db->query( WPF()->db->prepare( $sql, $form['locale'], $form['formid'] ) );
				}
			}
		}

		/**
		 * @param $formid
		 *
		 * @return bool
		 */
		public function delete( $formid ) {
			if( ! $formid = intval( $formid ) ) return false;

			if( false !== WPF()->db->delete(
					WPF()->tables->forms, [ 'formid' => $formid ], [ '%d' ]
				) ) {
				return true;
			}

			return false;
		}

		private function parse_args( $args ) {
			$args                     = wpforo_parse_args( $args, $this->default->sql_select_args );
			$args                     = wpforo_array_ordered_intersect_key( $args, $this->default->sql_select_args );
			$args['type_include']     = wpforo_parse_args( $args['type_include'] );
			$args['type_exclude']     = wpforo_parse_args( $args['type_exclude'] );
			$args['formid_include']   = wpforo_parse_args( $args['formid_include'] );
			$args['formid_exclude']   = wpforo_parse_args( $args['formid_exclude'] );
			$args['forumids_include'] = wpforo_parse_args( $args['forumids_include'] );
			$args['forumids_exclude'] = wpforo_parse_args( $args['forumids_exclude'] );
			$args['groupids_include'] = wpforo_parse_args( $args['groupids_include'] );
			$args['groupids_exclude'] = wpforo_parse_args( $args['groupids_exclude'] );

			return $args;
		}

		/**
		 * @param string $field
		 * @param array $values
		 * @param bool $not
		 * @param string $gate
		 *
		 * @return string
		 */
		private function build_sql_find_in_set( $field, $values, $not = false, $gate = 'AND' ) {
			$where  = '';
			$wheres = [];
			$not    = ( $not ? 'NOT ' : '' );
			foreach( $values as $value ) if( is_scalar( $value ) ) $wheres[] = $not . "FIND_IN_SET( '" . $value . "', IFNULL( `" . $field . "`, '' ) )";

			if( $wheres ) $where = "( " . implode( ' ' . $gate . ' ', $wheres ) . " )";

			return $where;
		}

		private function build_sql_select( $args, $select = '' ) {
			$args = $this->parse_args( $args );
			if( ! $select ) $select = '*';

			$wheres = [];

			if( ! is_null( $args['title_like'] ) ) $wheres[] = "`title` LIKE '%" . esc_sql( $args['title_like'] ) . "%'";
			if( ! is_null( $args['title_notlike'] ) ) $wheres[] = "`title` NOT LIKE '%" . esc_sql( $args['title_notlike'] ) . "%'";

			if( ! is_null( $args['locale_like'] ) ) {
				$locale_like = "`locale` LIKE '%" . esc_sql( $args['locale_like'] ) . "%'";
				if( $args['locale_empty'] ) {
					$locale_like = "( `locale` = '' OR `locale` IS NULL OR " . $locale_like . " )";
				}
				$wheres[] = $locale_like;
			}
			if( ! is_null( $args['locale_notlike'] ) ) {
				$locale_notlike = "`locale` NOT LIKE '%" . esc_sql( $args['locale_notlike'] ) . "%'";
				if( ! is_null( $args['locale_empty'] ) ) {
					if( $args['locale_empty'] ) {
						$locale_notlike = "( `locale` = '' OR `locale` IS NULL OR " . $locale_notlike . " )";
					} else {
						$locale_notlike = "( `locale` <> '' AND `locale` IS NOT NULL AND " . $locale_notlike . " )";
					}
				}
				$wheres[] = $locale_notlike;
			}
			if( ! is_null( $args['locale_empty'] ) && is_null( $args['locale_like'] ) && is_null( $args['locale_notlike'] ) ) {
				if( $args['locale_empty'] ) {
					$wheres[] = "( `locale` = '' OR `locale` IS NULL )";
				} else {
					$wheres[] = "( `locale` <> '' AND `locale` IS NOT NULL )";
				}
			}

			if( ! is_null( $args['is_default'] ) ) $wheres[] = "`is_default` = '" . intval( $args['is_default'] ) . "'";
			if( ! is_null( $args['status'] ) ) $wheres[] = "`status` = '" . intval( $args['status'] ) . "'";

			if( ! empty( $args['type_include'] ) ) $wheres[] = "`type` IN('" . implode( "','", array_map( 'trim', $args['type_include'] ) ) . "')";
			if( ! empty( $args['type_exclude'] ) ) $wheres[] = "`type` NOT IN(" . implode( "','", array_map( 'trim', $args['type_exclude'] ) ) . "')";

			if( ! empty( $args['formid_include'] ) ) $wheres[] = "`formid` IN(" . implode( ',', array_map( 'intval', $args['formid_include'] ) ) . ")";
			if( ! empty( $args['formid_exclude'] ) ) $wheres[] = "`formid` NOT IN(" . implode( ',', array_map( 'intval', $args['formid_exclude'] ) ) . ")";

			if( ! empty( $args['forumids_include'] ) ) {
				$forumids_include = $this->build_sql_find_in_set( 'forumids', array_map( 'intval', $args['forumids_include'] ) );
				if( $args['forumids_empty'] ) {
					$forumids_include = "( `forumids` = '' OR `forumids` IS NULL OR " . $forumids_include . " )";
				}
				$wheres[] = $forumids_include;
			}
			if( ! empty( $args['forumids_exclude'] ) ) {
				$forumids_exclude = $this->build_sql_find_in_set( 'forumids', array_map( 'intval', $args['forumids_exclude'] ), true );
				if( ! is_null( $args['forumids_empty'] ) ) {
					if( $args['forumids_empty'] ) {
						$forumids_exclude = "( `forumids` = '' OR `forumids` IS NULL OR " . $forumids_exclude . " )";
					} else {
						$forumids_exclude = "( `forumids` <> '' AND `forumids` IS NOT NULL AND " . $forumids_exclude . " )";
					}
				}
				$wheres[] = $forumids_exclude;
			}
			if( ! is_null( $args['forumids_empty'] ) && empty( $args['forumids_include'] ) && empty( $args['forumids_exclude'] ) ) {
				if( $args['forumids_empty'] ) {
					$wheres[] = "( `forumids` = '' OR `forumids` IS NULL )";
				} else {
					$wheres[] = "( `forumids` <> '' AND `forumids` IS NOT NULL )";
				}
			}

			if( ! empty( $args['groupids_include'] ) ) {
				$groupids_include = $this->build_sql_find_in_set( 'groupids', array_map( 'intval', $args['groupids_include'] ) );
				if( $args['groupids_empty'] ) {
					$groupids_include = "( `groupids` = '' OR `groupids` IS NULL OR " . $groupids_include . " )";
				}
				$wheres[] = $groupids_include;
			}
			if( ! empty( $args['groupids_exclude'] ) ) {
				$groupids_exclude = $this->build_sql_find_in_set( 'groupids', array_map( 'intval', $args['groupids_exclude'] ), true );
				if( ! is_null( $args['groupids_empty'] ) ) {
					if( $args['groupids_empty'] ) {
						$groupids_exclude = "( `groupids` = '' OR `groupids` IS NULL OR " . $groupids_exclude . " )";
					} else {
						$groupids_exclude = "( `groupids` <> '' AND `groupids` IS NOT NULL AND " . $groupids_exclude . " )";
					}
				}
				$wheres[] = $groupids_exclude;
			}
			if( ! is_null( $args['groupids_empty'] ) && empty( $args['groupids_include'] ) && empty( $args['groupids_exclude'] ) ) {
				if( $args['groupids_empty'] ) {
					$wheres[] = "( `groupids` = '' OR `groupids` IS NULL )";
				} else {
					$wheres[] = "( `groupids` <> '' AND `groupids` IS NOT NULL )";
				}
			}

			$wheres = array_filter( $wheres );

			$sql = "SELECT $select FROM " . WPF()->tables->forms;
			if( $wheres ) $sql .= " WHERE " . implode( " AND ", $wheres );
			if( $args['orderby'] ) $sql .= " ORDER BY " . $args['orderby'];
			if( $args['row_count'] ) $sql .= " LIMIT " . intval( $args['offset'] ) . "," . intval( $args['row_count'] );

			return $sql;
		}

		/**
		 * @param array|numeric $args
		 *
		 * @return array
		 */
		public function get_form( $args ) {
			if( is_numeric( $args ) ) $args = [ 'formid_include' => (int) $args ];
			if( ! wpfkey( $args, 'orderby' ) ) $args['orderby'] = '`is_default` ASC, `formid` DESC';
			if( ! wpfkey( $args, 'row_count' ) ) {
				$args['offset']    = 0;
				$args['row_count'] = 1;
			}
			$form = (array) WPF()->db->get_row( $this->build_sql_select( $args ), ARRAY_A );
			if( $form ) $form = $this->decode( $form );

			return $form;
		}

		/**
		 * @param array $args
		 *
		 * @return array
		 */
		public function get_forms( $args = [] ) {
			return array_map( [ $this, 'decode' ], (array) WPF()->db->get_results( $this->build_sql_select( $args ), ARRAY_A ) );
		}

		/**
		 * @param array $args
		 *
		 * @return int
		 */
		public function get_count( $args = [] ) {
			return (int) WPF()->db->get_var( $this->build_sql_select( $args, 'COUNT(*)' ) );
		}

		/**
		 * @param string $locale
		 * @param array $formids_exclude
		 *
		 * @return array
		 */
		public function get_all_used_forumids( $locale = '', $formids_exclude = [] ) {
			$formids_exclude = array_filter( array_map( 'intval', (array) $formids_exclude ) );
			$sql             = "SELECT GROUP_CONCAT(NULLIF(`forumids`, '')) AS all_forumids FROM " . WPF()->tables->forms;
			$wheres          = [];
			if( $locale ) $wheres[] = "`locale` = '" . $locale . "'";
			if( $formids_exclude ) $wheres[] = "`formid` NOT IN(" . implode( ',', $formids_exclude ) . ")";
			if( $wheres ) $sql .= " WHERE " . implode( ' AND ', $wheres );
			$forumids = (string) WPF()->db->get_var( $sql );

			return array_filter( array_map( 'intval', explode( ',', $forumids ) ) );
		}

		public function reset_current() {
			$this->form = $this->default->form;
		}

		/**
		 * @param array $args
		 */
		public function init_current( $args = [] ) {
			if( $this->form['formid'] ) return;
			$form = [];
			if( ! ( $formid = (int) wpfval( $args, 'formid' ) ) ) {
				$groupids = (int) wpfval( $args, 'groupid' ) ?: WPF()->current_user_groupids;
				if( wpforo_is_admin() ) {
					if( wpfval( $_GET, 'page' ) === wpforo_prefix_slug( 'tcf' ) && wpfval( $_GET, 'wpfaction' ) === 'wpforotcf_save_form' ) {
						$formid = (int) wpfval( $_GET, 'formid' );
					}
				} elseif( ( $forumid = (int) wpfval( $args, 'forumid' ) ) || ( $forumid = (int) WPF()->current_object['forumid'] ) ) {
					$args = [
						'forumids_include' => $forumid,
						'forumids_empty'   => true,
						'groupids_include' => $groupids,
						'groupids_empty'   => true,
						'locale_like'      => ( $locale = (string) wpfval( $args, 'locale' ) ) ? $locale : WPF()->locale,
						'locale_empty'     => true,
						'type_include'     => ( $type = (string) wpfval( $args, 'type' ) ) ? $type : 'topic',
						'status'           => true,
					];
					$form = wpforo_ram_get( [ $this, 'get_form' ], $args );
				} elseif( wpfval( $args, 'template' ) === 'search' || WPF()->current_object['template'] === 'search' ) {
					$args   = [
						'groupids_include' => $groupids,
						'groupids_empty'   => true,
						'locale_like'      => ( $locale = (string) wpfval( $args, 'locale' ) ) ? $locale : WPF()->locale,
						'locale_empty'     => true,
						'type_include'     => ( $type = (string) wpfval( $args, 'type' ) ) ? $type : 'topic',
						'status'           => true,
					];
					$forms  = wpforo_ram_get( [ $this, 'get_forms' ], $args );
					$fields = $structure = [];
					foreach( $forms as $f ) {
						$perm = false;
						if( empty( $f['forumids'] ) ) {
							$perm = true;
						} else {
							foreach( $f['forumids'] as $forumid ) {
								if( WPF()->perm->forum_can( 'vf', $forumid, $groupids ) && WPF()->perm->forum_can( 'vt', $forumid, $groupids ) ) {
									$perm = true;
									break;
								}
							}
						}
						if( $perm ) {
							$fields    = array_merge( $fields, $f['fields'] );
							$structure = array_merge( $structure, $f['structure'] );
						}
					}

					if( $fields ) {
						$form              = $this->default->form;
						$form['formid']    = - 1;
						$form['fields']    = $fields;
						$form['structure'] = $structure;
					}
				}
			}
			if( $form || ( $formid && ( $form = wpforo_ram_get( [ $this, 'get_form' ], $formid ) ) ) ) $this->form = $form;
		}

		public function get_current() {
			return $this->form;
		}
	}
}
