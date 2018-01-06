<?php
/*
Plugin Name: Advanced Tag Archive
Author: Svetlana Yushkina
Author URI: https://github.com/YushkinaS
*/
class Tag_Archive {

    function __construct() {
        add_action( 'init',                               array( $this, 'register_taxonomy' ) ,99);
        add_action( 'init',                               array( $this, 'register_post_types' ), 99 );
        add_action( 'init',                               array( $this, 'add_rewrite_rules' ), 99 );
        add_action( 'template_redirect',                  array( $this, 'tag_pagination_redirect' ), 99 );
        add_filter( 'template_include',                   array( $this, 'use_custom_template' ), 99 );
        add_filter( 'document_title_parts',               array( $this, 'override_tag_title' ) );    
        
        //rewrite tag '%posttype%' from url => post type name
        $this->posttype_url_to_name = array(
            'posts'    => 'post',
            'articles' => 'articles', 
            'events'   => 'events', 
            'news'     => 'news', 
        );
        
        $this->posttype_archive_templates = array(
            'posts'    => plugin_dir_path( __FILE__ ) . '/templates/archive-post.php', 
            'articles' => plugin_dir_path( __FILE__ ) . '/templates/archive.php', 
            'events'   => plugin_dir_path( __FILE__ ) . '/templates/archive.php', 
            'news'     => plugin_dir_path( __FILE__ ) . '/templates/archive.php', 
        );
        
        $this->posttype_display_count = array(
            'posts'    => 7, 
            'articles' => 5, 
            'events'   => 2, 
            'news'     => 3,
        );
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
                'slug' => 'tag',
            ),
        ) );

    }
    
    function register_post_types() {
        register_post_type( 'news', array(
            'labels'       => array(
                'name'               => 'Новости', // основное название для типа записи
                'singular_name'      => 'Новость', // название для одной записи этого типа
            ),
            'show_in_menu' => true,
            'show_ui'      => true,
            'public'       => true,
            'hierarchical' => true,
            'has_archive'  => 'news',
            'supports'     => array( 'title','editor','author','thumbnail','excerpt','comments' ),
            'taxonomies'   => array( 'advanced_tags' )
        ) );
        
        register_post_type( 'articles', array(
            'labels'       => array(
                'name'               => 'Статьи', // основное название для типа записи
                'singular_name'      => 'Статья', // название для одной записи этого типа
            ),
            'show_in_menu' => true,
            'show_ui'      => true,
            'public'       => true,
            'hierarchical' => true,
            'has_archive'  => 'articles',
            'supports'     => array( 'title','editor','author','thumbnail','excerpt','comments' ),
            'taxonomies'   => array( 'advanced_tags' )
        ) );
        
        register_post_type( 'events', array(
            'labels'       => array(
                'name'               => 'События', // основное название для типа записи
                'singular_name'      => 'Событие', // название для одной записи этого типа
            ),
            'show_in_menu' => true,
            'show_ui'      => true,
            'public'       => true,
            'hierarchical' => true,
            'has_archive'  => 'events',
            'supports'     => array( 'title','editor','author','thumbnail','excerpt','comments' ),
            'taxonomies'   => array( 'advanced_tags' )
        ) );
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

   function tag_pagination_redirect() {
        if ( preg_match( '#^/tag/([^/]*)/page/[0-9]+/?$#i', $_SERVER['REQUEST_URI'], $matches ) ) {
            wp_redirect( site_url().'/tag/'.$matches[1], 301 );
            exit;
        }
    }

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
    
/* 
template functions 
*/
    
    function posts_by_tag($term,$posttype) {
         $args = array(
            'post_type'     => $this->posttype_url_to_name[$posttype],
            'advanced_tags' => $term,
            'post_status'   => 'publish',
            'nopaging'      => true, 
            'orderby'       => 'date',
            'order'         => 'desc',
        );
        $posts = get_posts($args);
        
        $count = 0;
        
        foreach ($posts as $post) {
            if ($count <= $this->posttype_display_count[$posttype]) {
                ?>
                <div><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?><a></div>
                <?php
                $count += 1; 
            }               
        }
        if ($count > $this->posttype_display_count[$posttype]) {
            ?>
            <div><a href="<?php echo home_url('tag/'.$term.'/'.$posttype); ?>">Просмотреть все<a></div>
            <?php
        }
    }
    
    function all_posts_by_tag($term,$posttype) {
        
         $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        
         $args = array(
            'post_type'      => $this->posttype_url_to_name[$posttype],
            'advanced_tags'  => $term,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'desc',
            'posts_per_page' => 20,
            'paged' => $paged
        );
        $query = new WP_Query( $args );

        while ( $query->have_posts() ) {
            $query->the_post();
                ?>
                <div><a href="<?php echo get_permalink($query->post->post_id); ?>"><?php echo $query->post->post_title; ?><a></div>
                <?php 
        }
        wp_reset_postdata();
        the_posts_pagination( array('total' => $query->max_num_pages) );
    }
    
}
$tag_archive = new Tag_Archive;
?>
