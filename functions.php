<?php

require get_template_directory().'/custom_admin/function_admin.php';

function boilerplate_load_assets() {
  wp_enqueue_script('ourmainjs', get_theme_file_uri('/build/index.js'), array('wp-element'), '1.0', true);
  wp_enqueue_style('ourmaincss', get_theme_file_uri('/build/index.css'));
  wp_enqueue_style('style-css', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'boilerplate_load_assets');

function boilerplate_add_support() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'boilerplate_add_support');


//**********************************************************************//
//nav menu
//**********************************************************************//
function register_my_menus() {
  register_nav_menus(
    array(
      'header-menu' => __( 'Header Menu' ),
      'extra-menu' => __( 'Extra Menu' )
    )
  );
}
add_action( 'init', 'register_my_menus' );




//***************************************************************//
// feature img source for wp-json data
//***************************************************************/
add_action( 'rest_api_init', 'add_thumbnail_to_JSON' );
function add_thumbnail_to_JSON() {
//Add featured image
register_rest_field( 
    'post', // Where to add the field (Here, blog posts. Could be an array)
    'featured_image_src', // Name of new field (You can call this anything)
    array(
        'get_callback'    => 'get_image_src',
        'update_callback' => null,
        'schema'          => null,
         )
    );
}



function get_image_src( $object, $field_name, $request ) {
  $feat_img_array = wp_get_attachment_image_src(
    $object['featured_media'], // Image attachment ID
    'thumbnail',  // Size.  Ex. "thumbnail", "large", "full", etc..
    true // Whether the image should be treated as an icon.
  );
  return $feat_img_array[0];
}


//------------------------------------------------------------------------------
// shows some new field/attributes in wp rest api --------------------------------
//------------------------------------------------------------------------------

function prepare_rest($data, $post, $request){
  $_data = $data->data;

  // Thumbnails
  // $thumbnail_id = get_post_thumbnail_id( $post->ID );
  // $thumbnail300x180 = wp_get_attachment_image_src( $thumbnail_id, '300x180' );
  // $thumbnailMedium = wp_get_attachment_image_src( $thumbnail_id, 'medium' );

  //Categories ------------- ----------
  $cats = get_the_category($post->ID);

  //author name  ---------------- -----

  // $author_name = get_the_author($post->ID);  //a depricated function 

// way number one -------
 //   $author_id = $post->post_author; 
//  $author_name =  get_the_author_meta( 'nicename', $author_id );

// way number two -------
$author_id = get_post_field( 'post_author', $post->ID );
$author_name = get_the_author_meta( 'display_name', $author_id );

//--------------------------------------------

//date ----------
$format = get_option( 'date_format' );
// $date_time = get_the_date($format, $post);
$custom_date = get_the_date($format, $post);


//time 
  $format = get_option( 'time_format' );
  $custom_time = get_the_time($format, $post);

//tags

 $ptags = get_the_tags($post);




 

  // $_data['fi_300x180'] = $thumbnail300x180[0];
  // $_data['fi_medium'] = $thumbnailMedium[0];
  $_data['cats'] = $cats;
  $_data['author_name'] = $author_name;
  $_data['custom_date'] = $custom_date;
  $_data['custom_time'] = $custom_time;
  
  $_data['ptags'] = $ptags;
  $data->data = $_data;

  return $data;
}
add_filter('rest_prepare_post', 'prepare_rest', 10, 3);


//--------------------------------------------------//
//----------Custom Post Type For Portfolio -----------//
//--------------------------------------------------//

