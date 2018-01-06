<?php get_header(); ?>

<h1><?php echo single_term_title( '' ); ?></h1>

<?php $term = get_query_var( 'advanced_tags' ); ?>

<h2>Новости</h2>
<?php $tag_archive->posts_by_tag($term,'news'); ?>  

<h2>Статьи</h2>
<?php $tag_archive->posts_by_tag($term,'articles'); ?>

<h2>События</h2>
<?php $tag_archive->posts_by_tag($term,'events'); ?>

<h2>Записи</h2>
<?php $tag_archive->posts_by_tag($term,'posts'); ?>    
    
<?php get_footer() ?>