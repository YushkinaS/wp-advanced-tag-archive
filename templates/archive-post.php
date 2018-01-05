<?php get_header(); ?>

<h1><?php echo single_term_title( 'Записи по тегу: ' ); ?></h1>

<?php $tag_archive->all_posts_by_tag($term,'post');?>

<?php get_footer() ?>