function portfolio_custom_post_type(){
  $labels = array(
    'name' => 'Portfolio',
    'singular_name' => 'portfolio',
    'add_new' => 'Add Portfolio Item',
    'all_items' => 'Portfolio Items',
    'add_new_item' => 'Add new Portfolio Item',
    'edit_item' => 'Edit Portfolio Item',
    'new_item' => 'New Portfolio Item',
    'view_item' => 'View Portfolio Item',
    'search_item' => 'Search Portfolio',
    'not_found' => 'No Item Found',
    'not_found_in_trash' => 'No Item Found In Trash',
    'parent_item_colon' => 'Parent Item',
  );
  $args = array(
    'labels' => $labels,
    'rewrite' => true,
  
    // 'capabilities' => array(
    //   'create_posts' => 'do_not_allow',
     
    // ),
    // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
    'map_meta_cap' => true, // Set to true for edit otion, Set to `false`, if users are not allowed to edit/delete existing posts
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    // 'show_in_menu'        => true,
    
    // 'show_in_nav_menus'   => true,
    // 'show_in_admin_bar'   => true,
    'menu_position'       => 9,
    // 'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'query_var'  => true,
    'capability_type'     => 'post',
    'show_in_rest' => true,
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions',
      
    ),
    'taxonomies' => array('category', 'post_tag'),
  );
  
   register_post_type('portfolio', $args);
}

add_action('init', 'portfolio_custom_post_type' );


//--------------------------------------------------//
//----------Custom Post Type For Bio -----------//
//--------------------------------------------------//




function bio_custom_post_type(){

  $bioPosts = array(
    'post_type' => 'bio',
    'post_status' => 'publish'
  );
  $bioPostQuery = new WP_Query($bioPosts);
  
 
  $labels = array(
    'name' => 'Bio',
    'singular_name' => 'bio',
    'add_new' => 'Add Bio Item',
    'all_items' => 'Bio Items',
    'add_new_item' => 'Add new Bio Item',
    'edit_item' => 'Edit Bio Item',
    'new_item' => 'New Bio Item',
    'view_item' => 'View Bio Item',
    'search_item' => 'Search Bio',
    'not_found' => 'No Item Found',
    'not_found_in_trash' => 'No Item Found In Trash',
    'parent_item_colon' => 'Parent Item',
   
  );
  $args = array(
    'labels' => $labels,
    'rewrite' => true,

    
  
    'capabilities' => array(
      // 'create_posts' => 'do_not_allow',
      'create_posts' => $bioPostQuery -> have_posts() ? false : true 
     
    ),
    // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
    'map_meta_cap' => true, // Set to true for edit otion, Set to `false`, if users are not allowed to edit/delete existing posts
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    // 'show_in_menu'        => true,
     'show_in_menu'        => 'edit.php?post_type=portfolio',
    
    // 'show_in_nav_menus'   => true,
    // 'show_in_admin_bar'   => true,
    'menu_position'       => 9,
    // 'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'query_var'  => true,
    'capability_type'     => 'post',
    'show_in_rest' => true,
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions',
      
    ),
    'taxonomies' => array('category', 'post_tag'),
  );

  
  
   register_post_type('bio', $args);

  
}

add_action('init', 'bio_custom_post_type' ); 


//--------------------------------------------------//
//----------Custom Post Type For Skill -----------//
//--------------------------------------------------//




function skill_custom_post_type(){

  $skillPosts = array(
    'post_type' => 'skill',
    'post_status' => 'publish'
  );
  $skillPostQuery = new WP_Query($skillPosts);
  
 
  $labels = array(
    'name' => 'Skill',
    'singular_name' => 'skill',
    'add_new' => 'Add Skill Item',
    'all_items' => 'Skill Items',
    'add_new_item' => 'Add new Skill Item',
    'edit_item' => 'Edit Skill Item',
    'new_item' => 'New Skill Item',
    'view_item' => 'View Skill Item',
    'search_item' => 'Search Skill',
    'not_found' => 'No Item Found',
    'not_found_in_trash' => 'No Item Found In Trash',
    'parent_item_colon' => 'Parent Item',
   
  );
  $args = array(
    'labels' => $labels,
    'rewrite' => true,

    
  
    'capabilities' => array(
      // 'create_posts' => 'do_not_allow',
      'create_posts' => $skillPostQuery -> have_posts() ? false : true 
     
    ),
    // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
    'map_meta_cap' => true, // Set to true for edit otion, Set to `false`, if users are not allowed to edit/delete existing posts
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    // 'show_in_menu'        => true,
     'show_in_menu'        => 'edit.php?post_type=portfolio',
    
    // 'show_in_nav_menus'   => true,
    // 'show_in_admin_bar'   => true,
    'menu_position'       => 9,
    // 'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'query_var'  => true,
    'capability_type'     => 'post',
    'show_in_rest' => true,
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions',
    ),
    'taxonomies' => array('category', 'post_tag'),
  );

  
  
   register_post_type('skill', $args);

  
}

