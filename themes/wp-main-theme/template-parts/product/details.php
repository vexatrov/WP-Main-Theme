<?php
defined( 'ABSPATH' ) || exit;
$item = $args['product'];
$data = $args['guidance'];
?>
<?php if ( 'wpmain_usage' === $args['kind'] ) : ?>
	<?php foreach ( array( 'use_cases' => __( 'What can you use it for?', 'wp-main-theme' ), 'audience' => __( 'Who is it for?', 'wp-main-theme' ) ) as $key => $heading ) : ?>
		<?php if ( $data[ $key ] ) : ?><h2><?php echo esc_html( $heading ); ?></h2><div class="product-guidance-copy"><?php echo wp_kses_post( wpautop( esc_html( $data[ $key ] ) ) ); ?></div><?php endif; ?>
	<?php endforeach; ?>
<?php elseif ( 'wpmain_faq' === $args['kind'] ) : ?>
	<h2><?php esc_html_e( 'Product FAQs', 'wp-main-theme' ); ?></h2>
	<?php foreach ( $data['faq'] as $faq ) : ?>
		<?php if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) { continue; } ?>
		<details class="product-faq"><summary><?php echo esc_html( $faq['question'] ); ?></summary><div><?php echo wp_kses_post( wpautop( esc_html( $faq['answer'] ) ) ); ?></div></details>
	<?php endforeach; ?>
<?php elseif ( 'wpmain_comparison' === $args['kind'] ) : ?>
	<?php
	$choices = wpmain_product_comparison_candidates( $item, $data );
	$raw = isset( $_GET['compare_product'] ) ? wp_unslash( $_GET['compare_product'] ) : $data['comparison'];
	$items = array_merge( array( $item ), wpmain_product_comparison_selection( $raw, $choices ) );
	// Keep the established products argument available to child template overrides.
	get_template_part( 'template-parts/product/comparison', null, array( 'product' => $item, 'guidance' => $data, 'choices' => $choices, 'products' => $items ) );
	?>
<?php endif; ?>
