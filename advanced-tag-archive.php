<?php
/*
Plugin Name: Advanced Tag Archive
Author: Svetlana Yushkina
Author URI: https://github.com/YushkinaS
*/
class Tag_Archive { //создать несколько типов постов, протестировать на большом количестве, провертиь пагинацию

    function __construct() {
        add_action( 'init',                               array( $this, 'register_taxonomy' ) );
       // add_action( 'init',                               array( $this, 'register_post_types' ), 99 );
        add_action( 'init',                               array( $this, 'add_rewrite_rules' ), 99 );
      //  add_action( 'template_redirect',                  array( $this, 'tag_pagination_redirect' ), 99 );
        add_filter( 'template_include',                   array( $this, 'use_custom_template' ), 99 );
        add_filter( 'document_title_parts',               array( $this, 'override_tag_title' ) );    
        
        //rewrite tag '%posttype%' from url => post type name
        $this->posttype_url_to_name = array(
            'post' => 'post', 
        );
        
        $this->posttype_archive_templates = array(
            'post' =>  plugin_dir_path( __FILE__ ) . '/templates/archive-post.php', 
        );
        
        $this->posttype_display_count = array(
            'post' => 7, 
        );
    }
    
    function override_tag_title($title){
        if ( is_tax( 'advanced_tags' ) ) {
            $term = get_queried_object();
            if ( $term ) {
                $posttype = get_query_var( 'posttype' );
                if ( $posttype ) {
                    $posttype_obj = get_post_type_object($this->posttype_url_to_name[$posttype]);
                    if ($posttype_obj) {
                        $title['title'] = $posttype_obj->labels->name . ' по тегу ' . $term->name;
                    }
                } 
                else {
                    $title['title'] = $term->name;
                }

            }
        }
        return $title;
    }

    function add_rewrite_rules(){
        add_rewrite_tag('%posttype%', '([^&]+)');
        $permastruct = "tag/%advanced_tags%/%posttype%";
        add_permastruct( 'advanced_tags1', $permastruct);
    }

/*    function tag_pagination_redirect() {
        if ( preg_match( '#^/tag/([^/]*)/page/[0-9]+/?$#i', $_SERVER['REQUEST_URI'], $matches ) ) {
            wp_redirect( site_url().'/tag/'.$matches[1], 301 );
            exit;
        }
    }*/

    function use_custom_template($template) {
        if ( is_tax('advanced_tags') ) { 
            $posttype = get_query_var( 'posttype' );
            if ($posttype) {
                $template = $this->posttype_archive_templates[$posttype];
                if (empty($template)) {
                    $template = get_404_template();
                }
            }
            else {
                $template = plugin_dir_path( __FILE__ ) . '/templates/taxonomy.php';
            }

        }
        return $template;
    }
    
    
    function register_taxonomy () {

        register_taxonomy( 'advanced_tags', array( 'post' ), array(
            'hierarchical'      => false,
            'labels'            => array(
                'name'          => 'Теги',
                'singular_name' => 'Тег',
                'add_new'       => 'Добавить новый',
                'add_new_item'  => 'Добавить новый тег',
            ),
            'show_ui'           => true,
            'show_in_nav_menus' => true,
            'query_var' => true,
            'rewrite'           => array(
                'slug'       => 'tag',
            ),
        ) );

    }
    
    
/* 
template functions 
*/
    
    function posts_by_tag($term,$posttype) {
         $args = array(
            'post_type' => $this->posttype_url_to_name[$posttype],
            'advanced_tags' => $term,
            'post_status' => 'publish',
            'nopaging' => true, 
            'orderby' => 'date',
            'order' => 'desc',
        );
        $posts = get_posts($args);
        
        $count = 0;
        
        foreach ($posts as $post) {
            if ($count <= $this->posttype_display_count[$posttype]) {
                ?>
                <div><a href="<?php echo get_permalink($post->post_id); ?>"><?php echo $post->post_title; ?><a></div>
                <?php
                $count += 1; 
            }
            else {
                ?>
                <div><a href="<?php echo home_url('tag/'.$term.'/'.$posttype); ?>">Просмотреть все<a></div>
                <?php
            }
                    
        }
    }
    
    function all_posts_by_tag($term,$posttype) {
         $args = array(
            'post_type' => $this->posttype_url_to_name[$posttype],
            'advanced_tags' => $term,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'desc',
        );
        $query = new WP_Query( $args );

        while ( $query->have_posts() ) {
            $query->the_post();
                ?>
                <div><a href="<?php echo get_permalink($query->post->post_id); ?>"><?php echo $query->post->post_title; ?><a></div>
                <?php 
        }

        the_posts_pagination();
    }
    
}
$tag_archive = new Tag_Archive;
?>