add_action('init', 'skill_custom_post_type' );

//*********************************************/
//for making column in skill admin
//*********************************************/

add_filter('manage_skill_posts_columns', 'portfolio_set_skill_columns');
add_action('manage_skill_posts_custom_column', 'portfolio_skill_custom_column',10,2);

function portfolio_set_skill_columns($columns){
  $newColumns = array();
  $newColumns['title'] = 'Title';
  $newColumns['categories'] = 'Categories';
  $newColumns['skill'] = 'Skill';
  return $newColumns;
}

function portfolio_skill_custom_column($column, $post_id){
  switch($column){
    case 'categories':
        echo get_the_excerpt();
        break;
    case 'skill':
    $skill = get_post_meta($post_id, '_skill_value_key', true);
    echo $skill;
        break;
    
  }
}


//*********************************************/
//custom meta box for custom post type skill
//*********************************************/

function portfolio_skill_add_meta_boxes( ){
  add_meta_box( 'skill_meta_box_this_id', 'Skill', 'skill_callback', 'skill','side' );
  
}



//This callback function will print the HTML markup into the meta box
function skill_callback( $post ){
  
  // we should consider keeping things safe. We need to call the function wp_nonce_field
  wp_nonce_field('portfolio_save_skill_data_unique_action_id', 'skill_meta_box_nonce' );

  $value = get_post_meta($post->ID, '_skill_value_key', true);
  echo'<label for="skill_field">Skill </label>';
  echo'<input type="text" id="skill_field" name="skill_field" value="'.esc_attr($value).'" size="25" />';

  
  
}

add_action( 'add_meta_boxes', 'portfolio_skill_add_meta_boxes' );

function portfolio_save_skill_data_unique_action_id($post_id){
 if (! isset($_POST['skill_meta_box_nonce'])){
   return;
 }

 if(! wp_verify_nonce($_POST['skill_meta_box_nonce'], 'portfolio_save_skill_data_unique_action_id')){
   return;
 }

 if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
   return;
 }

 if(! current_user_can('edit_post', $post_id)){
   return;
 }

 if(! isset($_POST['skill_field'])){
   return;
 }

 $my_data = sanitize_text_field($_POST['skill_field']);

 update_post_meta($post_id,'_skill_value_key', $my_data);
 

}

add_action('save_post', 'portfolio_save_skill_data_unique_action_id' );

//**********************************************************/
//custom meta box for custom post type Bio occupation
//**********************************************************/

function portfolio_occupation_add_meta_boxes( ){
  add_meta_box( 'occupation_meta_box_this_id', 'Occupation', 'occupation_callback', 'skill' ,'side' );
  
}



