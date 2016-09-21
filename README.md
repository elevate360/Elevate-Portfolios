# Elevate-Portfolios
Plugin that facilitates the creation and display of portfolios. Creates a new 'portfolio' content type for easy content management by users. 

Download the following files and upload them to your plugin repository. When activated you will have a new 'portfolio' content type that lets you outline how your 
portfolio will be displayed on it's listing and single pages.

The plugin has been built with extensibility in mind. Listed below are the shortcodes / action hooks applicable

**Shortcodes (to go into your content area)**
	- [portfolio_card]
	- [portfolio_listing]
	- [portfolio_gallery]
	- [portfolio_pagination]
	- [portfolio_categories]
	- [portfolio_tags]
	- [portfolio_microdata]

**WordPress action hooks (to go in your child theme files)**
	- el_display_portfolio_listing
	- el_display_portfolio_categories
	- el_display_portfolio_single
	- el_display_portfolio_tags
	- el_display_portfolio_pagination
	- el_display_portfolio_microdata_information
	- el_display_portfolio_gallery

###How to use in themes
The best way to use this plugin is to integrate it into your theme. 

Create a new single page with the structure `single-el_portfolio.php` and output the various elements you want for each portfolio with the following hook

`add_action('actionName',$post->ID))` 

e.g `add_action('el_display_portfolio_gallery',$post->ID))` to output a gallery for a set portfolio.


###Extending the plugin for custom data
The plugin can be extended to provide additional meta fields / boxes on the portfolios. Extending leverages the plugins ability to automatically display and save data fields.
You could for example extend the plugin to create a new metabox and additional meta fields by the following

```
//Adds a new metabox outputted on the single portfolio page
add_action('el_portfolios_meta_box_args', 'register_specification_metabox');
function register_specification_metabox($html){

	$html[] = array(
		'id'			=> 'portfolio_specifications',
		'title'			=> 'Specification Info',
		'context'		=> 'normal',
		'args'			=> array(
			'description' => 'Specification information for each portfolio'
		)	
	);

	return $html;
}

//adds new fields to the new metabox we created, these fields will be displayed and saved
add_action('el_portfolios_meta_field_args', 'register_new_metafields');
function register_new_metafields($html){
	
	
	$html[] = array(
		'id'			=> 'portfolio_spec_suburb',
		'title'			=> 'Suburb',
		'description'	=> 'Suburb for this element',
		'type'			=> 'text',
		'meta_box_id'	=> 'portfolio_specifications'
	);	
	$html[] = array(
		'id'			=> 'portfolio_spec_dimensions',
		'title'			=> 'Dimensions',
		'description'	=> 'Dimensions for the portfolio',
		'type'			=> 'text',
		'meta_box_id'	=> 'portfolio_specifications'
	);

	
	return $html;
}
```

##Additional filters
Below are some of the filters that can be used to adjust default values for the plugin


**Single portfolio gallery filters**
el_portfolio_gallery_small_image_size
el_portfolio_gallery_large_image_size

**Single portfolio gallery slider filters**
el_porfolio_gallery_slider_image_size

**Single portfolio listing filters**
el_portfolio_archive_image_size

**Single term listing filters**
el_portfolio_term_image_size

**Misc filters**
el_portfolio_all_filter_text

You can hook into these in your child theme to adjust how the plugin will work. For example if you wanted the galleries to output larger resolution images you could 
hook into the `el_portfolio_gallery_small_image_size` filter so that when the gallery grid is built it uses a higher quality image.

```
function change_default_portfolio_gallery_small_image_size($size){
	$size = 'large';
	return $size;
}
add_filter('el_portfolio_gallery_small_image_size', 'change_default_portfolio_gallery_small_image_size');

```



