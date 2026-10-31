<?php
/** Standalone editorial TOC regression checks; no WordPress install is required. */
define( 'ABSPATH', __DIR__ );

function add_filter( ...$args ) {}
function add_shortcode( ...$args ) {}
function is_singular( $type = '' ) { return 'post' === $type; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function get_bloginfo( $show = '' ) { return 'UTF-8'; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function sanitize_title( $value ) { return preg_match('/[^\x00-\x7F]/', $value) ? rawurlencode(str_replace(' ','-',trim($value))) : trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $value ) ), '-' ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain = '' ) { return esc_html( $value ); }
function __( $value, $domain = '' ) { return $value; }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }

/** Small test double for the WordPress HTML Tag Processor methods used here. */
class WP_HTML_Tag_Processor {
	private $html;
	private $tokens = array();
	private $cursor = 0;
	private $current = null;

	public function __construct( $html ) {
		$this->html = $html;
		preg_match_all( '/<([a-z][a-z0-9:-]*)\b[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE );
		foreach ( $matches[0] as $match ) {
			$this->tokens[] = array(
				'tag'   => strtoupper( $matches[1][ count( $this->tokens ) ][0] ),
				'original' => $match[0],
				'raw'   => $match[0],
				'offset' => $match[1],
			);
		}
	}

	public function next_tag( $query = null ) {
		$tag_name = is_array( $query ) && isset( $query['tag_name'] ) ? strtoupper( $query['tag_name'] ) : null;
		while ( isset( $this->tokens[ $this->cursor ] ) ) {
			$index = $this->cursor++;
			if ( null !== $tag_name && $tag_name !== $this->tokens[ $index ]['tag'] ) {
				continue;
			}
			$this->current = $index;
			return true;
		}
		return false;
	}

	public function get_attribute( $name ) {
		if ( null === $this->current || ! preg_match( '/\s' . preg_quote( $name, '/' ) . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $this->tokens[ $this->current ]['raw'], $match ) ) {
			return null;
		}
		return '' !== $match[1] ? $match[1] : ( '' !== $match[2] ? $match[2] : ( $match[3] ?? '' ) );
	}

	public function set_attribute( $name, $value ) {
		if ( null === $this->current ) {
			return;
		}
		$raw = $this->tokens[ $this->current ]['raw'];
		$escaped = htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
		$attribute = preg_quote( $name, '/' );
		if ( preg_match( '/\s' . $attribute . '\s*=/i', $raw ) ) {
			$raw = preg_replace( '/(\s' . $attribute . '\s*=\s*)(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '$1"' . $escaped . '"', $raw, 1 );
		} else {
			$raw = preg_replace( '/\s*\/?\s*>$/', ' ' . $name . '="' . $escaped . '">', $raw, 1 );
		}
		$this->tokens[ $this->current ]['raw'] = $raw;
	}

	public function get_updated_html() {
		$tokens = $this->tokens;
		usort( $tokens, static function ( $left, $right ) { return $right['offset'] <=> $left['offset']; } );
		foreach ( $tokens as $token ) {
			if ( $token['original'] !== $token['raw'] ) {
				$this->html = substr_replace( $this->html, $token['raw'], $token['offset'], strlen( $token['original'] ) );
			}
		}
		return $this->html;
	}
}

require __DIR__ . '/../themes/wp-main-theme/inc/editorial.php';

$content = '<div id="editorial-toc-title"></div><h2 id="same">Same</h2><h2>Same</h2><h2></h2><h3>Same</h3><h2>Final</h2>';
$output  = wpmain_editorial_table_of_contents( $content );

check( false !== strpos( $output, '<nav class="editorial-toc" aria-labelledby="editorial-toc-title-2">' ), 'The TOC label ID avoids a collision with an authored ID.' );
check( false !== strpos( $output, '<h2 id="editorial-toc-title-2">On this page</h2>' ), 'The generated TOC heading uses the collision-safe ID.' );
check( false !== strpos( $output, '<h2 id="same">Same</h2>' ), 'An authored heading ID is preserved.' );
check( false !== strpos( $output, '<a href="#same">Same</a>' ), 'The TOC links to the authored heading ID.' );
check( false !== strpos( $output, '<h2 id="same-2">Same</h2>' ), 'A generated ID avoids an authored ID with the same slug.' );
check( false !== strpos( $output, '<h3 id="same-3">Same</h3>' ), 'A skipped empty heading does not shift later generated IDs.' );
check( false !== strpos( $output, '<a href="#same-3">Same</a>' ), 'The TOC link targets the later duplicate heading.' );
check( false !== strpos( $output, '<h2 id="final">Final</h2>' ), 'Headings after duplicates keep their matching generated IDs.' );

preg_match_all( '/\bid="([^"]+)"/', $output, $id_matches );
check( count( $id_matches[1] ) === count( array_unique( $id_matches[1] ) ), 'Generated IDs and authored unique IDs do not collide.' );
$persian = wpmain_editorial_table_of_contents( '<h2>راهنما</h2><h2>راهنما</h2><h3>ابزار</h3>' );
check( str_contains( $persian, 'id="راهنما"' ) && str_contains( $persian, 'href="#راهنما"' ), 'Persian anchors use actual Unicode IDs, not percent-encoded ID strings.' );
check( str_contains( $persian, 'id="راهنما-2"' ), 'Duplicate Persian headings remain unique.' );

echo "PASS: article TOC preserves authored anchors and assigns collision-safe IDs across empty and duplicate headings.\n";

// Editor-picked recommendations must keep the established public-product boundary.
function post_password_required() { return $GLOBALS['protected_article'] ?? false; }
function get_the_ID() { return 9; }
function get_post_meta( ...$args ) { return $GLOBALS['article_choices'] ?? array(); }
class WooCommerce {}
function shortcode_atts( $defaults, $attributes, $name ) { return array_merge( $defaults, $attributes ); }
function absint( $value ) { return abs( (int) $value ); }
function get_post( $id ) { return (object) array( 'post_type' => 'product', 'post_status' => 2 === $id ? 'private' : 'publish', 'post_password' => 3 === $id ? 'secret' : '' ); }
function esc_url( $value ) { return esc_attr( $value ); }
function wp_kses_post( $value ) { return $value; }
function wc_get_product( $id ) {
	return new class( $id ) {
		private $id;
		public function __construct( $id ) { $this->id = $id; }
		public function is_visible() { return 4 !== $this->id; }
		public function get_permalink() { return '/product/' . $this->id . '/'; }
		public function get_image( $size ) { return '<img src="/image.svg" alt="">'; }
		public function get_name() { return 'Public <pen> ' . $this->id; }
		public function get_price_html() { return '<span>100</span>'; }
	};
}
check( 'Article' === wpmain_editorial_selected_products( 'Article' ), 'Empty optional recommendations change nothing' );
$GLOBALS['article_choices'] = array( 1, 2, 3, 4, 5 );
$selected = wpmain_editorial_selected_products( 'Article' );
check( 1 === substr_count( $selected, 'class="editorial-product"' ) && str_contains( $selected, 'Public &lt;pen&gt; 1' ) && ! str_contains( $selected, '/product/5/' ), 'Recommendations cap selections, escape titles and omit private/password/hidden products' );
$GLOBALS['protected_article'] = true;
check( 'Article' === wpmain_editorial_selected_products( 'Article' ), 'Protected articles do not expose recommendations' );
echo "PASS: editor-picked article products preserve optional rendering, selection bounds and public visibility.\n";
