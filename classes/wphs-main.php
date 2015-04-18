<?php

if ( ! class_exists( 'WPPS_Main' ) ) {

	/**
	 * Creates a custom post type and associated taxonomies
	 */
	class WPPS_Main extends WPPS_Module implements WPPS_Custom_Post_Type {
		protected static $readable_properties  = array();
		protected static $writeable_properties = array();

		const POST_TYPE_NAME = 'WPHS Blog';
		const POST_TYPE_SLUG = 'wphs-blog';
		const TAG_NAME       = 'Blog Category';
		const TAG_SLUG       = 'wphs-blog-tax';


		/*
		 * Magic methods
		 */

		/**
		 * Constructor
		 *
		 * @mvc Controller
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
		}


		/*
		 * Static methods
		 */

		/**
		 * Registers the custom post type
		 *
		 * @mvc Controller
		 */
		public static function create_post_type() {
			if ( ! post_type_exists( self::POST_TYPE_SLUG ) ) {
				$post_type_params = self::get_post_type_params();
				$post_type        = register_post_type( self::POST_TYPE_SLUG, $post_type_params );

				if ( is_wp_error( $post_type ) ) {
					add_action( 'admin_notices', __CLASS__ . '::wphs_admin_notification' ); 
				}
			}
		}

		/**
		 * Defines the parameters for the custom post type
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		protected static function get_post_type_params() {
			$labels = array(
				'name'               => self::POST_TYPE_NAME . 's',
				'singular_name'      => self::POST_TYPE_NAME,
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New ' . self::POST_TYPE_NAME,
				'edit'               => 'Edit',
				'edit_item'          => 'Edit ' .    self::POST_TYPE_NAME,
				'new_item'           => 'New ' .     self::POST_TYPE_NAME,
				'view'               => 'View ' .    self::POST_TYPE_NAME . 's',
				'view_item'          => 'View ' .    self::POST_TYPE_NAME,
				'search_items'       => 'Search ' .  self::POST_TYPE_NAME . 's',
				'not_found'          => 'No ' .      self::POST_TYPE_NAME . 's found',
				'not_found_in_trash' => 'No ' .      self::POST_TYPE_NAME . 's found in Trash',
				'parent'             => 'Parent ' .  self::POST_TYPE_NAME
			);

			$post_type_params = array(
				'labels'               => $labels,
				'singular_label'       => self::POST_TYPE_NAME,
				'public'               => false,
				'exclude_from_search'  => true,
				'publicly_queryable'   => false,
				'show_ui'              => true,
				'show_in_menu'         => true,
				'register_meta_box_cb' => __CLASS__ . '::add_meta_boxes',
				'taxonomies'           => array( self::TAG_SLUG ),
				'menu_position'        => 20,
				'hierarchical'         => true,
				'capability_type'      => 'post',
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'supports'             => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' )
			);

			return apply_filters( 'wpps_post-type-params', $post_type_params );
		}

		/**
		 * Registers the category taxonomy
		 *
		 * @mvc Controller
		 */
		public static function create_taxonomies() {
			if ( ! taxonomy_exists( self::TAG_SLUG ) ) {
				$tag_taxonomy_params = self::get_tag_taxonomy_params();
				register_taxonomy( self::TAG_SLUG, self::POST_TYPE_SLUG, $tag_taxonomy_params );
			}
		}

		/**
		 * Defines the parameters for the custom taxonomy
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		protected static function get_tag_taxonomy_params() {
			$tag_taxonomy_params = array(
				'label'                 => self::TAG_NAME,
				'labels'                => array( 'name' => self::TAG_NAME, 'singular_name' => self::TAG_NAME ),
				'hierarchical'          => true,
				'rewrite'               => array( 'slug' => self::TAG_SLUG ),
				'update_count_callback' => '_update_post_term_count'
			);

			return apply_filters( 'wpps_tag-taxonomy-params', $tag_taxonomy_params );
		}

		/**
		 * Adds meta boxes for the custom post type
		 *
		 * @mvc Controller
		 */
		public static function add_meta_boxes() {
			add_meta_box(
				'wpps_blog-box',
				'Blog Meta',
				__CLASS__ . '::markup_meta_boxes',
				self::POST_TYPE_SLUG,
				'normal',
				'core'
			);
		}

		/**
		 * Builds the markup for all meta boxes
		 *
		 * @mvc Controller
		 *
		 * @param object $post
		 * @param array  $box
		 */
		public static function markup_meta_boxes( $post, $box ) {
			$variables = array();

			switch ( $box['id'] ) {
				case 'wpps_blog-box':
					$variables['exampleBoxField'] = get_post_meta( $post->ID, 'wphs_blog_meta_field', true );
					$view                         = 'wphs-main/metabox-blog-box.php';
					break;

				/*
				case 'wpps_some-other-box':
					$variables['someOtherField'] = get_post_meta( $post->ID, 'wpps_some-other-field', true );
				 	$view                        = 'wphs-main/metabox-another-box.php';
					break;
				*/

				default:
					$view = false;
					break;
			}

			echo self::render_template( $view, $variables );
		}

		/**
		 * Determines whether a meta key should be considered private or not
		 *
		 * @mvc Model
		 *
		 * @param bool $protected
		 * @param string $meta_key
		 * @param mixed $meta_type
		 * @return bool
		 */
		public static function is_protected_meta( $protected, $meta_key, $meta_type ) {
			switch( $meta_key ) {
				case 'wpps_blog-box':
				case 'wpps_example-box2':
					$protected = true;
					break;

				case 'wpps_some-other-box':
				case 'wpps_some-other-box2':
					$protected = false;
					break;
			}

			return $protected;
		}

		/**
		 * Saves values of the the custom post type's extra fields
		 *
		 * @mvc Controller
		 *
		 * @param int    $post_id
		 * @param object $post
		 */
		public static function save_post( $post_id, $revision ) {
			global $post;
			$ignored_actions = array( 'trash', 'untrash', 'restore' );

			if ( isset( $_GET['action'] ) && in_array( $_GET['action'], $ignored_actions ) ) {
				return;
			}

			if ( ! $post || $post->post_type != self::POST_TYPE_SLUG || ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) {
				return;
			}

			self::save_custom_fields( $post_id, $_POST );
		}

		/**
		 * Validates and saves values of the the custom post type's extra fields
		 *
		 * @mvc Model
		 *
		 * @param int   $post_id
		 * @param array $new_values
		 */
		protected static function save_custom_fields( $post_id, $new_values ) {
			if ( isset( $new_values[ 'wphs_blog_meta_field' ] ) ) {
				if ( false ) { // some business logic check
					update_post_meta( $post_id, 'wphs_blog_meta_field', $new_values[ 'wphs_blog_meta_field' ] );
				} else {
					//add_notice( 'Example of failing validation', 'error' );
					add_action( 'admin_notices', __CLASS__ . '::wphs_admin_notification' ); 
				}
			}
		}
		/**
		 * Used to show the admin notification 
		 *
		 * @mvc Controller
		 *
		 * @param object
		 */
		public static function wphs_admin_notification() {
			$class = "update-nag";
			echo"<div class=\"$class\"> <p>There is some problem please try again later</p></div>"; 
		}
		/**
		 * Defines the [wphs-blog-shortcode] shortcode
		 *
		 * @mvc Controller
		 *
		 * @param array $attributes
		 * return string
		 */
		public static function wphs_shortcode_blog( $attributes ) {
		
			//$variables = array();		
			$get_settings = get_option( 'wphs_settings');	
			
			if(!empty($get_settings)){
				$nofblog = $get_settings['basic']['wphs_noof_blog'];
				if($get_settings['advanced']['wphs_ajax_option']== 1){
					$blogtype = $get_settings['advanced']['wphs_ajax_option'];
				}else{
					$blogtype = 0;				
				}
			}else{
				$nofblog = -1;
				$blogtype = 0;								
			}
			// check the blog type is ajax load or simple upload
			if($blogtype == 1 ){
				// template to show the all normal blog content
				return self::render_template( 'wphs-main/shortcode-wphs-ajax-blog.php');			
			}else{
				// normal blog section will work from inside this loop
				$variables['blog_list'] = '';
				  // get page no from url default is 1
				  $paged = get_query_var( 'paged', 1 );
				  
				  $query_args = array(
					  'post_type' 		=> 'wphs-blog',
					  'post_status'  	=> 'publish',
					  'posts_per_page' 	=> $nofblog,
					  'paged' 			=> $paged,
					  'page' 			=> $paged
					);
				  $the_query = new WP_Query( $query_args ); ?>

				  <?php if ( $the_query->have_posts() ) : ?>
					<!-- the loop -->
					<?php while ( $the_query->have_posts() ) : $the_query->the_post();
						$variables['blog_list'] .= '<li>';
						$variables['blog_list'] .= '<h3>'.get_the_title().'</h3>';
						$variables['blog_list'] .= '<div>';
						$variables['blog_list'] .= '<span class="blogthum">';
						$variables['blog_list'] .= get_the_post_thumbnail( get_the_ID(), 'thumbnail');
						$variables['blog_list'] .= '</span>';
						$variables['blog_list'] .= '<span class="blogtext">'.get_the_excerpt().'</span>';
						$variables['blog_list'] .= '</div>';
						$variables['blog_list'] .= '</li>';
					endwhile; 
					//-- end of the loop -->
					//-- pagination here -->
					if($the_query->found_posts > $nofblog){
					$variables['blog_pagination'] = self::custom_pagination($the_query->max_num_pages,$nofblog,$paged);
					}else{
					$variables['blog_pagination'] ='';
					}
					//-- pagination end here -->
					wp_reset_postdata();

					else:
					$variables['blog_list'] .= '<li>Sorry, no posts matched your criteria.</li>';
					endif;
				
				// template to show the all normal blog content
				return self::render_template( 'wphs-main/shortcode-wphs-shortcode-blog.php', $variables );
			}
		}
		
		/**
		 * Custom post pagination 
		 *
		 * @mvc Controller
		 */		
		public static function custom_pagination($numpages = '', $pagerange = '', $paged='') {

		  if (empty($pagerange)) {
			$pagerange = 2;
		  }

		  /**
		   * This first part of our function is a fallback
		   * for custom pagination inside a regular loop that
		   * uses the global $paged and global $wp_query variables.
		   * 
		   * It's good because we can now override default pagination
		   * in our theme, and use this function in default quries
		   * and custom queries.
		   */
		  global $paged;
		  
		  if (empty($paged)) {
			$paged = 1;
		  }
		  if ($numpages == '') {
			global $wp_query;
			$numpages = $wp_query->max_num_pages;
			if(!$numpages) {
				$numpages = 1;
			}
		  }

		  /** 
		   * We construct the pagination arguments to enter into our paginate_links
		   * function. 
		   */
		  $pagination_args = array(
			'base'            => get_pagenum_link(1) . '%_%',
			'format'          => 'page/%#%',
			'total'           => $numpages,
			'current'         => $paged,
			'show_all'        => False,
			'end_size'        => 1,
			'mid_size'        => $pagerange,
			'prev_next'       => True,
			'prev_text'       => __('&laquo;'),
			'next_text'       => __('&raquo;'),
			'type'            => 'plain',
			'add_args'        => false,
			'add_fragment'    => ''
		  );

		  $paginate_links = paginate_links($pagination_args);
		  $pagination = "";
		  if ($paginate_links) {
			$pagination .= "<nav class='custom-pagination'>";
			$pagination .= "<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
			$pagination .= $paginate_links;
			$pagination .= "</nav>";
		  }
		 return $pagination;
		 
		}
		
		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'init',                     __CLASS__ . '::create_post_type' );
			add_action( 'init',                     __CLASS__ . '::create_taxonomies' );
			add_action( 'save_post',                __CLASS__ . '::save_post', 10, 2 );
			add_filter( 'is_protected_meta',        __CLASS__ . '::is_protected_meta', 10, 3 );

			add_action( 'init',                     array( $this, 'init' ) );

			add_shortcode( 'wphs-shortcode-blog', __CLASS__ . '::wphs_shortcode_blog' );
			
			// add js function in wp_head
			add_action('wp_head',  __CLASS__ . '::wphs_ajax_jsfunction');
			
			// call ajax when super admin is logged in 
			add_action('wp_ajax_wphs_ajax_action', __CLASS__ . '::wphs_ajax_loader');
			
			// load data using wordpress ajax call ajax fuction if no session found			
			add_action('wp_ajax_nopriv_wphs_ajax_action', __CLASS__ . '::wphs_ajax_loader');
			
		}
		
		/**
		 * Add ajax loader script to wp_head
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */		
		static function wphs_ajax_jsfunction(){
			//check the blog type from wordpress backend  
			$get_settings = get_option( 'wphs_settings');	
			
			if(!empty($get_settings)){
				if($get_settings['advanced']['wphs_ajax_option']== 1){
					$blogtype = $get_settings['advanced']['wphs_ajax_option'];
				}else{
					$blogtype = 0;				
				}
			}else{
				$blogtype = 0;								
			}
		if($blogtype == 1){
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					var track_load = 0; //total loaded record group(s)
					var loading  = false; //to prevents multipal ajax loads
					var total_groups = "10"; //total record group(s)
					var ajaxUrl = "<?php echo get_bloginfo('wpurl').'/wp-admin/admin-ajax.php'; ?>";
					var ajaxData = {
						'action': 'wphs_ajax_action',
						'group_no': track_load      // We pass php values differently!
					};
					// onload show few default blog post
					jQuery('#ajaxBlogpost').load(ajaxUrl, ajaxData, function() {	track_load++;}); 
					//load first group
					
					jQuery(window).scroll(function() { //detect page scroll
						
						if(jQuery(window).scrollTop() + jQuery(window).height() == jQuery(document).height())  //user scrolled to bottom of the page?
						{
							if(track_load <= total_groups && loading==false) //there's more data to load
							{
								loading = true; //prevent further ajax loading
								jQuery('.wphs_blog_ajax').show(); //show loading image
								//load data from the server using a HTTP POST request
								var ajaxScrollData = {
									'action': 'wphs_ajax_action',
									'group_no': track_load      // We pass php values differently!
								};
								jQuery.post(ajaxUrl,ajaxScrollData, function(data){
									jQuery("#ajaxBlogpost").append(data); //append received data into the element
									//hide loading image
									jQuery('.wphs_blog_ajax').hide(); //hide loading image once data is received
									track_load++; //loaded group increment
									loading = false; 
								}).fail(function(xhr, ajaxOptions, thrownError) { //any errors?
									alert(thrownError); //alert with HTTP error
									jQuery('.wphs_blog_ajax').hide(); //hide loading image
									loading = false;
								});
								
							}
						}
					});
				});
			</script>
		<?php }	
		}
		/**
		 * Get ajax submit data here and return result
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		static function wphs_ajax_loader(){
			
			//$variables = array();		
			$get_settings = get_option( 'wphs_settings');	
			
			if(!empty($get_settings)){
				$nofblog = $get_settings['basic']['wphs_noof_blog'];
			}else{
				$nofblog = 3;
			}
			
			if($_POST['group_no'] > 0){
				 $args = array(
					'posts_per_page'   => $nofblog,
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'post_type'        => 'wphs-blog',
					'post_status'      => 'publish',
					'suppress_filters' => true 
				);
				
			}else{
				$args = array(
					'posts_per_page'   => $nofblog,
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'post_type'        => 'wphs-blog',
					'post_status'      => 'publish',
					'suppress_filters' => true 
				);
			}
			
			$blog_array = get_posts( $args );
			
			$renderHtml = '';
			if(!empty($blog_array) && is_array($blog_array)){
				foreach($blog_array as $blogData){
				$renderHtml .= '<li id="item_'.$blogData->ID.'">';
				$renderHtml .= '<h3>'.$blogData->post_title.'</h3>';
				$renderHtml .= '<div>';
				$renderHtml .= '<span class="blogthum">';
				 if(has_post_thumbnail( $blogData->ID )){
				$renderHtml .= get_the_post_thumbnail( $blogData->ID, 'thumbnail');
				}else{
				$renderHtml .= '<img width="150" height="150" src="'.WPHS_PLUGINS_URL.'/images/default-thumb.jpeg">';
				}
				$renderHtml .= '</span>';
				$renderHtml .= '<span class="blogtext">'.$blogData->post_content.'</span>';
				$renderHtml .= '</div>';
				$renderHtml .= '</li>';
				}
			}else{
				$renderHtml .= '<li id="item_1">Sorry ! not found any data.</li>';	
			}
			echo $renderHtml;
		}
		/**
		 * Prepares site to use the plugin during activation
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			self::create_post_type();
			self::create_taxonomies();
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
			
			/*   REGISTER ALL CSS FOR LOGIN MODAL */
			wp_register_style('wphs_modal',WPHS_PLUGINS_URL.'/css/front-end.css');

			wp_enqueue_style('wphs_modal');		
		}

		/**
		 * Executes the logic of upgrading from specific older versions of the plugin to the current version
		 *
		 * @mvc Model
		 *
		 * @param string $db_version
		 */
		public function upgrade( $db_version = 0 ) {
			/*
			if( version_compare( $db_version, 'x.y.z', '<' ) )
			{
				// Do stuff
			}
			*/
		}

		/**
		 * Checks that the object is in a correct state
		 *
		 * @mvc Model
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them
		 *
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			return true;
		}
	} // end WPPS_Main
}
