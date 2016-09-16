<?php
/*
 * Content Type
 * Base class used for creating custom content types. 
 * Other classes can inherit and extent this class to abstract away some common tasks. Handles
 * - Content type creation
 * - Taxonomies creation
 * - Custom meta fields / meta boxes for content type 
 */

 
 class el_content_type{
 	
	//instance of the class
	private static $instance = null;

	//options
	private $post_type_args; 
	private $meta_box_args;
	private $meta_field_args; 
	private $taxonomy_args;
	private $taxonomy_field_args; 
	
	
	//constructor, sets default values and actions
	public function __construct($post_type_args = null, $meta_box_args = null, $meta_field_args = null, $taxonomy_args = null, $taxonomy_field_args = null){
			
		//TODO: Going to need to create HUGE default arrays to hold options, these can be overridden for each post type	
		
		
		$this->set_post_type_args($post_type_args);
		$this->set_meta_box_args($meta_box_args);
		$this->set_meta_field_args($meta_field_args);
		$this->set_taxonomy_args($taxonomy_args);
		$this->set_taxonomy_field_args($taxonomy_field_args);
	
		//add hooks
		add_action('init', array($this, 'register_content_type'));
		add_action('add_meta_boxes_' . $this->post_type_args['post_type_name'], array($this, 'register_meta_boxes'));
		add_action('save_post_' . $this->post_type_args['post_type_name'], array($this, 'save_post'));
		add_action('init', array($this, 'register_taxonomies'));
		
		//taxonomy fields
		if($this->taxonomy_args){
			foreach($this->taxonomy_args as $taxonomy){

				add_action($taxonomy['taxonomy_name'] . '_add_form_fields', array($this, 'add_extra_fields_to_taxonomy_new'));  //creating new tax
				add_action($taxonomy['taxonomy_name'] . '_edit_form_fields', array($this, 'add_extra_fields_to_taxonomy_edit'), 10, 2); //editing tax
				add_action('create_' . $taxonomy['taxonomy_name'], array($this, 'save_extra_taxonomy_field'), 10, 1); //saving tax when creating tax
				add_action('edit_' . $taxonomy['taxonomy_name'], array($this, 'save_extra_taxonomy_field'), 10, 1); //saving existing tax when editing
			}
			
		}
	
		//filter default arguments (triggered early before anything else runs);
		add_action('init', array($this,'filter_default_class_arguments'), 5, 1);
		
	}

	//Update the default values passed to us from the child class. These are filterable so they can be adjusted in themes / other plugins
	public function filter_default_class_arguments(){


		//filterable post type values: add_action('{$nameOfClass}_post_type_args', 'functionName); 
		$this->post_type_args = apply_filters(get_called_class() . '_post_type_args', $this->post_type_args);
		//filterable taxonomy arguments: add_action('{$nameOfClass}_taxonomy_args', 'functionName); 
		$this->taxonomy_args = apply_filters(get_called_class() . '_taxonomy_args', $this->taxonomy_args);
		//filterable taxonomy field arguments: add_action('{$nameOfClass}_taxonomy_field_args', 'functionName); 
		$this->taxonomy_field_args = apply_filters(get_called_class() . '_taxonomy_field_args', $this->taxonomy_field_args);
		//filterable meta box args: add_action('{$nameOfClass}_meta_box_args', 'functionName); 
		$this->meta_box_args = apply_filters(get_called_class() . '_meta_box_args', $this->meta_box_args);
		//filterable meta field args: add_action('{$nameOfClass}_meta_field_args', 'functionName); 
		$this->meta_field_args = apply_filters(get_called_class() . '_meta_field_args', $this->meta_field_args);
		
	}

	
	//property setters
	public function set_post_type_args($post_type_args){
		$this->post_type_args = $post_type_args;
	}
	public function set_meta_box_args($meta_box_args){
		$this->meta_box_args = $meta_box_args;
	}
	public function set_meta_field_args($meta_field_args){
		$this->meta_field_args = $meta_field_args;
	}
	public function set_taxonomy_args($taxonomy_args){
		$this->taxonomy_args = $taxonomy_args;
	}
	public function set_taxonomy_field_args($taxonomy_field_args){
		$this->taxonomy_field_args = $taxonomy_field_args;
	}
	
	
	//register content type 
	public function register_content_type(){
		
		
		
		if($this->post_type_args){
			
			//TODO: make this pretty. update values (such as labels or arguments)
			//check to see if this content type already exists
			if(post_type_exists($this->post_type_args['post_type_name'])){
				
				$current_post_type = get_post_type_object($this->post_type_args['post_type_name']);
				
			}else{
	
				//Default labels for custom post type
				$labels = array(
		            'name'               => $this->post_type_args['post_type_plural_name'],
		            'singular_name'      => $this->post_type_args['post_type_single_name'],
		            'menu_name'          => $this->post_type_args['post_type_plural_name'],
		            'name_admin_bar'     => $this->post_type_args['post_type_single_name'],
		            'add_new'            => 'Add New', 
		            'add_new_item'       => 'Add New '. $this->post_type_args['post_type_single_name'], 
		            'new_item'           => 'New ' . $this->post_type_args['post_type_single_name'],
		            'edit_item'          => 'Edit ' . $this->post_type_args['post_type_single_name'],
		            'view_item'          => 'View ' . $this->post_type_args['post_type_single_name'],
		            'all_items'          => 'All ' . $this->post_type_args['post_type_plural_name'],
		            'set_featured_image' => 'Set Featured Image',
		            'remove_featured_image'	=> 'Remove Featured Image',
		            'use_featured_image'	=> 'Use as Featured Image',
		            'search_items'       => 'Search ' . $this->post_type_args['post_type_plural_name'],
		            'parent_item_colon'  => 'Parent ' . $this->post_type_args['post_type_single_name'] . ':', 
		            'not_found'          => 'No ' . $this->post_type_args['post_type_plural_name'] . ' found.', 
		            'not_found_in_trash' => 'No ' . $this->post_type_args['post_type_plural_name'] . ' found in Trash.',
		        );
				
				//update labels
				if(isset($this->post_type_args['labels'])){
					$labels = array_merge($labels, $this->post_type_args['labels']);
				}
				
				//default arguments for post type
				$args = array(
		            'labels'            => $labels,
		            'public'            => true,
		            'publicly_queryable'=> true,
		            'show_ui'           => true,
		            'show_in_nav'       => true,
		            'query_var'         => true,
		            'hierarchical'      => true,
		            'supports'          => array('title','editor','thumbnail','excerpt'), 
		            'has_archive'       => false,
		            'rewrite'			=> array(
											'slug'		=> $this->post_type_args['post_type_name'],
											'with_front'=> true,
											'feeds'		=> true,
											'pages'		=> true,
										),
		            'menu_position'     => 20,
		            'show_in_admin_bar' => true,
		            'exclude_from_search' => false,
		            'menu_icon'         => 'dashicons-admin-post'
		        );
				
			
				
				
				//update arguments
				if(isset($this->post_type_args['args'])){
					$args = array_merge($args, $this->post_type_args['args']);
				}
				
				//register content type
				register_post_type($this->post_type_args['post_type_name'], $args); 
				
				//flush rewrite rules 
				if(get_option($this->post_type_args['post_type_name'] . '_flush_rewrite') != false){
					flush_rewrite_rules();
					update_option($this->post_type_args['post_type_name'] . '_flush_rewrite', false);
				}
				
				
			}
	
		}
	}
	
	//register taxonomies
	public function register_taxonomies(){
			
		if($this->taxonomy_args){
			
			foreach($this->taxonomy_args as $taxonomy){
				
				//default labels for taxonomy
				$labels = array(
					'name'              => $taxonomy['taxonomy_plural_name'],
					'singular_name'     => $taxonomy['taxonomy_single_name'],
					'search_items'      => 'Search ' . $taxonomy['taxonomy_plural_name'],
					'all_items'         => 'All ' . $taxonomy['taxonomy_plural_name'],
					'parent_item'       => 'Parent ' . $taxonomy['taxonomy_single_name'],
					'parent_item_colon' => 'Parent: ' . $taxonomy['taxonomy_single_name'],
					'edit_item'         => 'Edit ' . $taxonomy['taxonomy_single_name'],
					'update_item'       => 'Update ' . $taxonomy['taxonomy_single_name'],
					'add_new_item'      => 'Add New ' . $taxonomy['taxonomy_single_name'],
					'new_item_name'     => 'New ' . $taxonomy['taxonomy_single_name'],
					'menu_name'         => $taxonomy['taxonomy_plural_name'],
				);
				
				//update labels
				if(isset($taxonomy['labels'])){
					$labels = array_merge($labels, $taxonomy['labels']);
				}
				
				//default arguments for taxonomy
				$args = array(
					'hierarchical'      => true,
					'labels'            => $labels,
					'show_ui'           => true,
					'public'			=> true,
					'show_admin_column' => true,
					'has_archive'		=> true,
					'query_var'			=> $taxonomy['taxonomy_name'],
					'rewrite'			=> array(
						'slug'			=> $taxonomy['taxonomy_name'],
						'with_front'	=> true,
						'hierarchical'	=> true,
					)
				);
				
				//update defaults
				if(isset($taxonomy['args'])){
					$args = array_merge($args, $taxonomy['args']);
				}
				
				//register the taxonomy
				register_taxonomy($taxonomy['taxonomy_name'], $this->post_type_args['post_type_name'], $args);
				//register taxonomy on object type
				register_taxonomy_for_object_type($taxonomy['taxonomy_name'], $this->post_type_args['post_type_name']);
				
				//flush rewrite rules if needed
				if(get_option($taxonomy['taxonomy_name'] . '_flush_rewrite') != false){
					flush_rewrite_rules();
					update_option($taxonomy['taxonomy_name'] . '_flush_write', false); 	
				}
			}
		}
	}
	
	//used to output extra fields to the taxonomy screen (when adding new terms)
	public function add_extra_fields_to_taxonomy_new($taxonomy_name){
			
		$html = '';
		
		
			
		//if we have taxonomy meta fields to add
		if($this->taxonomy_field_args){
			
			foreach($this->taxonomy_field_args as $field){
				
				//render only if this field belongs to this taxonomy
				if($field['taxonomy_name'] == $taxonomy_name){
					//render the metafield based on it's options.
					$html .= $this->render_metafield_element($field);
					
				}
			}
		}
		
		echo $html;
		
	}

	//used to edit extra fields on the taxonomy edit screen
	public function add_extra_fields_to_taxonomy_edit($term, $taxonomy_name){
	
		$html = ''; 

		//if we have tax fields
		if($this->taxonomy_field_args){
			
			foreach($this->taxonomy_field_args as $field){
				
				//only show applicable elements
				if($field['taxonomy_name'] == $taxonomy_name){
					
					//render the field
					$html .= $this->render_metafield_element($field, $term); 
				}
			}
		}
		
		echo $html;
		
	}

	//called when saving our term (creating or editing)
	public function save_extra_taxonomy_field($term_id){
		
		$taxonomy = get_term($term_id);
		$taxonomy_name = $taxonomy->taxonomy;
		//type of field controls that save multiple values
		$multi_value_fields = array('checkbox', 'upload-multi-image', 'related-posts');
		//exclude some fields from sanitization (ruins formatting)
		$exclude_from_sanitization = array('editor'); 
		
		//filterable taxonomy field arguments
		$this->taxonomy_field_args = apply_filters(get_called_class() . '_taxonomy_field_args', $this->taxonomy_field_args);
		
		//if we have tax fields
		if($this->taxonomy_field_args){
				
			//loop through all tax fields
			foreach($this->taxonomy_field_args as $field){
					
				//must belong to this taxonomy
				if($field['taxonomy_name'] == $taxonomy_name){
					
					//determine if this is a multi or single value (affects how we save)
					if(in_array($field['type'], $multi_value_fields)){
						$value = isset($_POST[$field['id']]) ? $_POST[$field['id']] : '';
						if(!empty($value)){
							$value = json_encode($value);
						}
					}else{
						
						if(!in_array($field['type'], $exclude_from_sanitization)){
							$value = isset($_POST[$field['id']]) ? sanitize_text_field($_POST[$field['id']]) : '';
						}else{
							$value = isset($_POST[$field['id']]) ? ($_POST[$field['id']]) : '';
						}	
					}

					//update meta values
					update_term_meta($term_id, $field['id'], $value);
				}
				
			}
		
		}
		
	}
	
	//TODO: Create ability to register admin columns for post meta
	//register custom admin columns
	public function register_admin_columns(){
		
	}
	//TODO: Create output for admin columns
	//output custom admin columns
	public function manage_admin_columns(){
		
	}
	
	//register metaboxes for use
	public function register_meta_boxes($post){
		
		if($this->meta_box_args){
			
			foreach($this->meta_box_args as $meta_box_args){
				//default args
				$args = array(
					'id'		=> $this->post_type_args['post_type_name'] . '_meta_box',
					'title'		=> $this->post_type_args['post_type_single_name'] . ' Meta Box',
					'callback'	=> array($this, 'meta_box_output'),
					'screen'	=> get_post_type($post),
					'context'	=> 'normal',
					'priority'	=> 'default',
					'args'		=> array()
				);
				
				//update args
				$args = array_merge($args, $meta_box_args);
				
				//add meta box
				add_meta_box(
					$args['id'],
					$args['title'],
					$args['callback'],
					$args['screen'],
					$args['context'],
					$args['priority'],
					$args['args']
				);
			}
		}
	}
	
	//output function for metabox (to display custom fields)
	public function meta_box_output($post, $args){
			
		$html = '';

		//output nonce
		wp_nonce_field($this->post_type_args['post_type_name'] . '_nonce', $this->post_type_args['post_type_name'] . '_nonce_field'); 
		
		//display description if our box has one
		if(isset($args['args']['description'])){
			$html .= '<p>' . $args['args']['description'] . '</p>';
		}
		
		//display metafields assigned to this box
		if($this->meta_field_args){
			
			foreach($this->meta_field_args as $field){
				
				//if this field belongs to this box
				if($field['meta_box_id'] == $args['id']){
					
					//render the metafield based on it's options.
					$html .= $this->render_metafield_element($field, $post);
					
				}
				
				
			}
			
		}
		
		
		echo $html;
	}
	
	//renders a metafield, used either for post types or taxonomies to add custom fields
	//Optionally pass in the object it belongs with to prepopulate saved values
	public static function render_metafield_element($field, $object = null){
				
		$instance = self::getInstance();
		
		$html = '';

		//determine if we're passed in a post or tax object
		$object_type = null;
		if(is_a($object, 'WP_Post')){
			$object_type = 'post';
		}else if(is_a($object, 'WP_Term')){
			$object_type = 'term';
		}
		
		//get pre-saved value for field
		$value = '';
		$multi_value_fields = array('checkbox', 'upload-multi-image', 'related-posts');
		//multi-value elements
		if(in_array($field['type'], $multi_value_fields)){
			//post
			if($object_type == 'post'){
				$value = get_post_meta($object->ID, $field['id'], true);
			//terms
			}else if($object_type == 'term'){
				$value = get_term_meta($object->term_id, $field['id'], true);
			}
			//json decode array (if not empty)
			$value = (!empty($value)) ? json_decode($value) : $value;
		}
		//regular single value entries
		else{
			if($object_type == 'post'){
				$value = get_post_meta($object->ID, $field['id'], true);
			}else if($object_type == 'term'){
				$value = get_term_meta($object->term_id, $field['id'], true);
			}
		}
		
	
		$html .= '<table class="form-table">';
			$html .= '<tbody>';
			//depending on field type, display control
			switch($field['type']){
				
				//basic text area
				case 'text': 
					

				
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							$placeholder = '';
							if(isset($field['args']['placeholder'])){
								$placeholder = $field['args']['placeholder'];
							}
						
							$html .= '<input type="text" class="widefat" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value .'" placeholder="' . $placeholder . '"/>';
						$html .= '</td>';
					$html .= '</tr>';
					break;
				
				//number 
				case 'number':
					if(empty($value)){
						$value = 1;
					}
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							$html .= '<input type="number" class="widefat" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value .'" min="1" step="1"/>';
						$html .= '</td>';
					$html .= '</tr>';
					break;	
				
				//predefined options
				case 'select':
					
					//execute only if we have supplied options
					if(isset($field['args']['options'])){
						$html .= '<tr class="form-field wb-field">';
							$html .= '<th valign="top" scope="row">';
								$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
							$html .= '</th>';
							$html .= '<td>';
								if(isset($field['description'])){
									$html .= '<p class="description">' . $field['description'] . '</p>';
								}
								$html .= '<select class="widefat" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '">';
								//loop through all options, output them
								foreach($field['args']['options'] as $option){
									//if we have this value saved already, select it	
									if($option['id'] == $value){
										$html .= '<option selected value="' . $option['id'] . '">' . $option['title'] . '</option>';
									}else{
										$html .= '<option value="' . $option['id'] . '">' . $option['title'] . '</option>';
									}
								}
								$html .= '</select>';
							$html .= '</td>';
						$html .= '</tr>';
					}
					break;
					
				//Radio options, only one chosen at a time
				case 'radio':
					
					//execute only if we have supplied options
					if(isset($field['args']['options'])){
						$html .= '<tr class="form-field wb-field">';
							$html .= '<th valign="top" scope="row">';
								$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
							$html .= '</th>';
							$html .= '<td>';
								if(isset($field['description'])){
									$html .= '<p class="description">' . $field['description'] . '</p>';
								}
								$counter = 0; 
								foreach($field['args']['options'] as $option){
	
									$html .= '<p>';
									
									$checked = '';
									//if we have no saved values and this is the first option, check it
									if(empty($value) && $counter == 0){
										$checked = 'checked';
									}
									
									//ensure checked values are saved
									if($option['id'] == $value){
										$checked = 'checked';
									}
										
									$html .= '<input ' . $checked. ' type="radio" name="' . $field['id'] . '" id="' . $option['id'] . '" value="' . $option['id'] . '"/>';
									$html .= '<label for="' . $option['id'] . '">' . $option['title'] . '</label>';
									$html .= '</p>';
									
									$counter++;
								}
							$html .= '</td>';
						$html .= '</tr>';
					}
					
					break;
				
				//checkboxes, several chosen at once
				case 'checkbox':
					//execute only if we have supplied options
					if(isset($field['args']['options'])){
							
						$html .= '<tr class="form-field wb-field">';
							$html .= '<th valign="top" scope="row">';
								$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
							$html .= '</th>';
							$html .= '<td>';
								if(isset($field['description'])){
									$html .= '<p class="description">' . $field['description'] . '</p>';
								}
								//loop through all options, output
								foreach($field['args']['options'] as $option){
										
									//ensure checked values are saved
									$checked = '';
									if(is_array($value)){
										if(in_array($option['id'] , $value)){
											$checked = 'checked';
										}
									}
										
									$html .= '<p>';
									$html .= '<input ' . $checked. ' type="checkbox" name="' . $field['id'] . '[]" id="' . $option['id'] . '" value="' . $option['id'] . '"/>';
									$html .= '<label for="' . $option['id'] . '"> ' . $option['title'] . '</label>';
									$html .= '</p>';
								}

							$html .= '</td>';
						$html .= '</tr>';
					}
					break;
				
				//textarea content (not editor)
				case 'textarea':
				
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							$html .= '<textarea rows="5" name="' . $field['id'] . '" id="' . $field['id'] . '">';
							$html .= $value;
							$html .= '</textarea>';
						$html .= '</td>';
					$html .= '</tr>';
					break;
					
				//textarea (WP editor)
				case 'editor':
	
					//basic editor settings
					$editor_args = array(
						'media_buttons'		=> true,
						'textarea_name'		=> $field['id'],
					    'textarea_rows'		=> 15
						
					);
					
					
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							
							//add a trigger to the TinyMCE element to ensure editor is saved when editing terms
							//http://wordpress.stackexchange.com/questions/39594/wp-editor-textarea-value-not-updating
							$html .= '<script type="text/javascript">';
							$html .= 'jQuery(document).ready(function($){
										$("#submit").on("mousedown", function() {
											console.log("clicked");
											tinyMCE.triggerSave();
										});
									  });';
							$html .= '</script>';
							
							
							//use output buffering to capture this
							ob_start();
							wp_editor($value, $field['id'], $editor_args);
							$editor_html = ob_get_clean();
							$html .= $editor_html;
						$html .= '</td>';
					$html .= '</tr>';
					
					break;
					
				//Color selector (WordPress style)	
				case 'color':
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
						if(isset($field['description'])){
							$html .= '<p class="description">' . $field['description'] . '</p>';
						}
						//default color (if not value has been selected)
						if(empty($value) && isset($field['args']['default-color'])){
							$value = $field['args']['default-color'];
						}

						$html .= '<input type="text" value="' . $value .'" name="' . $field['id'] . '" class="colorpicker-field" />';
						//show notice if value is empty
						if(empty($value)){
							$html .= '<small>No Color Set</small>';
						}
						$html .= '</td>';
					$html .= '</tr>';
					break;
					
				//single or multiple image uploads	
				case 'upload-multi-image':
				case 'upload-image':
								
					//enqueue media scripts
					wp_enqueue_media();
									
					//determine if multi-upload enabled
					$multi_upload = ($field['type'] == 'upload-multi-image') ? 'true' : 'false';
					$classes = ($multi_upload == 'true') ? 'multi-image' : '';
					
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							$html .= '<div class="image-container">';
								$html .= '<input type="button" value="Select Image" class="image-upload-button" data-multiple-upload="' . $multi_upload . '" data-file-type="image" data-field-name="' . $field['id'] .'"/>';
								$html .= '<div class="image-container cf ' . $classes . '">';
								
								//if multi-upload, loop through all images
								if($multi_upload == 'true'){
									if($value){
										foreach($value as $image_id){
											//get image thumbnail url
											$image_url = wp_get_attachment_image_src($image_id, 'thumbnail', false)[0];
											$field_name = $field['id'] . '[]';
											
											$html .= '<div class="image">';
											$html .=	'<input type="hidden" name="' . $field_name . '" value="' .  $image_id . '"/>';
											$html .=	'<div class="image-preview" style="background-image:url(' . $image_url . ');"></div>';
											$html .=	'<div class="image-controls cf">';
											$html .=		'<div class="control remove_image"><i class="fa fa-minus"></i></div>';	
											$html .=		'<div class="control image_up"><i class="fa fa-caret-up"></i></div>';
											$html .=		'<div class="control image_down"><i class="fa fa-caret-down"></i></div>';
											$html .=	'</div>';
											$html .= '</div>';
										}
									}
								}
								//else singular, display single
								else{
									//get image thumbnail url
									$image_id = $value;
									$image_url = wp_get_attachment_image_src($image_id, 'thumbnail', false)[0];
									$field_name =  $field['id'];
									
									if($value){
										$html .= '<div class="image">';
										$html .=	'<input type="hidden" name="' . $field_name . '" value="' .  $image_id . '"/>';
										$html .=	'<div class="image-preview" style="background-image:url(' . $image_url . ');"></div>';
										$html .=	'<div class="image-controls cf">';
										$html .=		'<div class="control remove_image"><i class="fa fa-minus"></i></div>';	
										$html .=	'</div>';
										$html .= '</div>';
									}
									
								}
	
								$html .= '</div>';
							$html .= '</div>';
						$html .= '</td>';
					$html .= '</tr>';	
					
					
	
					break;
				
				//SVG element (image) uploaded to the media gallery
				case 'upload-svg':
					
					
					//enqueue media scripts
					wp_enqueue_media();

					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							$html .= '<div class="image-container">';
								$html .= '<input type="button" value="Select SVG" class="image-upload-button" data-multiple-upload="false" data-file-type="svg" data-field-name="' . $field['id'] .'"/>';
								$html .= '<div class="image-container cf">';
												
								//if we have presaved value
								if($value){
									
									//get link to SVG resource
									$attachment = get_post($value);
									$image_id = $value;
									$image_url = $attachment->guid;
									$field_name =  $field['id'];
									
									$html .= '<div class="image">';
									$html .=	'<input type="hidden" name="' . $field_name . '" value="' .  $image_id . '"/>';
									$html .=	'<div class="image-preview" style="background-image:url(' . $image_url . ');"></div>';
									$html .=	'<div class="image-controls cf">';
									$html .=		'<div class="control remove_image"><i class="fa fa-minus"></i></div>';	
									$html .=	'</div>';
									$html .= '</div>';
								}
									
								
	
								$html .= '</div>';
							$html .= '</div>';
						$html .= '</td>';
					$html .= '</tr>';	
					
					break;
				
				//Line break
				case 'line-break':
					$html .= '<tr class="form-field wb-field">';
						$html .= '<td><hr/></td>';
					$html .= '</tr>';
				
					break;
				//line break + title separator
				case 'line-break-title':
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th></th>';
						$html .= '<td>';
						if(!empty($field['title'])){
							$html .= '<div><strong>' . $field['title'] . '</strong></div>';
						}
						if(!empty($field['description'])){
							$html .= '<div>' . $field['description'] . '</div>';
						}
						$html .= '<hr/>';
						$html .= '</td>';
					$html .= '</tr>';
				break;
				//Related Post Objects
				case 'related-posts': 
					
					
					//determine if optins will be checkbox or radio
					$output_type = 'checkbox';
					if(isset($field['args']['type'])){
						$output_type = $field['args']['type'];
					}
					
					$html .= '<tr class="form-field wb-field">';
						$html .= '<th valign="top" scope="row">';
							$html .= '<label for="' . $field['id'] . '">' . $field['title'] . '</label>';
						$html .= '</th>';
						$html .= '<td>';
							if(isset($field['description'])){
								$html .= '<p class="description">' . $field['description'] . '</p>';
							}
							
							$related_post_type_name = (isset($field['args']['related_post_type_name']) ? $field['args']['related_post_type_name'] : 'post');
							$post_args = array(
								'post_type'		=> $related_post_type_name,
								'posts_per_page'=> -1,
								'orderby'		=> 'post_title',
								'order'			=> 'ASC',
								'post_status'	=> 'publish'
							);
							$posts = get_posts($post_args);
							if($posts){
								$html .= '<p>Select your applicable post type objects</p>';
								foreach($posts as $post){
									$post_title = $post->post_title;
									$post_id = $post->ID;
									//precheck values if needed
									$checked = '';
									if(!empty($value)){
										if(in_array($post_id, $value)){
											$checked = 'checked';
										}
									}
																	
									$html .= '<p>';
									$html .= '<input ' . $checked. ' type="' . $output_type .'" name="' . $field['id'] . '[]" id="' . $field['id'] . '-' . $post_id .'" value="' . $post_id . '"/>';
									$html .= '<label for="' . $field['id'] . '-' . $post_id . '"> ' . $post_title. '</label>';
									$html .= '</p>';
								}
							}else{
								$html .= '<p>Selected related post type objects not found</p>';
							}
							
							
						$html .= '</td>';
					$html .= '</tr>';	
				
					break;
				//default case, maybe do something custom
				default:
					//TODO: Add a filter here to allow a callback function to run.
					break;	
			}
			$html .= '</tbody>';
		$html .= '</table>';
		return $html;	
	}
	
	//TODO: Come back to
	//register shortcodes for post type
	public function register_shortcodes(){
		
	}
	
	//TODO: Come back to
	//output for the shortcodes
	public function shortcode_output(){
		
	}
	
	//called when saving post type
	public function save_post($post_id){
		
		//filterable post type args
		$this->post_type_args = apply_filters(get_called_class() . '_post_type_args', $this->post_type_args);
		//filterable meta field args
		$this->meta_field_args = apply_filters(get_called_class() . '_meta_field_args', $this->meta_field_args);
		
		
		//check set nonce
		if(!isset($_POST[$this->post_type_args['post_type_name'] . '_nonce_field'])){
			return false;
		}
		//verify nonce
		if(!wp_verify_nonce($_POST[$this->post_type_args['post_type_name'] . '_nonce_field'], $this->post_type_args['post_type_name'] . '_nonce')){
			return false;
		}
		//no autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
			return false;
		}
		//has capability
		if(!current_user_can('edit_post', $post_id)){
			return false;
		}
		
		//All good to save values now
		if($this->meta_field_args){
				
			//TODO: MAKE UNIVERSAL FILTER NEXT
			//type of field controls that save multiple values
			$multi_value_fields = array('checkbox', 'upload-multi-image', 'related-posts');
			//exclude some fields from sanitization (ruins formatting)
			$exclude_from_sanitization = array('editor'); 
				
			foreach($this->meta_field_args as $field){
					
				//multi value component
				if(in_array($field['type'], $multi_value_fields)){
					$field_value = isset($_POST[$field['id']]) ? $_POST[$field['id']] : '';
					if($field_value){
						$field_value = json_encode($field_value);
					}
				}
				//single value
				else{
					if(!in_array($field['type'], $exclude_from_sanitization)){
						$field_value = isset($_POST[$field['id']]) ? sanitize_text_field($_POST[$field['id']]) : '';
					}else{
						$field_value = isset($_POST[$field['id']]) ? ($_POST[$field['id']]) : '';
					}
					
				}
				//update meta
				update_post_meta( $post_id, $field['id'], $field_value);
			}
		}
		
		
		
		
	}

	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
 }


?>