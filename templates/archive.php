<?php get_header(); ?>

<h1><?php echo single_term_title( '' ); ?></h1>

<?php $tag_archive->all_posts_by_tag($term,get_query_var( 'posttype' )); ?>

<?php get_footer() ?>