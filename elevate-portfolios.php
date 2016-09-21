<?php
/**
 * Plugin Name: Elevate Portfolios
 * Plugin URI:  https://elevate360.com.au/plugins
 * Description: Showcases portfolios with an easy to use admin back-end. Contains a filterable listing page for portfolios plus a single portfolio showcase. Use a combination of
 * either shortcodes or action hooks to output content for your single portfolio pages. All portfolios are enriched with schema.org metadata  
 * Version:     1.1.2
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

 
 //TODO: check for WP version before starting, we handle terms using WP_Term (4.4+)
 
 
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
	//taxonomy field args
	
	
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
		
		//taxonomy fields to add to terms
		$this->taxonomy_field_args = array(

			array(
				'id'			=> 'el_portfolio_term_content',
				'title'			=> 'Term Content',
				'description'	=> 'Primary content used for this term. Used as the primary content zone',
				'taxonomy_name'	=> 'el_portfolio_category',
				'type'			=> 'editor'
			),
			array(
				'id'			=> 'el_portfolio_term_content',
				'title'			=> 'Term Content',
				'description'	=> 'Primary content used for this term. Used as the primary content zone',
				'taxonomy_name'	=> 'el_portfolio_tags',
				'type'			=> 'editor'
			),
			array(
				'id'			=> 'el_portfolio_term_subtitle',
				'title'			=> 'Term Subtitle',
				'description'	=> 'secondary smaller subtitle displayed when this term is shown in a listing of all portfolio categories or tag',
				'taxonomy_name'	=> 'el_portfolio_category',
				'type'			=> 'text'
			),
			array(
				'id'			=> 'el_portfolio_term_subtitle',
				'title'			=> 'Term Subtitle',
				'description'	=> 'secondary smaller subtitle displayed when this term is shown in a listing of all portfolio categories or tag',
				'taxonomy_name'	=> 'el_portfolio_tags',
				'type'			=> 'text'
			),
			array(
				'id'			=> 'el_portfolio_term_image',
				'title'			=> 'Term Image',
				'description'	=> 'Image that represents this term. To be used when viewing a grid listing of all terms e.g viewing all portfolio categories',
				'taxonomy_name'	=> 'el_portfolio_category',
				'type'			=> 'upload-image'
			),
			array(
				'id'			=> 'el_portfolio_term_image',
				'title'			=> 'Term Image',
				'description'	=> 'Image that represents this term. To be used when viewing a grid listing of all terms e.g viewing all portfolio tags',
				'taxonomy_name'	=> 'el_portfolio_tags',
				'type'			=> 'upload-image'
			),
			array(
				'id'			=> 'el_portfolio_term_overlay_background_color',
				'title'			=> 'Term Overlay Background Colour',
				'description'	=> 'Colour of the overlay used when hovering / interacting with this term when viewed in it\'s card listing form',
				'taxonomy_name'	=> 'el_portfolio_category',
				'type'			=> 'color'
			),
			array(
				'id'			=> 'el_portfolio_term_overlay_background_color',
				'title'			=> 'Term Overlay Background Colour',
				'description'	=> 'Colour of the overlay used when hovering / interacting with this term when viewed in it\'s card listing form',
				'taxonomy_name'	=> 'el_portfolio_tags',
				'type'			=> 'color'
			),
			array(
				'id'			=> 'el_portfolio_term_text_color',
				'title'			=> 'Term Text Colour',
				'description'	=> 'Colour of the title, subtitle, description and readmore elements when this term is displayed in it\'s card listing form',
				'taxonomy_name'	=> 'el_portfolio_category',
				'type'			=> 'color'
			),
			array(
				'id'			=> 'el_portfolio_term_text_color',
				'title'			=> 'Term Text Colour',
				'description'	=> 'Colour of the title, subtitle, description and readmore elements when this term is displayed in it\'s card listing form',
				'taxonomy_name'	=> 'el_portfolio_tags',
				'type'			=> 'color'
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
			),
			array(
				'id'			=> 'single_portfolio_gallery_slider_metabox',
				'title'			=> 'Single Portfolio Gallery Slider',
				'context'		=> 'normal',
				'args'			=> array(
					'description' => 'Here you can define your slider displayed on the single portfolio page'
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
			
			//single page gallery
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
			array(
				'id'			=> 'portfolio_gallery_items_per_row',
				'title'			=> 'Portfolio Images Per Row',
				'description'	=> 'Select how many images will be shown per row in the gallery' ,
				'type'			=> 'select',
				'meta_box_id'	=> 'single_portfolio_gallery_metabox',
				'args'			=> array(
					'options'		=> array(
						array(
							'id'	=> 'row-of-1',
							'title' => 'Row of 1'
						),
						array(
							'id'	=> 'row-of-2',
							'title' => 'Row of 2'
						),
						array(
							'id'	=> 'row-of-3',
							'title' => 'Row of 3'
						),
						array(
							'id'	=> 'row-of-4',
							'title' => 'Row of 4'
						),
						array(
							'id'	=> 'row-of-5',
							'title' => 'Row of 5'
						),
					)
				)
			),
			
			//single page gallery slider
			array(
				'id'			=> 'portfolio_gallery_slider_images',
				'title'			=> 'Portfolio Gallery Slider Images',
				'description'	=> 'Images to be used to build a slider on the single portfolio',
				'type'			=> 'upload-multi-image',
				'meta_box_id'	=> 'single_portfolio_gallery_slider_metabox'
			),
			array(
				'id'			=> 'portfolio_gallery_slider_image_type',
				'title'			=> 'Portfolio Gallery Display Type',
				'description'	=> 'Determine if your gallery slider images will be displayed as \'background\' images (square and cropped) or as \'traditional\' images (unique sizes displayed with masonry)' ,
				'type'			=> 'select',
				'meta_box_id'	=> 'single_portfolio_gallery_slider_metabox',
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
			)
			
		);
	
		//call parent constrcutor
		parent::__construct(
			$this->post_type_args, 
			$this->meta_box_args,
			$this->meta_field_args,
			$this->taxonomy_args,
			$this->taxonomy_field_args); 	
		
			
		add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts_and_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles'));
		add_action('init', array($this, 'register_shortcodes'));
		add_action('et_builder_post_types', array($this, 'add_divi_support'));
		
		//action hooks to output content
		add_action('el_display_portfolio_listing', array($this, 'display_portfolio_listing'), 10, 1); //displays a listing of portfolio cards
		add_action('el_display_portfolio_single', array($this, 'display_portfolio_single'), 10, 2); //displays a single portfolio card
		add_action('el_display_portfolio_gallery', array($this, 'display_portfolio_gallery'), 10, 2);  //displays the gallery for a single portfolio
		add_action('el_display_portfolio_gallery_slider', array($this, 'display_portfolio_gallery_slider'), 10, 2); //displays the gallery slider for a single portfolio
		add_action('el_display_portfolio_categories', array($this, 'display_portfolio_categories'), 10, 2); //displays the categories for a single portfolio
		add_action('el_display_portfolio_tags', array($this, 'display_portfolio_tags'), 10, 2); //displays the tags for a single portfolio
		add_action('el_display_portfolio_pagination', array($this, 'display_portfolio_pagination'), 10, 2); //displays the pagination for a single portfolio
		add_action('el_display_portfolio_microdata_information', array($this, 'display_portfolio_microdata_information'), 10, 2); //displays the schema.org microdata for a single portfolio
		add_action('el_display_portfolios_for_term', array($this, 'display_portfolios_for_term'), 10, 2); //displays a listing of portfolios that belong to a single term ID, used for tax listings
		add_action('el_display_portfolio_term_listing', array($this, 'display_portfolio_term_listing'), 10, 2); //displays a listing of portfolio terms (categories / tags) in a grid. 
		
		
		//TODO: Add universal settings to customizer
		//add_action('customize_register', array($this, 'register_customizer_settings')); //hooks into theme customizer for options
	}

	//add divi support so the content area can be used
	public function add_divi_support($post_types){
		
		$post_types[] = $this->post_type_args['post_type_name'];
		
		return $post_types;
	}

	//hook to disply a single portfolio item
	public static function display_portfolio_single($post_id, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = $instance->get_portfolio_single($post_id, $optional_args );
		
		echo $html;
	}
	
	//hook to display the portfolio listings, with optional arguments
	public static function display_portfolio_listing($optional_args = array()){
		
		$instance = self::getInstance();
		$html = $instance->get_portfolio_listing($optional_args);
		
		echo $html;
	}
	
	//displays project pagination
	public static function display_portfolio_pagination($post_id, $optional_args = array()){
		$instance = self::getInstance();
		$html = $instance->get_portfolio_pagination($post_id, $optional_args);
		
		echo $html;
	}
	
	//displays the portfolio tags
	public static function display_portfolio_tags($post_id, $optional_args = array()){
		$instance = self::getInstance();
		$html = $instance->get_portfolio_tags($post_id, $optional_args);
		
		echo $html;
	}

	//displays the portfolio categories
	public static function display_portfolio_categories($post_id, $optional_args = array()){
		$instance = self::getInstance();
		$html = $instance->get_portfolio_categories($post_id, $optional_args);
		
		echo $html;
	}

	//display the gallery elements for a single portfolio
	public static function display_portfolio_gallery($post_id, $optional_args = array()){
			
		
		$instance = self::getInstance();
		$html = $instance->get_portfolio_gallery($post_id, $optional_args);
	
		
		echo $html;
	}
	
	//display the gallery slider for a single portfolio
	public static function display_portfolio_gallery_slider($post_id, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = $instance->get_portfolio_gallery_slider($post_id, $optional_args);
	
		echo $html;
	}
	
	//outputs an entire microdata format element based off the 'services' schema, displayed as an invisible div
	//http://schema.org/Service
	public static function display_portfolio_microdata_information($post_id, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = '';
		
		$post = get_post($post_id);
		$html .= $instance->get_portfolio_microdata_information($post->ID, $optional_args);
		
		echo $html;
	}

	//action hook to display portfolios belonging to a set term id (category term or tag term)
	public static function display_portfolios_for_term($term_id, $optional_args = array()){
		
		
		$instance = self::getInstance();
		
		$html = '';
		$html .= $instance->get_portfolios_for_term($term_id, $optional_args);
		
		echo $html;
		
	}

	public static function display_portfolio_term_listing($taxonomy_name, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = '';
		
		$html .= $instance->get_portfolio_term_listing($taxonomy_name, $optional_args);
		
		echo $html;
	}

	//gets the microdata in the form of a service card 
	public static function get_portfolio_microdata_information($post_id = null, $optional_args = array()){
		
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
					$url = wp_get_attachment_image_src($portfolio_archive_image, apply_filters('el_portfolio_archive_image_size','large'), true)[0];
					$html .= '<meta itemprop="image" content="' . $url . '"></meta>';
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
	public static function get_portfolio_content($post_id, $optional_args = array()){
			
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
			$portfolio_gallery_items_per_row = get_post_meta($post_id, 'portfolio_gallery_items_per_row', true);
			$portfolio_gallery_image_type = get_post_meta($post_id, 'portfolio_gallery_image_type', true);
			
			if(!empty($portfolio_gallery_images)){
				
				//filter for before gallery output	
				$html = apply_filters('el_gallery_before', $html); 
					
				$html .= '<article class="portolio-gallery row-item ' . $portfolio_gallery_items_per_row . ' ' . $portfolio_gallery_image_type . '">';
				
				$portfolio_gallery_images = json_decode($portfolio_gallery_images);

				foreach($portfolio_gallery_images as $image_id){
					
					$url_medium = wp_get_attachment_image_src($image_id, apply_filters('el_portfolio_gallery_small_image_size', 'medium'), false)[0];
					$url_large = wp_get_attachment_image_src($image_id, apply_filters('el_portfolio_gallery_large_image_size', 'large'), true)[0];

					//determine output for images
					$image = '';
					
					
					
					if($portfolio_gallery_image_type == 'background' || empty($portfolio_gallery_image_type)){
						$style = 'background-image: url(' . $url_medium .');'; 
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

	//gets the gallery output for the slider
	public static function get_portfolio_gallery_slider($post_id = null, $optional_args = array()){
			
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
			
			//output slider 
			$portfolio_gallery_slider_images = get_post_meta($post->ID, 'portfolio_gallery_slider_images', true);
			if($portfolio_gallery_slider_images){
				
				$portfolio_gallery_slider_images = json_decode($portfolio_gallery_slider_images);
				$portfolio_gallery_slider_image_type = get_post_meta($post->ID, 'portfolio_gallery_slider_image_type', true);
				
				$html .= '<div class="flexslider portfolio-gallery-slider portfolio">';
					$html .= '<div class="slides">';
					foreach($portfolio_gallery_slider_images as $image_id){
						
						$url= wp_get_attachment_image_src($image_id, apply_filters('el_porfolio_gallery_slider_image_size', 'large'), false)[0];
					
						//determine output for images
						$image = '';
						if($portfolio_gallery_slider_image_type == 'background' || empty($portfolio_gallery_slider_image_type)){
							$style = 'background-image: url(' . $url .');'; 
							
							$image .= '<div class="inner-wrap aspect-16-9">';
								$image .= '<div class="background-image" style="'. $style .'"></div>';
							$image .= '</div>';
							
						}else{
							$image .= '<img src="' . $url . '"/>';
						}
						
						//output each slide
						$html .= '<div class="slide">';
							$html .= $image;	
						$html .= '</div>';
					}
					$html .= '</div>';
				$html .= '</div>';
				
			}
			
			
				
				
			
		}
		
		return $html;
	}

	//gets the pagination (next and prev) for a single portfolio element
	public static function get_portfolio_pagination($post_id = null, $optional_args = array()){
			
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
				$content = apply_filters('el_portfolio_previous_text', 'Previous - ' . $previous_post->post_title);
				$html .= '<a class="previous" href="' . get_permalink($previous_post->ID) . '">' . $content . '</a>';
			}
			if($next_post){
				$content = apply_filters('el_portfolio_next_text', 'Next - ' . $next_post->post_title);
				$html .= '<a class="next" href="' . get_permalink($next_post->ID) . '">' . $content . '</a>';
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
	
	//gets the HTML markup for the portfolio listings
	public static function get_portfolio_listing($optional_args = array()){
		
		$html = '';
		$instance = self::getInstance();
		
		$post_type = $instance->post_type_args['post_type_name'];
		
		$post_args = array(
			'post_type'			=> $post_type,
			'posts_per_page'	=> -1,
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
			
		}else{
			$html .= '<div class="portfolio-listing no-portfolios">';
			$html .= '<strong>Sorry, but there are no portfolios to display</strong>';
			$html .= '</div>';
		}
		
		

		return $html; 
	}
	
	//given an id, get the HTML output for a single portfolio card
	public static function get_portfolio_single($post_id, $optional_args = array()){
		
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
							
							$url = wp_get_attachment_image_src($portfolio_archive_image, apply_filters('el_portfolio_archive_image_size', 'medium'), false)[0];

							$style = 'background-image: url(' . $url .');'; 	
							$html .= '<div class="background-image" style="'. $style .'"></div>';
							
							
							//add a metatag for microdata (image)
							$html .= '<meta itemprop="image" content="' . $url . '"></meta>';
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
		
	//given a category / term ID, get all portfolios belonging to that category, displayed as a card
	public static function get_portfolios_for_term($term_id,  $optional_args = array()){
		
		
		$instance = self::getInstance();
		$html = '';
		
	
		$term = get_term($term_id);
		//execute only if we have found our term
		if(($term) && (!is_a($term, 'WP_Error'))){
			

			$term_args = array(
				'post_type'		=> $instance->post_type_args['post_type_name'],
				'post_status'	=> 'publish',
				'posts_par_page'=> -1,
				'tax_query'		=> array(
					array(
						'taxonomy'	=> $term->taxonomy,
						'field'		=> 'term_id',
						'terms'		=> $term->term_id
					)	
				)
			);
			
			$portfolios = get_posts($term_args);
			if($portfolios){
				$html .= '<div class="portfolio-listing cf">';
					
					$classes = isset($optional_args['items_per_row']) ? 'row-of-' . $optional_args['items_per_row'] : 'row-of-2';
					$html .= '<div class="portfolios row-item ' . $classes .'">';
					foreach($portfolios as $portfolio){
						$html .= $instance->get_portfolio_single($portfolio->ID);
					}	
					$html .= '</div>';
				$html .= '</div>';
			}else{
				$html .= '<div class="portfolios no-portfolios">';
					$html .= '<strong>Sorry there are no portfolios to display</strong>';
				$html .= '</div>';
			}
			
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
			$default_text = apply_filters('el_portfolio_all_filter_text', 'All');
			$html .= '<article class="portfolio-filter portfolio-terms">';
			$html .= '<div class="term term-reset active">' . $default_text .'</div>';
			foreach($terms as $term){
				
				$name = $term->name;
				$term_id = $term->term_id;

				$html .= '<div class="term" data-filter="portfolio_category_' . $term_id .'">' . $name . '</div>';
			}
			$html .= '</article>';
			
		}
		
		
		return $html;
	}
	
	//get a card based grid listing of all terms belonging to an applicable taxonomy e.g el_categories or el_tags
	public static function get_portfolio_term_listing($taxonomy_name, $optional_args = array()){
			
		$instance = self::getInstance();
		$html = '';
		
		$taxonomy = get_taxonomy($taxonomy_name);
		if($taxonomy){
			
			$term_args = array(
				'hide_empty'	=> false
			);
			
			//display terms if we have any for taxonomy
			$terms = get_terms($taxonomy_name, $term_args); 
			if(($terms) && (!is_a($terms, 'WP_Error'))){
				$html .= '<div class="portfolios row-item row-of-2">';
				foreach($terms as $term){
					$html .= $instance->get_portfolio_single_term($term->term_id);	
				}
				$html .= '</div>';
			}
			
		}
		
		
		return $html;
	}
	
	
	//gets a single portfolio term (category or tag) to display as a card
	public static function get_portfolio_single_term($term_id, $optional_args = array()){
	
		$instance = self::getInstance();
		$html = '';
		
		$term = get_term($term_id);
		if( ($term) && (!is_a( $term,'WP_Error') ) ){
				
			$term_name = $term->name;
			$term_permalink = get_term_link($term);
			$term_description = $term->description;
			$term_image = get_term_meta($term->term_id, 'el_portfolio_term_image', true);
			$term_text_color = get_term_meta($term->term_id, 'el_portfolio_term_text_color', true);
			$term_overlay_background_color = get_term_meta($term->term_id, 'el_portfolio_term_overlay_background_color', true);
			$term_subtitle = get_term_meta($term->term_id, 'el_portfolio_term_subtitle', true);
			

			//output (
			$html .= '<article class="portfolio-card grid-item" itemscope itemtype="https://schema.org/Thing">';
			
				//link to single term (with microdata)
				$html .= '<a href="' . $term_permalink . '" title="See all portolios tagged under: ' . $term_name .'" itemprop="url">';
				
					$html .= '<div class="inner">';
				
						//background image
						if(!empty($term_image)){
							
							$url = wp_get_attachment_image_src($term_image, apply_filters('el_portfolio_term_image_size','medium'), false)[0];

							$style = 'background-image: url(' . $url .');'; 
							$html .= '<div class="background-image" style="'. $style .'"></div>';
							
							
							//add a metatag for microdata (image)
							$html .= '<meta itemprop="image" content="' . $url . '"></meta>';
						}
						
						//overlay
						if(!empty($term_overlay_background_color)){
							$html .= '<div class="overlay" style="background-color: ' . $term_overlay_background_color . ';"></div>';
						}
		
						//main content
						$style = !empty($term_text_color) ? 'color: ' . $term_text_color .';' : '';
						$html .= '<div class="content" style="' . $style .'">';
		
							//display term name
							if(!empty($term_name)){
								$html .= '<h2 class="title" itemprop="name">' . $term_name . '</h2>';
							}
							//display subtitle (with microdata)
							if(!empty($term_subtitle)){
								$html .= '<h3 class="subtitle" itemprop="description">' . $term_subtitle . '</h3>';
							}
							
							//display excerpt (with microdata)
							if(!empty($term_description)){
								$html .= '<p class="excerpt" itemprop="description">' . wp_trim_words($term_description, 25, '...') . '</p>';
							}


							//readmore button
							$style = !empty($term_text_color) ? 'color: ' . $term_text_color . ';' : '';
							$style .= !empty($term_text_color) ? 'border: solid 1px ' . $term_text_color . ';' : '';
							$html .= '<div class="readmore" style="' . $style .'">Read More</div>';

							
						$html .= '</div>';
					
					$html .= '</div>';
			
				$html .= '</a>';
			
			$html .= '</article>';	
			
		}
		
		return $html;
	}
	
	
	//public scripts and styles
	public function enqueue_public_scripts_and_styles(){
		$directory = plugin_dir_url( __FILE__ );	
		
		wp_enqueue_style('el-portfolio-public-styles', $directory . '/css/portfolio_public_styles.css');
		wp_enqueue_style('el-portfolio-flexslider-style', '//cdnjs.cloudflare.com/ajax/libs/flexslider/2.6.3/flexslider.min.css');
		wp_enqueue_style('el-lightbox-style', $directory . '/css/magnific-popup.css'); 
			
		wp_enqueue_script('el-lightbox-script', $directory . '/js/jquery.magnific-popup.min.js', array('jquery')); //lightbox gallery
		wp_enqueue_script('el-portfolio-flexslider-script', '//cdnjs.cloudflare.com/ajax/libs/flexslider/2.6.3/jquery.flexslider-min.js', array('jquery')); 
		wp_enqueue_script('jquery-masonry'); //masonry for flexible layout
		wp_enqueue_script('isotope', '//unpkg.com/isotope-layout@3.0/dist/isotope.pkgd.js', array('jquery')); //isotope for project filtering
		wp_enqueue_script('el-portfolio-public-script', $directory . '/js/portfolio_public_scripts.js', array('jquery', 'isotope', 'jquery-masonry', 'el-lightbox-script','el-portfolio-flexslider-script')); 

	}
	
	//admin only scripts and styles
	public function enqueue_admin_scripts_and_styles(){
		$directory = plugin_dir_url( __FILE__ );
		wp_enqueue_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'jquery-ui-sortable' );
		wp_enqueue_script('el-portfolio-admin-script', $directory . '/js/portfolio_admin_scripts.js', array('jquery','wp-color-picker', 'jquery-ui-sortable')); 
		wp_enqueue_style('el-portfolio-admin-styles', $directory . '/css/portfolio_admin_styles.css');
		
	}
	
	//registers theme options via customizer
	public function register_customizer_settings(){
		
	}
	
	//registers shortcodes for use
	public function register_shortcodes(){
		
		add_shortcode('portfolio_listing', array($this, 'display_shortcodes')); //get a listing of portfolios (multiple cards)
		add_shortcode('portfolio_single', array($this, 'display_shortcodes')); //gets a single portfolio card
		add_shortcode('portfolio_gallery', array($this, 'display_shortcodes')); //gets the gallery for a single portfolio
		add_shortcode('portfolio_pagination', array($this, 'display_shortcodes')); //gets the pagination for use on a single portfolio
		add_shortcode('portfolio_categories', array($this, 'display_shortcodes')); //gets the categories associated with a single portfolio
		add_shortcode('portfolio_tags', array($this, 'display_shortcodes')); //gets the tags associated with a single portfolio
		add_shortcode('portfolio_microdata', array($this, 'display_shortcodes' )); //gets a schema.org element for a single portfolio
		add_shortcode('portfolio_gallery_slider', array($this, 'display_shortcodes')); //gets the gallery slider for a single portfolio
		add_shortcode('portfolio_term_listing', array($this, 'display_shortcodes')); //gets a grid listing of all terms in a taxonomy (e.g categories)
		add_shortcode('portfolio_listing_for_term', array($this, 'display_shortcodes')); //displays portfolis for a single term
		
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
		else if($tag == 'portfolio_single'){
			
			//determine shortcode args
			$args = shortcode_atts(array(
				'id'		=> ''
			), $atts, $tag);	
	
			$html .= $this->get_portfolio_single($args['id']);
				
		}
		
		//displays gallery for portfolio
		else if($tag == 'portfolio_gallery'){
			
			global $post;

			$html .= $this->get_portfolio_gallery($post->ID);
		}
		
		//gets the gallery slider
		else if($tag == 'portfolio_gallery_slider'){
			global $post;
				
			$html .= $this->get_portfolio_gallery_slider($post->ID);
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
		//get a grid listing of all terms in a taxonomy (category / tags)
		else if($tag == 'portfolio_term_listing'){
			
			$args = shortcode_atts(array(
				'taxonomy_name'	=> 'el_portfolio_category'
			), $atts, $tag);
			
			//get a listing of terms for tax (default categories)
			$html .= $this->get_portfolio_term_listing($args['taxonomy_name']);
		}
		//gets all portfolios belonging to a set term id (category or tag term)
		else if($tag == 'portfolio_listing_for_term'){
			
			$args = shortcode_atts(array(
				'term_id'	=> ''
			), $atts, $tag);
			
			//get a listing of terms for tax (default categories)
			$html .= $this->get_portfolios_for_term($args['term_id']);
		}
		
		
		
				
				
		return $html;
	}
	
	//registers custom widgets 
	public function register_widgets(){
		
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