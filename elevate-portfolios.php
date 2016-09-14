<?php
/**
 * Plugin Name: Elevate Portfolios
 * Plugin URI:  https://elevate360.com.au/plugins
 * Description: Showcases portfolios with an easy to use admin back-end. Contains a filterable listing page for portfolios plus a single portfolio showcase. Use a combination of
 * either shortcodes or action hooks to output content for your single portfolio pages. All portfolios are enriched with schema.org metadata  
 * Version:     1.0.0
 * Author:      Simon Codrington
 * Author URI:  https://simoncodrington.com.au
 * Text Domain: elevate-portfolios
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * TODO:
 * - Add custom widgets for this (display single card, multiple cards etc)
 * - Additional hooks / filters for developers (to help extension)
 * - More options potentially
 */

 
 //load our el-content-type
 if(!class_exists('el_content_type')){
 	include(plugin_dir_path(__FILE__) . '/inc/el-content-type.php');
 }
 
 //extends our universal content type class
 class el_portfolios extends el_content_type{
 	
	//single instance of class
	static $instance = null;
	
	//content type args
 	private $post_type_args = null;
	//metabox args
	private $meta_box_args = null;
	//metafields for metabox
	private $meta_field_args = null;
	//taxonomy args
	private $taxonomy_args = null;
	
	
	//constructor
	public function __construct(){
		
		
		//set up post type info
		$this->post_type_args = array(
			'post_type_name'		=> 'el_portfolio',
			'post_type_single_name'	=> 'portfolio',
			'post_type_plural_name'	=> 'Portfolios',
			'labels'				=> array(
				'menu_name'				=> 'Portfolios'
			),
			'args'					=> array(
				'menu_icon'				=> 'dashicons-images-alt2',
				'rewrite'				=> array(
					'slug'					=> 'portfolio'
				),
				'supports'				=> array('title','editor')
			)
		);
		
		//set up tax info
		$this->taxonomy_args = array(
			array(
				'taxonomy_name'			=> 'el_portfolio_category',
				'taxonomy_single_name'	=> 'Portfolio Category',
				'taxonomy_plural_name'	=> 'Portfolio Categories',
				'labels' 	=> array(
					'menu_name'  	=> 'Categories'
				),
				'args'		=> array(
					'hierarchical'	=> true,
					'rewrite'		=> array(
						'slug'			=> '/portfolios/category'
					)
				)
			),
			array(
				'taxonomy_name'			=> 'el_portfolio_tags',
				'taxonomy_single_name'	=> 'Portfolio Tag',
				'taxonomy_plural_name'	=> 'Portfolio Tags',
				'labels' 	=> array(
					'menu_name'  	=> 'Tags'
				),
				'args'		=> array(
					'hierarchical'	=> false,
					'rewrite'		=> array(
						'slug'			=> '/portfolios/tags'
					)
				)
			)
		);
		
		//set up metabox info
		$this->meta_box_args = array(
			array(
				'id'			=> 'listing_portfolio_metabox',
				'title'			=> 'Listing Portfolio Information',
				'context'		=> 'normal',
				'args'			=> array(
					'description' => 'Information used when viewing this portfolio on the listing page'
				)	
			),
			array(
				'id'			=> 'single_portfolio_gallery_metabox',
				'title'			=> 'Single Portfolio Gallery',
				'context'		=> 'normal',
				'args'			=> array(
					'description' => 'Information about the gallery used for this portfolio'
				)	
			
			)
		);
		
		//set up metafield info
		$this->meta_field_args = array(
		
			//archive page settings
			array(
				'id'			=> 'portfolio_archive_title',
				'title'			=> 'Portfolio Archive Title',
				'description'	=> 'Primary title displayed when viewing the portfolio on its listing page. If not supplied the portfolio page name will be used.',
				'type'			=> 'text',
				'meta_box_id'	=> 'listing_portfolio_metabox'
			),
			array(
				'id'			=> 'portfolio_archive_subtitle',
				'title'			=> 'Portfolio Archive Subtitle',
				'description'	=> 'secondary smaller subtitle displayed when viewing the portfolio on its listing page.',
				'type'			=> 'text',
				'meta_box_id'	=> 'listing_portfolio_metabox'
			),
			array(
				'id'			=> 'portfolio_archive_excerpt',
				'title'			=> 'Portfolio Archive Excerpt',
				'description'	=> 'An optional summary to display on the archive listing card. A blurb about the portfolio',
				'type'			=> 'textarea',
				'meta_box_id'	=> 'listing_portfolio_metabox'
			),
			array(
				'id'			=> 'portfolio_archive_image',
				'title'			=> 'Portfolio Archive Image',
				'description'	=> 'Image used as the background for the portfolio on the archive page',
				'type'			=> 'upload-image',
				'meta_box_id'	=> 'listing_portfolio_metabox'
			),
			array(
				'id'			=> 'portfolio_archive_overlay_color',
				'title'			=> 'Portfolio Archive Overlay Colour',
				'description'	=> 'Colour of the overlay used when hovering / interacting with the portfolio card',
				'type'			=> 'color',
				'meta_box_id'	=> 'listing_portfolio_metabox',
			),
			array(
				'id'			=> 'portfolio_archive_text_color',
				'title'			=> 'Portfolio Archive Text Colour',
				'description'	=> 'Colour of the title displayed on top of the portfolio overlay. Select a high contrast color. ',
				'type'			=> 'color',
				'meta_box_id'	=> 'listing_portfolio_metabox'
			),
			array(
				'id'			=> 'portfolio_archive_display_readmore',
				'title'			=> 'Portfolio Archive Readmore Button',
				'description'	=> 'Display or hide the readmore button on the portfolio listing page. ',
				'type'			=> 'select',
				'meta_box_id'	=> 'listing_portfolio_metabox',
				'args'			=> array(
					'options'		=> array(
						array(
							'id'		=> 'show',
							'title'		=> "Display Readmore Button"
						
						),
						array(
							'id'		=> 'hide',
							'title'		=> "Hide Readmore Button"
						)
					)
				)
			),
			array(
				'id'			=> 'portfolio_archive_show_categories',
				'title'			=> 'Portfolio Archive Show Categories',
				'description'	=> 'Display or hide the categories associated with this portfolio. Displayed under the title and subtitle',
				'type'			=> 'select',
				'meta_box_id'	=> 'listing_portfolio_metabox',
				'args'			=> array(
					'options'		=> array(
						array(
							'id'		=> 'show',
							'title'		=> "Display Categories"
						
						),
						array(
							'id'		=> 'hide',
							'title'		=> "Hide Categories"
						)
					)
				)
			),
			array(
				'id'			=> 'portfolio_archive_order',
				'title'			=> 'Portfolio Archive Order',
				'description'	=> 'Order of the portfolio item in relation to others when displayed on the listing page.',
				'type'			=> 'number',
				'meta_box_id'	=> 'listing_portfolio_metabox'
			),
			
			//single page settings
			array(
				'id'			=> 'portfolio_gallery_images',
				'title'			=> 'Portfolio Gallery Images',
				'description'	=> 'Images to be used on the single portfolio page in a grid',
				'type'			=> 'upload-multi-image',
				'meta_box_id'	=> 'single_portfolio_gallery_metabox'
			),
			array(
				'id'			=> 'portfolio_gallery_image_type',
				'title'			=> 'Portfolio Image Display Type',
				'description'	=> 'Determine if your gallery images will be displayed as \'background\' images (square and cropped) or as \'traditional\' images (unique sizes displayed with masonry)' ,
				'type'			=> 'select',
				'meta_box_id'	=> 'single_portfolio_gallery_metabox',
				'args'			=> array(
					'options'		=> array(
						array(
							'id'	=> 'background',
							'title' => 'Background Images'
						),
						array(
							'id'	=> 'traditional',
							'title' => 'Traditional Images'
						)
					)
				)
			),
			
		);
	
		//call parent constrcutor
		parent::__construct(
			$this->post_type_args, 
			$this->meta_box_args,
			$this->meta_field_args,
			$this->taxonomy_args,
			false); 	
		
			
		add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts_and_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles'));
		add_action('init', array($this, 'register_shortcodes'));
		add_action('et_builder_post_types', array($this, 'add_divi_support'));
		
		//action hooks to output content
		add_action('el_display_portfolio_listing', array($this, 'display_portfolio_listing'));
		add_action('el_display_portfolio_single', array($this, 'display_portfolio_single'));
		add_action('el_display_portfolio_gallery', array($this, 'display_portfolio_gallery')); 
		add_action('el_display_portfolio_categories', array($this, 'display_portfolio_categories'));
		add_action('el_display_portfolio_tags', array($this, 'display_portfolio_tags'));
		add_action('el_display_portfolio_pagination', array($this, 'display_portfolio_pagination'));
		add_action('el_display_portfolio_microdata_information', array($this, 'display_portfolio_microdata_information'));
		
		
		//TODO: Add universal settings to customizer
		//add_action('customize_register', array($this, 'register_customizer_settings')); //hooks into theme customizer for options
	}

	//add divi support so the content area can be used
	public function add_divi_support($post_types){
		
		$post_types[] = $this->post_type_args['post_type_name'];
		
		return $post_types;
	}

	//hook to disply a single portfolio item
	public static function display_portfolio_single($post_id){
			
		$instance = self::getInstance();
		$html = $instance->get_portfolio_single($post_id);
		
		echo $html;
	}
	
	//hook to display the portfolio listings, with optional arguments
	public static function display_portfolio_listing($optional_args = array()){
		
		$instance = self::getInstance();
		$html = $instance->get_portfolio_listing($optional_args);
		
		echo $html;
	}
	
	//displays project pagination
	public static function display_portfolio_pagination($post_id){
		$instance = self::getInstance();
		$html = $instance->get_portfolio_pagination($post_id);
		
		echo $html;
	}
	
	//displays the portfolio tags
	public static function display_portfolio_tags($post_id){
		$instance = self::getInstance();
		$html = $instance->get_portfolio_tags($post_id);
		
		echo $html;
	}

	//displays the portfolio categories
	public static function display_portfolio_categories($post_id){
		$instance = self::getInstance();
		$html = $instance->get_portfolio_categories($post_id);
		
		echo $html;
	}

	//display the gallery elements for a single portfolio
	public static function display_portfolio_gallery($post_id){
			
		$instance = self::getInstance();
		$html = $instance->get_portfolio_gallery($post_id);
	
		
		echo $html;
	}
	
	//outputs an entire microdata format element based off the 'services' schema, displayed as an invisible div
	//http://schema.org/Service
	public static function display_portfolio_microdata_information($post_id){
			
		$instance = self::getInstance();
		$html = '';
		
		$post = get_post($post_id);
		$html .= $instance->get_portfolio_microdata_information($post->ID);
		
		echo $html;
	}


	//gets the microdata in the form of a service card 
	public static function get_portfolio_microdata_information($post_id = null){
		
		$instance = self::getInstance();
		$html = '';
		
		//if no gallery id passed, check post
		if(is_null($post_id)){
			global $post;
			if($post){
				if(get_post_type($post) == $instance->post_type_args['post_type_name']){
					$post_id = $post->ID;
				}
			}	
		}

		$post = get_post($post_id);
		if($post){
				
			$post_title = $post->post_title;
			$post_permalink = get_permalink($post_id);
			$post_content = $post->post_content;
			
			$portfolio_archive_title = get_post_meta($post_id, 'portfolio_archive_title', true);	
			$portfolio_archive_subtitle = get_post_meta($post_id, 'portfolio_archive_subtitle', true);
			$portfolio_archive_excerpt = get_post_meta($post_id, 'portfolio_archive_excerpt', true);
			$portfolio_archive_image = get_post_meta($post_id, 'portfolio_archive_image', true);
			$portfolio_archive_display_readmore = get_post_meta($post_id, 'portfolio_archive_display_readmore', true);
			$portfolio_archive_show_categories = get_post_meta($post_id, 'portfolio_archive_show_categories', true);
			$portfolio_archive_overlay_color = get_post_meta($post_id, 'portfolio_archive_overlay_color', true);
			$portfolio_archive_text_color = get_post_meta($post_id, 'portfolio_archive_text_color', true);
			$portfolio_categories = wp_get_post_terms($post_id, 'el_portfolio_category');
			
			//get a listing of other portfolios for use as related items
			$other_post_args = array(
				'post_type'			=> $instance->post_type_args['post_type_name'],
				'posts_per_page'	=> 5,
				'post_status'		=> 'publish',
				'post__not_in'		=> array($post_id)
			);
			$other_posts = get_posts($other_post_args);
			
			
			$html .= '<div itemscope itemtype="http://schema.org/Service">';
				
				//title
				if(!empty($portfolio_archive_title)){
					$html .= '<meta itemprop="name" content="' . $portfolio_archive_title . '"></meta>';
				}else{
					if(!empty($post_title)){
						$html .= '<meta itemprop="name" content="' . $post_title . '"></meta>';
					}
				}
				
				//descriptions
				if(!empty($portfolio_archive_subtitle) || !empty($portfolio_archive_excerpt)){
					if(!empty($portfolio_archive_subtitle)){
						$html .= '<meta itemprop="description" content="' . $portfolio_archive_subtitle . '"></meta>';
					}
					if(!empty($portfolio_archive_excerpt)){
						$html .= '<meta itemprop="description" content="' . $portfolio_archive_excerpt . '"></meta>';
					}
				}else{
					//display trimmed content if no others selected
					$post_content = wp_trim_words($post_content, 25, '...');
					$html .= '<meta itemprop="description" content="' . $post_content . '"></meta>';
				}
				
				//image
				if(!empty($portfolio_archive_image)){
					$url_large = wp_get_attachment_image_src($portfolio_archive_image, 'large', true)[0];
					$html .= '<meta itemprop="image" content="' . $url_large . '"></meta>';
				}
				
				//categories
				if(!empty($portfolio_categories) && !is_a($portfolio_categories, 'WP_Error')){
					foreach($portfolio_categories as $category){
						$html .= '<meta itemprop="category" content="' . $category->name . '"></meta>';
					}
				}
				
				//related portfolios
				if(!empty($other_posts)){
					foreach($other_posts as $post){
						$html .= '<meta itemprop="isRelatedTo" content="' . get_permalink($post->ID) . '"></meta>';
					}
				}
				
				$html .= '<meta itemprop="serviceType" content="Portfolio"></meta>';
				$html .= '<meta itemprop="serviceOutput" content="Portfolio"></meta>';
				$html .= '<meta itemprop="url" content=" ' . $post_permalink . '"></meta>';
				
			$html .= '</div>';
			
		}
		
		
		return $html; 
	}
	
	//gets the single portfolio main content area
	public static function get_portfolio_content($post_id){
			
		$html = '';
		$instance = self::getInstance();
		
		$post = get_post($post_id);
		if($post){
			$portfolio_single_content = get_post_meta($post_id, 'portfolio_single_content', true);
			if(!empty($portfolio_single_content)){
				$html .= '<article class="portfolio-content">';
					$html .= $portfolio_single_content;
				$html .= '</article>';
			}
		}
		
		return $html; 
		
	}
	
	//gets the markup for the portfolio gallery
	public static function get_portfolio_gallery($post_id = null, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = '';
		
		//if no gallery id passed, check post
		if(is_null($post_id)){
			global $post;
			if($post){
				if(get_post_type($post) == $instance->post_type_args['post_type_name']){
					$post_id = $post->ID;
				}
			}	
		}

		$post = get_post($post_id);
		if($post){
			$portfolio_gallery_images = get_post_meta($post_id, 'portfolio_gallery_images', true);
			$portfolio_gallery_image_type = get_post_meta($post_id, 'portfolio_gallery_image_type', true);
			
			if(!empty($portfolio_gallery_images)){
				
				//filter for before gallery output	
				$html = apply_filters('el_gallery_before', $html); 
					
				$classes = isset($optional_args['items_per_row']) ? 'row-of-' . $optional_args['items_per_row'] . ' ' : 'row-of-3 ';
				$classes .= $portfolio_gallery_image_type;
				$html .= '<article class="portolio-gallery row-item ' . $classes .'">';
				
				$portfolio_gallery_images = json_decode($portfolio_gallery_images);

				foreach($portfolio_gallery_images as $image_id){
					
					$url_medium = wp_get_attachment_image_src($image_id, 'medium', false)[0];
					$url_large = wp_get_attachment_image_src($image_id, 'large', true)[0];

					//determine output for images
					$image = '';
					
					
					
					if($portfolio_gallery_image_type == 'background' || empty($portfolio_gallery_image_type)){
						$style = 'background-image: url(' . $url_medium .');'; 
						$style .= 'background-image: -webkit-image-set(url(' . $url_medium .') 1x, url(' . $url_large .') 2x);';
						$style .= 'background-image: image-set(url(' . $url_medium .') 1x, url(' . $url_large .') 2x);';	
						$image .= '<div class="background-image" style="'. $style .'"></div>';
					}else{
						$srcset = $url_medium . ' 1x, ' . $url_large . ' 2x'; 
						$image .= '<img src="' . $url_medium . '" srcset="' . $srcset . '"/>';
					}
					
					
					//each image
					$html .= '<div class="portfolio-image grid-item ' . $portfolio_gallery_image_type .'">';
						$html .= '<div class="inner">';
							$html .= '<a href="' . $url_large . '" class="lightbox-element">';						
								$html .= $image;
							$html .= '</a>';
						$html .= '</div>';
					$html .= '</div>';
				}
				
				$html .= '</article>';
				
			}
		}
		
		
		return $html;
	}

	//gets the pagination (next and prev) for a single portfolio element
	public static function get_portfolio_pagination($post_id = null){
			
		$instance = self::getInstance();
		$html = '';
		
		//if no ID passed, check if we can collect it from current portfolio
		if(is_null($post_id)){
			global $post;
			if($post){
				if(get_post_type($post) == $instance->post_type_args['post_type_name']){
					$post_id = $post->ID;
				}
			}
		}
		
		$post = get_post($post_id);
		if($post){
			$previous_post = get_previous_post();
			$next_post = get_next_post();
			
			$html .= '<div class="portfolio-pagination cf">';
			
			if($previous_post){
				$html .= '<a class="previous" href="' . get_permalink($previous_post->ID) . '"> Previous - ' . $previous_post->post_title . '</a>';
			}
			if($next_post){
				$html .= '<a class="next" href="' . get_permalink($next_post->ID) . '"> Next - ' . $next_post->post_title . '</a>';
			}
			
			$html .= '</div>';
			
		}
		
		return $html;
	}
	
	//get a listing of all categories portfolio is tagged under, displays as a listing
	public static function get_portfolio_categories($post_id = null, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = '';
		
		//if no ID passed, check if we can collect it from current portfolio
		if(is_null($post_id)){
			global $post;
			if($post){
				if(get_post_type($post) == $instance->post_type_args['post_type_name']){
					$post_id = $post->ID;
				}
			}
		}
		
		$post = get_post($post_id);
		if($post){
			$terms = wp_get_post_terms($post_id, 'el_portfolio_category');
			//if we have terms
			if($terms && !is_a($terms, 'WP_Error')){
				$html .= '<div class="portfolio-terms categories">';
				
				//show title if required
				if(isset($optional_args['show_title'])){
					if($optional_args['show_title'] == 'true'){
						$html .= '<strong>Categoriesed Under: </strong>';
					}
				}
				
				foreach($terms as $term){
					$html .= '<a class="term" href="' . get_term_link($term) . '" title="' . $term->name . '">' . $term->name . '</a>';
				}
				$html .= '</div>';
			}
		}
		
		
		return $html;
	}
	
	//gets a listing of all applicable tags for the portfolio
	public static function get_portfolio_tags($post_id = null, $optional_args = array()){
		
		$instance = self::getInstance();
		$html = '';
		
		//if no ID passed, check if we can collect it from current portfolio
		if(is_null($post_id)){
			global $post;
			if($post){
				if(get_post_type($post) == $instance->post_type_args['post_type_name']){
					$post_id = $post->ID;
				}
			}
		}
		
		$post = get_post($post_id);
		if($post){
			$terms = wp_get_post_terms($post_id, 'el_portfolio_tags');
				
			//if we have terms
			if($terms && !is_a($terms, 'WP_Error')){
				$html .= '<div class="portfolio-terms tags">';
				
				//show title if required
				if(isset($optional_args['show_title'])){
					if($optional_args['show_title'] == 'true'){
						$html .= '<strong>Tagged Under: </strong>';
					}
				}
				foreach($terms as $term){
					$html .= '<a class="term" href="' . get_term_link($term) . '" title="' . $term->name . '">' . $term->name . '</a>';
				}
				$html .= '</div>';
			}
		}
		
		
		return $html;
		
	}
	
	//public scripts and styles
	public function enqueue_public_scripts_and_styles(){
		$directory = plugin_dir_url( __FILE__ );	
		wp_enqueue_script('el-lightbox-script', $directory . '/js/jquery.magnific-popup.min.js', array('jquery')); 
		wp_enqueue_style('el-lightbox-style', $directory . '/css/magnific-popup.css'); 
		wp_enqueue_script('jquery-masonry'); //masonry for flexible layout
		wp_enqueue_script('isotope', '//unpkg.com/isotope-layout@3.0/dist/isotope.pkgd.js', array('jquery')); //isotope for project filtering
		wp_enqueue_script('el-portfolio-public-script', $directory . '/js/portfolio_public_scripts.js', array('jquery', 'isotope', 'jquery-masonry', 'el-lightbox-script')); 
		wp_enqueue_style('el-portfolio-public-styles', $directory . '/css/portfolio_public_styles.css');
		
		
	}
	
	//admin only scripts and styles
	public function enqueue_admin_scripts_and_styles(){
		$directory = plugin_dir_url( __FILE__ );
		wp_enqueue_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('el-portfolio-admin-script', $directory . '/js/portfolio_admin_scripts.js', array('jquery','wp-color-picker')); 
		wp_enqueue_style('el-portfolio-admin-styles', $directory . '/css/portfolio_admin_styles.css');
		
	}
	
	//registers theme options via customizer
	public function register_customizer_settings(){
		
	}
	
	//registers shortcodes for use
	public function register_shortcodes(){
		add_shortcode('portfolio_listing', array($this, 'display_shortcodes'));
		add_shortcode('portfolio_card', array($this, 'display_shortcodes'));
		add_shortcode('portfolio_gallery', array($this, 'display_shortcodes'));
		add_shortcode('portfolio_pagination', array($this, 'display_shortcodes'));
		add_shortcode('portfolio_categories', array($this, 'display_shortcodes'));
		add_shortcode('portfolio_tags', array($this, 'display_shortcodes'));
		add_shortcode('portfolio_microdata', array($this, 'display_shortcodes' ));
	}
	
	//display for the shortcodes
	public function display_shortcodes($atts, $content = "", $tag){
			
		$html = '';
		
		//main portfolio listing
		if($tag == 'portfolio_listing'){
				
			//determine shortcode args
			$args = shortcode_atts(array(
				'items_per_row'		=> '2'
			), $atts, $tag);	
			
			//get listing of portfolio cards
			$html .= $this->get_portfolio_listing($args);			
		}
		
		//display a single portfolio card
		else if($tag == 'portfolio_card'){
			
			//determine shortcode args
			$args = shortcode_atts(array(
				'id'		=> ''
			), $atts, $tag);	
	
			$html .= $this->get_portfolio_single($args['id']);
				
		}
		
		//displays gallery for portfolio
		else if($tag == 'portfolio_gallery'){
			
			global $post;
			
			//determine shortcode args
			$args = shortcode_atts(array(
				'items_per_row'		=> '4'
			), $atts, $tag);	
			
			$html .= $this->get_portfolio_gallery($post->ID, $args);
		}
		
		//displays pagination for next / prev portfolio
		else if($tag == 'portfolio_pagination'){
			$html .= $this->get_portfolio_pagination();
		}
		
		//get a listing of portfolio categories, usually displayed on the single page
		else if($tag == 'portfolio_categories'){
			
			global $post;
			
			//determine shortcode args
			$args = shortcode_atts(array(
				'show_title'		=> 'true'
			), $atts, $tag);	
			
			$html .= $this->get_portfolio_categories($post->ID, $args);
		}
		
		//get a listing of porfolio tags, usually displayed on the single page
		else if($tag == 'portfolio_tags'){
			
			global $post;
			
			//determine shortcode args
			$args = shortcode_atts(array(
				'show_title'		=> 'true'
			), $atts, $tag);	
			
			$html .= $this->get_portfolio_tags($post->ID, $args); 
		}
		//gets the microdata format card for this portfolio. 
		else if($tag == 'portfolio_microdata'){
				
			global $post;
			
			//determine shortcode args
			$args = shortcode_atts(array(
				'id'		=> ''
			), $atts, $tag);	
			
			$html .= $this->get_portfolio_microdata_information($args['id']);

		}
				
				
		return $html;
	}
	
	//registers custom widgets 
	public function register_widgets(){
		
	}
	
	
	//gets the HTML markup for the portfolio listings
	public static function get_portfolio_listing($optional_args = array()){
		
		$html = '';
		$instance = self::getInstance();
		
		$post_type = $instance->post_type_args['post_type_name'];
		
		$post_args = array(
			'post_type'			=> $post_type,
			'posts_per_page'	=> 5,
			'post_status'		=> 'publish',
			'orderby'			=> 'meta_value_num',
			'order'				=> 'ASC',
			'meta_key'			=> 'portfolio_archive_order'	
		);
		
		$posts = get_posts($post_args);
		

		if($posts){
			
			
			$html .= '<div class="portfolio-listing cf">';
			
				//get a listing of category terms for filters
				$html .= self::get_portfolio_category_filters();
			
				$classes = isset($optional_args['items_per_row']) ? 'row-of-' . $optional_args['items_per_row'] : 'row-of-2';
				$html .= '<div class="portfolios row-item ' . $classes .'">';
				foreach($posts as $post){
					
					//single portfolio
					$html .= $instance::get_portfolio_single($post->ID);
					
				}
				$html .= '</div>';
				
			$html .= '</div>';
			
		}
		
		

		return $html; 
	}
	
	//given an id, get the HTML output for a single portfolio card
	public static function get_portfolio_single($post_id){
		
		$html = '';
		$instance = self::getInstance();
		
		$post = get_post($post_id);
		if($post){
			$post_permalink = get_permalink($post_id);
			$post_title = apply_filters('the_title', $post->post_title);
			
			$portfolio_archive_title = get_post_meta($post_id, 'portfolio_archive_title', true);
			$portfolio_archive_subtitle = get_post_meta($post_id, 'portfolio_archive_subtitle', true);
			$portfolio_archive_excerpt = get_post_meta($post_id, 'portfolio_archive_excerpt', true);
			$portfolio_archive_image = get_post_meta($post_id, 'portfolio_archive_image', true);
			$portfolio_gallery_image_type = get_post_meta($post_id, 'portfolio_gallery_image_type', true);
			$portfolio_archive_display_readmore = get_post_meta($post_id, 'portfolio_archive_display_readmore', true);
			$portfolio_archive_show_categories = get_post_meta($post_id, 'portfolio_archive_show_categories', true);
			$portfolio_archive_overlay_color = get_post_meta($post_id, 'portfolio_archive_overlay_color', true);
			$portfolio_archive_text_color = get_post_meta($post_id, 'portfolio_archive_text_color', true);

			$portfolio_categories = wp_get_post_terms($post_id, 'el_portfolio_category');
			$portfolio_category_classes = '';
			
			//get a listing of other portfolios for use as related items
			$other_post_args = array(
				'post_type'			=> $instance->post_type_args['post_type_name'],
				'posts_per_page'	=> 5,
				'post_status'		=> 'publish',
				'post__not_in'		=> array($post_id)
			);
			$other_posts = get_posts($other_post_args);
			
			if(!empty($portfolio_categories)){
				foreach($portfolio_categories as $term){
					$portfolio_category_classes .= ' portfolio_category_' . $term->term_id;
				}
			}
			
			//output (with category classes appended for filtering)
			$html .= '<article class="portfolio-card grid-item' . $portfolio_category_classes .'" itemscope itemtype="http://schema.org/Service">';
			
				//link to single portfolio (with microdata)
				$html .= '<a href="' . $post_permalink . '" title="Find out more about this project" itemprop="url">';
				
					$html .= '<div class="inner">';
				
						//background image
						if(!empty($portfolio_archive_image)){
							
							$url_medium = wp_get_attachment_image_src($portfolio_archive_image, 'medium', false)[0];
							$url_large = wp_get_attachment_image_src($portfolio_archive_image, 'large', true)[0];
				
							$style = 'background-image: url(' . $url_medium .');'; 
							$style .= 'background-image: -webkit-image-set(url(' . $url_medium .') 1x, url(' . $url_large .') 2x);';
							$style .= 'background-image: image-set(url(' . $url_medium .') 1x, url(' . $url_large .') 2x);';	
							$html .= '<div class="background-image" style="'. $style .'"></div>';
							
							
							//add a metatag for microdata (image)
							$html .= '<meta itemprop="image" content="' . $url_large . '"></meta>';
						}
						
						//overlay
						if(!empty($portfolio_archive_overlay_color)){
							$html .= '<div class="overlay" style="background-color: ' . $portfolio_archive_overlay_color . ';"></div>';
						}
		
						//main content
						$style = !empty($portfolio_archive_text_color) ? 'color: ' . $portfolio_archive_text_color .';' : '';
						$html .= '<div class="content" style="' . $style .'">';
		
							//display either manual title or post title (with microdata)
							if(!empty($portfolio_archive_title) || !empty($post_title)){
								if(!empty($portfolio_archive_title)){
									$html .= '<h2 class="title" itemprop="name">' . $portfolio_archive_title . '</h2>';
								}else{
									$html .= '<h2 class="title" itemprop="name">' . $portfolio_archive_title . '</h2>';
								}
								
							}
							//display subtitle (with microdata)
							if(!empty($portfolio_archive_subtitle)){
								$html .= '<h3 class="subtitle" itemprop="description">' . $portfolio_archive_subtitle . '</h3>';
							}
							//display categories (with microdata)
							if(!empty($portfolio_archive_show_categories) && $portfolio_archive_show_categories == 'show'){
								
								if(!empty($portfolio_categories)){
									$html .= '<div class="categories">';
									foreach($portfolio_categories as $category){
										$html .= '<span class="category" itemprop="category">' . $category->name . '</span>';
									}
									$html .= '</div>';
								}
							}
							//display excerpt (with microdata)
							if(!empty($portfolio_archive_excerpt)){
								$html .= '<p class="excerpt" itemprop="description">' . $portfolio_archive_excerpt . '</p>';
							}
							if(!empty($portfolio_archive_display_readmore) && $portfolio_archive_display_readmore == 'show'){
								$style = !empty($portfolio_archive_text_color) ? 'color: ' . $portfolio_archive_text_color . ';' : '';
								$style .= !empty($portfolio_archive_text_color) ? 'border: solid 1px ' . $portfolio_archive_text_color . ';' : '';
								$html .= '<div class="readmore" style="' . $style .'">Read More</div>';
							}
							
							
						$html .= '</div>';
						
						//Additional microdata attributes
						$html .= '<meta itemprop="serviceOutput" content="Portfolio"></meta>';
						$html .= '<meta itemprop="serviceType" content="Portfolio"></meta>';
						
						//output related portfolios
						if(!empty($other_post_args)){
							foreach($other_posts as $post){
								$html .= '<meta itemprop="isRelatedTo" content="' . get_permalink($post->ID) . '"></meta>';
							}
						}
					
					$html .= '</div>';
			
				$html .= '</a>';
			
			$html .= '</article>';
			
		}
		
		return $html;
	}
		
	//gets a listing of categories to be used as filters when displaying the portfolio listing
	public static function get_portfolio_category_filters(){
		$html = '';
		$instance = self::getInstance();
		
		
		$term_args = array(
			'hide_empty' => false
		);
		$terms = get_terms('el_portfolio_category', $term_args);
		
		if($terms){
			$html .= '<article class="portfolio-filter portfolio-terms">';
			$html .= '<div class="term term-reset active">No Filter</div>';
			foreach($terms as $term){
				
				$name = $term->name;
				$term_id = $term->term_id;

				$html .= '<div class="term" data-filter="portfolio_category_' . $term_id .'">' . $name . '</div>';
			}
			$html .= '</article>';
			
		}
		
		
		return $html;
	}
	
	//sets / returns instance
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function get_post_type_args(){
		
	}
	
 }
 $el_portfolios = el_portfolios::getInstance();
 



?>