//This callback function will print the HTML markup into the meta box
function occupation_callback( $post){
  
  // we should consider keeping things safe. We need to call the function wp_nonce_field
  wp_nonce_field('portfolio_save_occupation_data_unique_action_id', 'occupation_meta_box_nonce' );

  $value = get_post_meta($post->ID, '_occupation_value_key', true);
 
  
  
  //   echo'<label for="occupation_field"> </label>';
  //  echo'<input type="radio" name="occupation_field" value="Graphic Designer" /> Graphic Designer<br />';
  //  echo'<input type="radio" name="occupation_field" value="Web Developer"  /> Web Developer <br />';
  //  echo'<input type="radio" name="occupation_field" value="Video Editor"  /> Video Editor';
   var_dump($value);
  

  ?>
  
<label for="occupation_field"></label>
 <input type="radio" id="graphic-designer" name="occupation_field"  value="graphic designer"  <?php checked($value, 'graphic designer'); ?>   /> Graphic Designer<br />
  <input type="radio" id="web-developer" name="occupation_field"  value="web developer"  <?php checked($value, 'web developer'); ?> /> Web Developer <br />
 <input type="radio" id="video-editor" name="occupation_field"  value="video editor"   <?php checked($value, 'video editor'); ?> /> Video Editor <br/>
  
<?php
  
  
}

add_action( 'add_meta_boxes', 'portfolio_occupation_add_meta_boxes' );

function portfolio_save_occupation_data_unique_action_id($post_id){
 if (! isset($_POST['occupation_meta_box_nonce'])){
   return;
 }

 if(! wp_verify_nonce($_POST['occupation_meta_box_nonce'], 'portfolio_save_occupation_data_unique_action_id')){
   return;
 }

 if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
   return;
 }

 if(! current_user_can('edit_post', $post_id)){
   return;
 }

 if(! isset($_POST['occupation_field'])){
   return;
 }

 $my_data = sanitize_text_field($_POST['occupation_field']);
//  $my_data = ( isset( $_POST['occupation_field'] ) ? sanitize_html_class( $_POST['occupation_field'] ) : '' );

 update_post_meta($post_id,'_occupation_value_key', $my_data);
//  update_post_meta($post_id, $meta_key, $meta_value, $prev_value)
 

}

add_action('save_post', 'portfolio_save_occupation_data_unique_action_id' );


//**********************************************************/
//custom meta box for custom post type skills
//**********************************************************/


function portfolio_skills_add_meta_boxes( ){
  add_meta_box( 'skills_meta_box_this_id', 'Skills', 'skills_callback', 'skill');
  
}



//This callback function will print the HTML markup into the meta box
function skills_callback( $post ){
  
  // we should consider keeping things safe. We need to call the function wp_nonce_field
  wp_nonce_field('portfolio_save_skills_data_unique_action_id', 'skills_meta_box_nonce' );


  // $value = get_post_meta($post->ID, '_skills_value_key', true);
  // $value = get_post_meta($post->ID, '_skills_value_key', true);
  // $value = get_post_meta( $post->ID, '_skills_value_key', true );
  $value = maybe_unserialize(get_post_meta( $post->ID, '_skills_value_key', true ));
  
 
  
  
//   $skills = array( 'Photoshop', 'Adobe Illustrator', 'Sketch', 'Figma', 'HTML', 'CSS', 'Javascript', 'React js', 'Vue js', 'Angular', 'Svelte', 'Node js', 'Express js','PHP','Laravel','Wrdpress Development',
// 'Adobe After Effects','Cinema 4D','Blender' );


  //  foreach ($skills as $skill ) {
  //   echo'<label for="skills"> </label>';
   
  //   echo '<input type="checkbox" name="skills" value="'.$skill.'" ' . checked( $value['skills'][0], $skill ) . ' /> '.$skill.' <br />';
    
    
  //  }




    // Our associative array here. id = value
  //   $elements = array(
  //     'apple'  => 'Apple',
  //     'orange' => 'Orange',
  //     'banana' => 'Banana'
  // );
    $elements = array(
       'Figma',
       'Sketch',
       'Photoshop',
      'HTML',
      ' CSS',
       'Javascript',
       'React Js',
       'Node js',
       'Vue js',
       'Angular',
       'Wordpress Development'
  );

  echo'<div style="background:#DCE7F6; padding:20px; display:flex;">';
  // Loop through array and make a checkbox for each element
  foreach ( $elements as  $element) {
  // foreach ( $elements as $id => $element) {

      // If the postmeta for checkboxes exist and 
      // this element is part of saved meta check it.
      if ( is_array(  $value ) && in_array( $element,  $value) ) {
      // if ( is_array(  $value ) && in_array( $id,  $value) ) {
          $checked = 'checked="checked"';
      } else {
          $checked = null;
      }

      
      ?>

      <p>
          <input  type="checkbox" name="skills[]" value="<?php echo $element;?>" <?php echo $checked; ?> />
          <?php echo $element;?>
      </p>

      <?php
  }

  echo'</div>';

  
  
}

