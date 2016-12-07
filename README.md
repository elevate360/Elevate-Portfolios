# Elevate-Portfolios
Plugin that facilitates the creation and display of portfolios. Creates a new 'portfolio' content type for easy content management by users. 

Download the following files and upload them to your plugin repository. When activated you will have a new 'portfolio' content type that lets you outline how your 
portfolio will be displayed on it's listing and single pages.

The plugin has been built with extensibility in mind. 

##Shortcodes

These can be used in your content area to display elements

**Shortcodes (to go into your content area)**

 - `[portfolio_card]` - Outputs a single portfolio card
 - `[portfolio_listing]` - Output the portfolio listing (with category toggles)
 - `[portfolio_gallery]` - Outputs the gallery for a portfolio
 - `[portfolio_pagination]` - Outputs the next / prev pagination
 - `[portfolio_categories]` - Outputs the categories for a portfolio
 - `[portfolio_tags]` - Outputs the tags for a portfolio
 - `[portfolio_microdata]` - Outputs a microdata card for a single portfolio
 - `[portfolio_term_microdata_information]` - Outputs a microdata card for a single term
 - `[portfolio_term_listing]` - Outputs a grid listing of terms
 - `[portfolio_listing_for_term]` - Gets a grid of portfolios for a single term (category etc)
 
 
##Action Hooks

**WordPress action hooks (to go in your child theme files)**

These can be used in your theme to output content

 - `el_display_portfolio_listing`
 - `el_display_portfolio_categories`
 - `el_display_portfolio_single`
 - `el_display_portfolio_tags`
 - `el_display_portfolio_pagination`
 - `el_display_portfolio_microdata_information`
 - `el_display_portfolio_gallery`
 - `el_display_portfolio_term_microdata_information`

The best way to use this plugin is to integrate it into your theme. 

Create a new single page with the structure `single-el_portfolio.php` and output the various elements you want for each portfolio with the following hook

`do_action('actionName',$post->ID));` 

e.g `do_action('el_display_portfolio_gallery',$post->ID));` to output a gallery for a set portfolio.

**WordPress filter hooks (again to go in your child theme)**

These filters are used to dynamically change the theme. These filters are used so that you can output additional content or change the way the plugin works



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
These filters apply to the gallery elements e.g the grid of images shown on the single portfolio page

 - `el_portfolio_gallery_small_image_size`
 - `el_portfolio_gallery_large_image_size`

**Single portfolio gallery slider filters**
These filters apply to the gallery slider that is shown on the single portfolio page

 - `el_porfolio_gallery_slider_image_size`

**Single portfolio listing filters**
These filters apply when you're displaying a listing of portfolios e.g on the page that shows all portfolio or when displaying portfolio for a specific term (category).

 - `el_portfolio_archive_image_size`
 - `el_portfolio_listing_card_before_content` - Outputs additional content before the main content displayed when viewing a single portfoilio in it's card form

**Single term listing filters**
These filters apply when viewing a single term page e.g if you have a 'residential' category for portfolios and are currently viewing it. These aren't applicable if you just use the portfolio shortcode on a page (it's not a real archive page)
 - `el_portfolio_term_image_size`
 - `el_portfolio_term_card_before_content` - Outputs additional content before the main content when viewing a single term in it's card form

**Misc filters**
Other filters that are used by the plugin 
 - `el_portfolio_all_filter_text`
 - `el_portfolio_previous_text`
 - `el_portfolio_next_text`
 
 
 
 

You can hook into these in your child theme to adjust how the plugin will work. For example if you wanted the galleries to output larger resolution images you could 
hook into the `el_portfolio_gallery_small_image_size` filter so that when the gallery grid is built it uses a higher quality image.

```
function change_default_portfolio_gallery_small_image_size($size){
	$size = 'large';
	return $size;
}
add_filter('el_portfolio_gallery_small_image_size', 'change_default_portfolio_gallery_small_image_size');

```



