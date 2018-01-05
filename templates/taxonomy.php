<?php get_header(); ?>
<h1><?php echo single_term_title( '' ); ?></h1>
<?php 
//var_dump( get_query_var( 'posttype' ));       
//var_dump(is_tax('advanced_tags'));    
$term = get_query_var( 'advanced_tags' );
//var_dump($term);  
$tag_archive->posts_by_tag($term,'post');       


?>

<?php get_footer() ?>