add_action( 'add_meta_boxes', 'portfolio_skills_add_meta_boxes' );



function skills_data( $post_id ) {
  $is_autosave = wp_is_post_autosave( $post_id );
  $is_revision = wp_is_post_revision( $post_id );
  $is_valid_nonce = ( isset( $_POST[ 'skills_meta_box_nonce' ] ) && wp_verify_nonce( $_POST[ 'skills_meta_box_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
  $my_data = sanitize_text_field($_POST['skills']);
  
  if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
      return;
  }

  // If the checkbox was not empty, save it as array in post meta
  if ( ! empty( $_POST['skills'] ) ) {
   
      update_post_meta( $post_id, '_skills_value_key', $_POST['skills'] );

  // Otherwise just delete it if its blank value.
  } else {
      delete_post_meta( $post_id, '_skills_value_key' );
  }

}

// add_action('save_post', 'portfolio_save_skills_data_unique_action_id' );
add_action('save_post', 'skills_data' );



//**********************************************************/
//custom meta box for custom post type services
//**********************************************************/


function portfolio_services_add_meta_boxes( ){
  add_meta_box( 'services_meta_box_this_id', 'Services', 'services_callback', 'skill');
  
}



//This callback function will print the HTML markup into the meta box
function services_callback( $post ){
  
  // we should consider keeping things safe. We need to call the function wp_nonce_field
  wp_nonce_field('portfolio_save_services_data_unique_action_id', 'services_meta_box_nonce' );


  $value = get_post_meta( $post->ID, 'services_key', true );
  
   var_dump(get_post_meta( $post->ID, 'services_key', true ));
  
 
    $services = array(
       'UI Design',
      'React Development',
       'Wordpress Development',
       'Rest Api Development',
       'Front End Development',
       'Full Stack Development'
  );





  echo'<div style="background:#DCE7F6; padding:20px; display:flex;">';
  

  

      
      ?>

      <p>
         <label for="services">Services</label>
         <select name="services_field" id="services">
         <option>-Select One-</option>
         <?php
          foreach($services as $service){
            
            
            //  echo'<option value="'. $id.'" '. selected( $value, $id ,true ).'> '. $service .' </option>';
             echo'<option value="'. $service.'" '.selected($value, $service, false).' > '. $service .' </option>';
            
          }
         ?>
        
         </select>
      </p>

      <?php
  // }

  echo'</div>';

  
  
}

add_action( 'add_meta_boxes', 'portfolio_services_add_meta_boxes' );




function services_data( $post_id ) {
  
  //  echo '<pre>';
   
  //  var_dump($_POST);
   
  //  echo '</pre>';

  //  die();

    if(isset($_POST["services_field"])){
       
       
         $field_id = $_POST['services_field'];
       

        update_post_meta($post_id, 'services_key', $field_id);
    
    }

}

// add_action('save_post', 'portfolio_save_skills_data_unique_action_id' );
add_action('save_post', 'services_data' );



//**********************************************************/
//custom meta box for custom post type Bio  occupation title
//**********************************************************/

function portfolio_title_add_meta_boxes( ){
  add_meta_box( 'title_meta_box_this_id', 'Occupation Title', 'title_callback', 'skill' ,'side' );
  
}



//This callback function will print the HTML markup into the meta box
function title_callback( $post){
  
  // we should consider keeping things safe. We need to call the function wp_nonce_field
  wp_nonce_field('portfolio_save_title_data_unique_action_id', 'title_meta_box_nonce' );

  $value = get_post_meta($post->ID, '_title_value_key', true);
 
  
  
  
  //  var_dump($value);
  

  
  
echo '<label for="title_field"></label>';
 echo '<textarea type="textarea" id="title" name="title_field"    > '.$value.' </textarea><br />';
  
  

  
  
}

add_action( 'add_meta_boxes', 'portfolio_title_add_meta_boxes' );

function portfolio_save_title_data_unique_action_id($post_id){
 if (! isset($_POST['title_meta_box_nonce'])){
   return;
 }

 if(! wp_verify_nonce($_POST['title_meta_box_nonce'], 'portfolio_save_title_data_unique_action_id')){
   return;
 }

 if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
   return;
 }

 if(! current_user_can('edit_post', $post_id)){
   return;
 }

 if(! isset($_POST['title_field'])){
   return;
 }

 $my_data = sanitize_text_field($_POST['title_field']);
//  $my_data = ( isset( $_POST['title_field'] ) ? sanitize_html_class( $_POST['title_field'] ) : '' );

 update_post_meta($post_id,'_title_value_key', $my_data);
//  update_post_meta($post_id, $meta_key, $meta_value, $prev_value)
 

}

add_action('save_post', 'portfolio_save_title_data_unique_action_id' ); 





//**********************************************************/
//custom meta box for custom post type Bio  occupation slogan
//**********************************************************/

function portfolio_slogan_add_meta_boxes( ){
  add_meta_box( 'slogan_meta_box_this_id', 'Occupation slogan', 'slogan_callback', 'skill' ,'side' );
  
}



//This callback function will print the HTML markup into the meta box
function slogan_callback( $post){
  
  // we should consider keeping things safe. We need to call the function wp_nonce_field
  wp_nonce_field('portfolio_save_slogan_data_unique_action_id', 'slogan_meta_box_nonce' );

  $value = get_post_meta($post->ID, '_slogan_value_key', true);
 
  
  
  
  //  var_dump($value);
  

  
  
echo '<label for="title_field"></label>';
 echo '<textarea type="textarea" id="slogan" name="slogan_field"    > '.$value.' </textarea><br />';
  
  

  
  
}

add_action( 'add_meta_boxes', 'portfolio_slogan_add_meta_boxes' );

function portfolio_save_slogan_data_unique_action_id($post_id){
 if (! isset($_POST['slogan_meta_box_nonce'])){
   return;
 }

 if(! wp_verify_nonce($_POST['slogan_meta_box_nonce'], 'portfolio_save_slogan_data_unique_action_id')){
   return;
 }

 if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
   return;
 }

 if(! current_user_can('edit_post', $post_id)){
   return;
 }

 if(! isset($_POST['slogan_field'])){
   return;
 }

 $my_data = sanitize_text_field($_POST['slogan_field']);
//  $my_data = ( isset( $_POST['title_field'] ) ? sanitize_html_class( $_POST['title_field'] ) : '' );

 update_post_meta($post_id,'_slogan_value_key', $my_data);
//  update_post_meta($post_id, $meta_key, $meta_value, $prev_value)
 

}

