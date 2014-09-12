<?php
if(!defined('ABSPATH')) {
	exit;
}

//required files below not needed, as they don't exist in Publications plugin

//require_once( EL_PATH.'includes/db.php' );
//require_once( EL_PATH.'includes/options.php' );
//require_once( EL_PATH.'includes/categories.php' );

// This class handles the navigation and filter bar
class Pub_Filterbar {
	private static $instance;
//	private $db;
//	private $options;
//	private $categories;

	public static function &get_instance() {
		// Create class instance if required
		if( !isset( self::$instance ) ) {
			self::$instance = new Pub_Filterbar();
		}
		// Return class instance
		return self::$instance;
	}

//	private function __construct() {
//		$this->db = &EL_Db::get_instance();
//		$this->options = &EL_Options::get_instance();
//		$this->categories = &EL_Categories::get_instance();
//	}

	// main function to show the rendered HTML output
	public function show($url, &$args) {
		$out = '
				<style type="text/css">
					.filterbar { display:table; width:100% }
					.filterbar > div { display:table-cell }
				</style>
				<!--[if lte IE 7]>
				<style>.filterbar > div { float:left }</style>
				<![endif]-->
				<div class="filterbar subsubsub">';
		// prepare filterbar-items
		//split 3 section (left, center, right) seperated by semicolon
		$sections = explode(";", $args['filterbar_items']);
		$section_align = array('left', 'center', 'right');
		for($i=0; $i<sizeof($sections) && $i<3; $i++) {
			if(strlen($sections[$i]) > 0) {
				$out .= '
					<div style="text-align:'.$section_align[$i].'">';
				//split items in section seperated by comma
				$items = explode(",", $sections[$i]);
				foreach($items as $item) {
					//search for item options
					$options = array();
					$item_array = explode("(", $item);
					if(sizeof($item_array) > 1) {
						// options available
						$option_array = explode("|", substr($item_array[1],0,-1));
						foreach($option_array as $option_text) {
							$o = explode("=", $option_text);
							$options[$o[0]] = $o[1];
						}
					}
					$item_array = explode("_", $item_array[0]);
					switch($item_array[0]) {
						case 'years':
							$out .= $this->show_years($url, $args, $item_array[1], 'std', $options);
							break;
						case 'cats':
							$out .= $this->show_cats($url, $args, $item_array[1], 'std', $options);
							break;
						case 'reset':
							$out .= $this->show_reset($url, $args, $options);
					}
				}
				$out .= '
					</div>';
			}
		}
		$out .= '</div>';
		return $out;
	}

	public function show_years($atts) {
		
		
		// $term_id is the ID for the "Years" parent publication-category
		$term_id = 21;
		
		$taxonomy_name = 'publication-category';
		$termchildren = get_term_children( $term_id, $taxonomy_name );

		return $termchildren;
		
		}
	
	public function show_author($atts) {
	
		// $term_id is the ID for the "Author" parent publication-category
		$term_id = 3;
		
		$taxonomy_name = 'publication-category';
		$termchildren = get_term_children( $term_id, $taxonomy_name );

		return $termchildren;

	}


	public function show_cats($atts) {
		
		// $term_id is the ID for the "Subject" parent publication-category
		$term_id = 22;
		
		$taxonomy_name = 'publication-category';
		$termchildren = get_term_children( $term_id, $taxonomy_name );

		return $termchildren;
		
	}


	public function show_reset($url, $args, $options) {
		$args_to_remove = array('event_id'.$args['sc_id_for_url'],
		                        'date'.$args['sc_id_for_url'],
		                        'cat'.$args['sc_id_for_url']);
		if(!isset($options['caption'])) {
			$options['caption'] = 'Reset';
		}
		return $this->show_link(remove_query_arg($args_to_remove, $url), __($options['caption']), 'link');
	}

	private function show_hlist($elements, $url, $name, $actual=null) {
		$out = '<ul class="hlist">';
		foreach($elements as $element) {
			$out .= '<li>';
			if($actual == $element['slug']) {
				$out .= '<strong>'.$element['name'].'</strong>';
			}
			else {
				$out .= $this->show_link(add_query_arg($name, $element['slug'], $url), $element['name']);
			}
			$out .= '</li>';
		}
		$out .= '</ul>';
		return $out;
	}

	private function show_dropdown($elements, $name, $subtype='std', $actual=null, $sc_id='') {
		$onchange = '';
		if('admin' != $subtype) {
			wp_register_script('pub_filterbar', EL_URL.'includes/js/filterbar.js', null, true);
			add_action('wp_footer', array(&$this, 'footer_script'));
			$onchange = ' onchange="eventlist_redirect(this.name,this.value,'.$sc_id.')"';
		}
		$out = '<select class="dropdown" name="'.$name.'"'.$onchange.'>';
		foreach($elements as $element) {
			$out .= '
					<option';
			if($element['slug'] == $actual) {
				$out .= ' selected="selected"';
			}
			$out .= ' value="'.$element['slug'].'">'.esc_html($element['name']).'</option>';
		}
		$out .= '
				</select>';
		return $out;
	}

	private function all_element($name=null) {
		if(null == $name) {
			$name = __('All');
		}
		return array('slug' => 'all', 'name' => $name);
	}

	private function parse_args($args) {
		$defaults = array('date' => null, 'event_id' => null, 'sc_id_for_url' => null);
		$args = wp_parse_args($args, $defaults);
		return $args;
	}

	public function footer_script() {
		wp_print_scripts('pub_filterbar');
	}
}
?>
