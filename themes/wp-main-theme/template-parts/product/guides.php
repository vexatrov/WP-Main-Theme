<?php
defined( 'ABSPATH' ) || exit;
$guides = array();
foreach ( $args['guides'] as $id ) { $guide_post = get_post( $id ); if ( $guide_post && 'publish' === $guide_post->post_status && ! $guide_post->post_password ) { $guides[] = $guide_post; } }
if ( ! $guides ) { return; }
?>
<section class="product-resources" aria-labelledby="product-guides-title">
	<h2 id="product-guides-title"><?php esc_html_e( 'Guides to help you choose', 'wp-main-theme' ); ?></h2>
	<ul class="product-guide-links"><?php foreach ( $guides as $guide ) : ?><li><a href="<?php echo esc_url( get_permalink( $guide ) ); ?>"><?php echo esc_html( get_the_title( $guide ) ); ?><span aria-hidden="true"> &larr;</span></a></li><?php endforeach; ?></ul>
</section>