add_action('save_post', 'portfolio_save_slogan_data_unique_action_id' );

//*******************************************************/
// Custom  meta box image upload
//*******************************************************/

function add_img_script(){
  wp_enqueue_media();
  wp_enqueue_script('img_upload_script', get_template_directory_uri().'/image_upload.js', array('jquery'), 1.0, true);
  wp_localize_script('img_upload_script','customUploads', array('imgData' => get_post_meta(get_the_ID(), 'uploaded_img_data', true)));
}

add_action('admin_enqueue_scripts' , 'add_img_script');

function image_upload_meta_box( ){
  add_meta_box( 'img_meta_box', 'Upload Image', 'img_callback', 'skill' );
  
}


function img_callback($post){

  wp_nonce_field('img_upload_field_nonce_id', 'img_upload_meta_box_nonce' );
  
  echo'<div style="background:#DCE7F6; padding:20px;">';
   ?>
  
  <img id="img-upload" style="width:10%;"><br/>
  <input type="hidden" id="hidden-field" name="uploaded_img_data">
  <input type="button" value="Add Image" id="add-img" style="height:30px;">
  <input type="button" value="Remove Image" id="del-img" style="height:30px;">
  
  <?php
  echo'</div>';
}


add_action( 'add_meta_boxes', 'image_upload_meta_box' );

