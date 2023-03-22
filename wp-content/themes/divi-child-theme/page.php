<?php

get_header();

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );

?>

<?php if ( ! $is_page_builder_used ) : ?>

<div id="main-content">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if ( ! $is_page_builder_used ) : ?>

			
		<?php
			$thumb = '';

			$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

			$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
			$classtext = 'et_featured_image';
			$titletext = get_the_title();
			$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
			$thumb = $thumbnail["thumb"];

			if ( 'on' === et_get_option( 'divi_page_thumbnails', 'false' ) && '' !== $thumb )
				print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height );
		?>

		<?php endif; ?>

			<div class="entry-content">

				<!-- Subpage Banner -->
				<?php 
					// Get Featured Image 

					if ( has_post_thumbnail( $post->ID ) ) :
					    $imageInfo = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
					    $imageUrl = $imageInfo[0];
					else:
					    $imageUrl = '/wp-content/uploads/banner-home.png';
					endif;
				?>

				<?php 
					$classes = get_body_class();
					if (in_array('no-header',$classes)): 

						// Don't Add Header
				?>
				<?php else: ?>
				<div class="et_pb_section et_pb_with_background et_section_regular" style="background-image:url('<?php echo $imageUrl; ?>');">
					<div class="et_pb_row et_pb_gutters1">
						<div class="et_pb_column et_pb_column_4_4 et_pb_css_mix_blend_mode_passthrough">
							<div class="et_pb_module et_pb_text cuw-bnnr-dscrptn et_pb_bg_layout_light et_pb_text_align_left">
								<div class="et_pb_text_inner">
									<h1><?php the_title(); ?></h1>	
								</div>
							</div> <!-- .et_pb_text -->
						</div> <!-- .et_pb_column -->
					</div> <!-- .et_pb_row -->
				</div>
				<!-- End Subpage Banner -->
			<?php endif; ?>
				<!-- Subpage Content -->
				<div class="et_pb_section et_section_regular">
					<div class="et_pb_row">
						<div class="et_pb_column et_pb_column_4_4 et_pb_css_mix_blend_mode_passthrough et-last-child">
							<div class="et_pb_module et_pb_text et_pb_bg_layout_light et_pb_text_align_left">
								<div class="et_pb_text_inner">
									<?php the_content(); ?>
								</div>
							</div> <!-- .et_pb_text -->
						</div> <!-- .et_pb_column -->
					</div> <!-- .et_pb_row -->
				</div>
				<!-- Footer -->
			</div> <!-- .entry-content -->

		<?php
			// if ( ! $is_page_builder_used && comments_open() && 'on' === et_get_option( 'divi_show_pagescomments', 'false' ) ) comments_template( '', true );
		?>

		</article> <!-- .et_pb_post -->

	<?php endwhile; ?>

</div> <!-- #main-content -->

<?php endif; ?>

<?php if ( $is_page_builder_used ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if ( ! $is_page_builder_used ) : ?>

			<h1 class="entry-title main_title"><?php the_title(); ?></h1>
		<?php
			$thumb = '';

			$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

			$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
			$classtext = 'et_featured_image';
			$titletext = get_the_title();
			$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
			$thumb = $thumbnail["thumb"];

			if ( 'on' === et_get_option( 'divi_page_thumbnails', 'false' ) && '' !== $thumb )
				print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height );
		?>

		<?php endif; ?>

			<div class="entry-content">
			<?php
				the_content();

				if ( ! $is_page_builder_used )
					wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
			?>
			</div> <!-- .entry-content -->

		<?php
			if ( ! $is_page_builder_used && comments_open() && 'on' === et_get_option( 'divi_show_pagescomments', 'false' ) ) comments_template( '', true );
		?>

		</article> <!-- .et_pb_post -->

	<?php endwhile; ?>

<?php endif; ?>

<?php get_footer(); ?>