function save_uploaded_img($post_id){

  // echo '<pre>';
  
  // var_dump($post_id);
  // echo '</pre>';
  // die();
  
   $is_autosave = wp_is_post_autosave( $post_id );
   $is_revision = wp_is_post_revision( $post_id );
   $is_valid_nonce = ( isset( $_POST[ 'img_upload_meta_box_nonce' ] ) && wp_verify_nonce( $_POST[ 'img_upload_meta_box_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
   $my_data = sanitize_text_field($_POST['skills']);
  
   if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
       return;
   }
    
   if(isset($_POST['uploaded_img_data'])){
     $img_data = json_decode(stripcslashes($_POST['uploaded_img_data']));

     if(is_object($img_data[0])){
       $img_data =  array('id' => intval($img_data[0]->id), 'src' => esc_url_raw($img_data[0]->url));
     }
     else {
       $img_data = [];
     }

     update_post_meta($post_id, 'uploaded_img_data', $img_data);
   }
   
}

add_action('save_post' , 'save_uploaded_img');


//*******************************************************/
// Custom rest api field making for custom meta box skill
//*******************************************************/
function portfolio_skill_custom_rest(){
  
  register_rest_field('skill' ,'skill', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), '_skill_value_key', true);
      
      }
  ));
  
  }

  add_action('rest_api_init','portfolio_skill_custom_rest');
//*******************************************************/
// Custom rest api field making for custom meta box skills
//*******************************************************/
function portfolio_skills_custom_rest(){
  
  register_rest_field('skill' ,'skills', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), '_skills_value_key', true);
      // return 'hello skills';
     
      
      }
  ));
  
  }

  add_action('rest_api_init','portfolio_skills_custom_rest');


//*******************************************************/
// Custom rest api field making for custom meta box occupation
//*******************************************************/
function portfolio_occupation_custom_rest(){
  
  register_rest_field('skill' ,'occupation', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), '_occupation_value_key', true);
      
      }
  ));
  
  }

  add_action('rest_api_init','portfolio_occupation_custom_rest');

//****************************************************************/
// Custom rest api field making for custom meta box occupation title
//******************************************************************/
function portfolio_title_custom_rest(){
  
  register_rest_field('skill' ,'ocupation title', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), '_title_value_key', true);
      
      }
  ));
  
  }

  add_action('rest_api_init','portfolio_title_custom_rest'); 

  //****************************************************************/
// Custom rest api field making for custom meta box occupation slogan
//******************************************************************/
function portfolio_slogan_custom_rest(){
  
  register_rest_field('skill' ,'ocupation slogan', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), '_slogan_value_key', true);
      
      }
  ));
  
  }

  add_action('rest_api_init','portfolio_slogan_custom_rest');


  //****************************************************************/
// Custom rest api field making for custom meta box services
//******************************************************************/
function portfolio_services_custom_rest(){
  
  register_rest_field('skill' ,'services', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), 'services_key', true);
      
      }
  ));
  
  }

  add_action('rest_api_init','portfolio_services_custom_rest');
  //****************************************************************/
// Custom rest api field making for custom meta box image upload
//******************************************************************/
function img_upload_custom_rest(){
  
  register_rest_field('skill' ,'custom image', array(
    'get_callback' => function(){
    
      return get_post_meta(get_the_ID(), 'uploaded_img_data', true);
      
      }
  ));
  
  }

  add_action('rest_api_init','img_upload_custom_rest');



