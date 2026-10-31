<?php
/** Complete fictional catalog copy. Local preview only; never deploy this file. */
require_once '/wordpress/wp-load.php';

function wpmain_complete_demo_svg_mimes( $mimes ) { $mimes['svg'] = 'image/svg+xml'; return $mimes; }

/** Original labeled schematics, not supplier photos or claims about real models. */
function wpmain_complete_demo_image( $sku, $kind, $view ) {
	$key = strtolower( $sku ) . '-' . $view;
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_wpmain_complete_image_key', 'meta_value' => $key ) );
	if ( $existing ) { return (int) $existing[0]; }
	$shapes = array(
		'machines' => '<rect x="320" y="150" width="160" height="400" rx="65" fill="#35453e"/><rect x="340" y="180" width="120" height="135" rx="35" fill="#789283"/><circle cx="400" cy="240" r="32" fill="#d78451"/><path d="M340 550h120l-40 90h-40z" fill="#a5b3a9"/>',
		'cartridges' => '<rect x="300" y="190" width="200" height="280" rx="45" fill="#b2c2bb" stroke="#35453e" stroke-width="8"/><rect x="320" y="210" width="160" height="85" rx="20" fill="#d78451"/><path d="M320 470h160l-65 150h-30z" fill="#789283"/><path d="M390 620v45M400 620v45M410 620v45" stroke="#35453e" stroke-width="5"/>',
		'inks' => '<rect x="300" y="275" width="200" height="310" rx="32" fill="#35453e"/><rect x="325" y="200" width="150" height="100" rx="20" fill="#789283"/><path d="M365 200l20-75h30l20 75z" fill="#35453e"/><rect x="300" y="355" width="200" height="145" fill="#e2cfaa"/><circle cx="400" cy="425" r="40" fill="#d78451"/>',
		'accessories' => '<rect x="170" y="260" width="460" height="300" rx="30" fill="#789283"/><rect x="200" y="290" width="400" height="240" rx="20" fill="#d6ddce"/><path d="M260 370h280M260 410h280M260 450h200" stroke="#35453e" stroke-width="12"/>',
	);
	$shape = $shapes[ $kind ] ?? $shapes['accessories'];
	$transform = 2 === $view ? 'translate(800 0) scale(-1 1)' : 'translate(0 0)';
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800"><rect width="800" height="800" fill="' . ( 2 === $view ? '#e6e0d4' : '#f2f1e9' ) . '"/><g transform="' . $transform . '">' . $shape . '</g><text x="400" y="730" text-anchor="middle" font-family="sans-serif" font-size="30" fill="#35453e">FICTIONAL DEMO · ' . esc_html( $sku ) . ' · VIEW ' . (int) $view . '</text></svg>';
	$upload = wp_upload_bits( 'complete-demo-' . $key . '.svg', null, $svg );
	if ( ! empty( $upload['error'] ) ) { return 0; }
	$id = wp_insert_attachment( array( 'post_title' => 'Fictional demo ' . $sku . ' view ' . $view, 'post_mime_type' => 'image/svg+xml', 'post_status' => 'inherit', 'guid' => $upload['url'] ), $upload['file'], 0, true );
	if ( is_wp_error( $id ) || ! $id ) { return 0; }
	wp_update_attachment_metadata( $id, array( 'width' => 800, 'height' => 800, 'file' => _wp_relative_upload_path( $upload['file'] ) ) );
	update_post_meta( $id, '_wp_attachment_image_alt', 'طرح شماتیک ساختگی برای بررسی گالری ' . $sku );
	update_post_meta( $id, '_wpmain_complete_image_key', $key );
	return (int) $id;
}

function wpmain_complete_archive_guide( $name, $kind, $guide_id ) {
	$examples = array(
		'machines' => array( 'پن بی‌سیم', 'پن کابلی', 'منبع تغذیه، وزن و استروک نمونه را کنار هم بخوانید؛ بالاتر بودن یک عدد به‌تنهایی انتخاب بهتر را تعیین نمی‌کند.' ),
		'cartridges' => array( 'راند لاینر', 'مگنوم', 'نام آرایش، تعداد بسته و مشخصات اتصال را مقایسه کنید؛ شباهت تصویر به معنی سازگاری واقعی نیست.' ),
		'inks' => array( 'بطری ۳۰ میلی‌لیتر', 'بطری ۶۰ میلی‌لیتر', 'حجم، قیمت همان گزینه و محتویات بسته را با هم بررسی کنید. در محصول متغیر ابتدا حجم را انتخاب کنید.' ),
		'accessories' => array( 'پد تمرین', 'لوازم جانبی میز', 'ابعاد، محتویات بسته و کاربرد درج‌شده را بخوانید تا دو بسته با تعداد متفاوت را اشتباه مقایسه نکنید.' ),
	);
	$example = $examples[ $kind ] ?? $examples['accessories'];
	$category = get_term_by( 'slug', $kind, 'product_cat' );
	$category_url = $category ? get_term_link( $category ) : '';
	$links = '<a href="' . esc_url( get_permalink( $guide_id ) ) . '">راهنمای آموزشی همین گروه</a>';
	if ( $category_url && ! is_wp_error( $category_url ) ) { $links .= ' و <a href="' . esc_url( $category_url ) . '">مدل‌های همین دسته‌بندی</a>'; }
	return wp_kses_post( '<h2>راهنمای نمونه ' . esc_html( $name ) . '</h2><p>این راهنما برای مقایسه محصولات ساختگی پیش‌نمایش نوشته شده است. توضیحات آن به موجودی واقعی یا تجهیزات قابل‌مصرف اشاره ندارند.</p><h3>چه گزینه‌ای را انتخاب کنیم؟</h3><p>' . esc_html( $example[2] ) . '</p><p>از صفحه محصول، مشخصات، تصاویر و گزینه‌های بسته را بررسی کنید. مقایسه برای کاربرد مشابه مفید است و برچسب سازگار به یک رابطه تأییدشده نیاز دارد؛ روابط این نمونه فقط برای تست هستند.</p><h3>مقایسه خوانا پیش از انتخاب</h3><table><caption>دو گزینه فرضی برای بررسی چیدمان</caption><thead><tr><th>گزینه</th><th>چه چیزی را بخوانیم؟</th></tr></thead><tbody><tr><th>' . esc_html( $example[0] ) . '</th><td>مشخصات ثبت‌شده، قیمت و محتویات همان بسته</td></tr><tr><th>' . esc_html( $example[1] ) . '</th><td>تفاوت کاربرد و گزینه انتخاب‌شده در صفحه محصول</td></tr></tbody></table><h3>پیش از نهایی‌کردن انتخاب</h3><ul><li>نام و شناسه مدل را با تصویر آن تطبیق دهید.</li><li>شرایط ارسال، ضمانت و وضعیت موجودی فروشگاه واقعی را بررسی کنید.</li><li>اگر مشخصات لازم درج نشده است، قبل از سفارش سؤال کنید.</li></ul><h3>پرسش‌های متداول</h3><details><summary>اطلاعات این آرشیو واقعی هستند؟</summary><p>خیر؛ تصاویر، دیدگاه‌ها و توضیحات این پیش‌نمایش ساختگی‌اند و فقط برای بررسی صفحه آماده شده‌اند.</p></details><details><summary>چطور دو گزینه را کنار هم ببینم؟</summary><p>در صفحه محصول، بخش مقایسه مشخصات را باز کنید. پیشنهادهای آن بخش قابل انتخاب‌اند و اطلاعات ثبت‌نشده با خط تیره مشخص می‌شوند.</p></details><details><summary>راهنمای تکمیلی کجاست؟</summary><p>' . $links . ' را ببینید.</p></details>' );
}

function wpmain_complete_catalog_preview() {
	if ( 'ابزار تتو / پیش‌نمایش' !== get_option( 'blogname' ) || '3' === get_option( 'wpmain_complete_catalog_preview' ) || ! function_exists( 'wpmain_catalog_guidance' ) ) { return; }
	$copy = array(
		'PREVIEW-1' => array( 'machines', 'پن بی‌سیم آموزشی', 'برای مقایسه بدنه جمع‌وجور، نمایش وضعیت باتری و چیدمان کنترل‌های یک پن فرضی ساخته شده است.', 'تمرین انتخاب ابزار بدون کابل در یک سناریوی آموزشی؛ داده‌ها راهنمای کار روی پوست نیستند.', 'هنرجویانی که می‌خواهند تفاوت چیدمان پن بی‌سیم و پن کابلی را در صفحه فروشگاه بررسی کنند.', array( 'منبع تغذیه نمایشی' => 'باتری قابل‌تعویض فرضی', 'بسته نمونه' => 'یک بدنه پن و یک پایه نمایشی' ), 'PREVIEW-5', array( 'PREVIEW-2', 'PREVIEW-6' ) ),
		'PREVIEW-2' => array( 'cartridges', 'کارتریج راند لاینر آموزشی', 'آرایش خطی نوک و خوانایی برچسب بسته‌بندی را در یک مدل کاملاً فرضی نشان می‌دهد.', 'مقایسه آرایش سوزن، نام‌گذاری و تعداد بسته در محیط آموزشی، بدون ادعای استریل‌بودن یا مصرف واقعی.', 'هنرجویان و خریدارانی که تفاوت اطلاعات بسته‌های کارتریج را بررسی می‌کنند.', array( 'آرایش نمایشی' => 'راند لاینر فرضی', 'تعداد بسته نمونه' => '۱۰ عدد نمایشی' ), 'PREVIEW-6', array( 'PREVIEW-1', 'PREVIEW-5' ) ),
		'PREVIEW-3' => array( 'inks', 'رنگ مشکی آموزشی با دو حجم', 'دو انتخاب بومی حجم ۳۰ و ۶۰ میلی‌لیتر را برای بررسی تفاوت قیمت و تغییر گزینه محصول نمایش می‌دهد.', 'بررسی انتخاب حجم، بازه قیمت و نمایش بطری رنگ در سفارش آزمایشی؛ این بطری محصول قابل‌مصرف نیست.', 'بازدیدکنندگانی که می‌خواهند انتخاب یک گزینه متغیر را پیش از افزودن به سبد آزمایشی تجربه کنند.', array( 'رنگ نمایشی' => 'مشکی فرضی', 'کاربری نمونه' => 'نمایش انتخاب حجم بطری' ), 'PREVIEW-7', array( 'PREVIEW-4' ) ),
		'PREVIEW-4' => array( 'accessories', 'پد تمرین آموزشی', 'سطح تخت و بسته‌بندی ساده یک پد فرضی را همراه با قیمت فروش ویژه نمونه نمایش می‌دهد.', 'تمرین مقایسه اندازه سطح، تعداد بسته و چیدمان ابزار روی میز آموزشی؛ نه تمرین روی بدن.', 'هنرجویانی که برای آموزش چیدمان ابزار و مشاهده یک کالای تخفیف‌دار به نمونه نیاز دارند.', array( 'ابعاد نمونه' => '۲۰ × ۱۵ سانتی‌متر فرضی', 'محتویات بسته نمونه' => 'یک صفحه تمرین نمایشی' ), 'PREVIEW-8', array( 'PREVIEW-1', 'PREVIEW-3' ) ),
		'PREVIEW-5' => array( 'machines', 'پن روتاری آموزشی', 'فرم بدنه پن کابلی فرضی را برای مقایسه وزن، استروک و شیوه تغذیه در کنار پن بی‌سیم نشان می‌دهد.', 'مقایسه اطلاعات درج‌شده برای یک پن کابلی و یک پن بی‌سیم در صفحه آزمایشی فروشگاه.', 'هنرجویانی که می‌خواهند تفاوت شیوه تغذیه و مشخصات قابل‌مقایسه دو فرم دستگاه را بخوانند.', array( 'منبع تغذیه نمایشی' => 'ورودی کابلی فرضی', 'بسته نمونه' => 'یک پن و یک کابل نمایشی' ), 'PREVIEW-1', array( 'PREVIEW-2', 'PREVIEW-6' ) ),
		'PREVIEW-6' => array( 'cartridges', 'کارتریج مگنوم آموزشی', 'چیدمان پهن نوک در یک کارتریج فرضی را برای مقایسه جدول مشخصات با آرایش خطی نشان می‌دهد.', 'بررسی تفاوت آرایش سوزن و نام مدل در بسته‌های فرضی؛ انتخاب واقعی باید متناسب با کار و مشخصات تأییدشده باشد.', 'هنرجویانی که می‌خواهند تفاوت نمایش دو آرایش سوزن را در جدول مقایسه ببینند.', array( 'آرایش نمایشی' => 'مگنوم فرضی', 'تعداد بسته نمونه' => '۱۰ عدد نمایشی' ), 'PREVIEW-2', array( 'PREVIEW-1', 'PREVIEW-5' ) ),
		'PREVIEW-7' => array( 'inks', 'رنگ میکس آموزشی', 'یک بطری رنگ فرضی با کاربرد نمایشی میکس را کنار رنگ مشکی متغیر قرار می‌دهد تا تفاوت نام، حجم و قیمت خوانا باشد.', 'آموزش خواندن برچسب، مقایسه حجم و نمایش یک بطری در گالری؛ این متن دستور ترکیب یا مصرف رنگ نیست.', 'بازدیدکنندگانی که می‌خواهند تفاوت مدل ثابت و مدل دارای انتخاب حجم را بررسی کنند.', array( 'رنگ نمایشی' => 'میکس فرضی', 'حجم نمونه' => '۳۰ میلی‌لیتر فرضی' ), 'PREVIEW-3', array( 'PREVIEW-4' ) ),
		'PREVIEW-8' => array( 'accessories', 'لوازم محافظتی آموزشی', 'یک بسته فرضی پوشش میز را برای بررسی محتویات بسته، تصویر گالری و چیدمان لوازم جانبی نشان می‌دهد.', 'آموزش مرتب‌کردن میز و مقایسه بسته‌بندی لوازم جانبی در یک محیط ساختگی؛ هیچ ادعای حفاظت پزشکی وجود ندارد.', 'هنرجویانی که خواندن محتویات بسته و تفاوت لوازم جانبی میز را تمرین می‌کنند.', array( 'کاربری نمونه' => 'پوشش نمایشی میز آموزشی', 'محتویات بسته نمونه' => '۲۰ برگ فرضی' ), 'PREVIEW-4', array( 'PREVIEW-1', 'PREVIEW-5' ) ),
		'QA-ENQUIRY' => array( 'machines', 'گزینه آموزشی استعلام قیمت', 'یک مدل فرضی با بسته قابل‌بررسی را بدون قیمت نگه می‌دارد تا مسیر پرسش از فروشگاه و خواندن مشخصات آزمایش شود.', 'آزمون مسیر استعلام و مقایسه اطلاعات یک پن فرضی؛ مبلغ عمداً خالی است و این fixture قیمتی به آن اضافه نمی‌کند.', 'بازدیدکنندگانی که پیش از انتخاب به توضیح بیشتر درباره بسته نمونه نیاز دارند.', array( 'نوع نمونه' => 'پن آموزشی نیازمند استعلام', 'محتویات بسته نمونه' => 'پس از گفت‌وگوی آزمایشی مشخص می‌شود' ), 'PREVIEW-1', array( 'PREVIEW-2' ) ),
		'QA-OUT' => array( 'cartridges', 'گزینه آموزشی ناموجود', 'یک بسته فرضی کارتریج را با وضعیت ناموجود نگه می‌دارد تا اطلاعات محصول و مسیر بررسی گزینه جایگزین قابل‌مشاهده باشد.', 'بررسی رفتار صفحه برای بسته ناموجود؛ موجودی عمداً تغییر نمی‌کند و این متن وعده تأمین نیست.', 'بازدیدکنندگانی که می‌خواهند پیش از بازگشت به دسته، مشخصات و پرسش‌های یک گزینه ناموجود را بخوانند.', array( 'نوع نمونه' => 'بسته کارتریج آموزشی فرضی', 'تعداد بسته نمونه' => '۱۰ عدد نمایشی' ), 'PREVIEW-2', array( 'PREVIEW-1', 'PREVIEW-5' ) ),
	);
	$products = array();
	foreach ( $copy as $sku => $row ) {
		$product = wc_get_product( wc_get_product_id_by_sku( $sku ) );
		if ( ! $product || $product->get_parent_id() || 'publish' !== $product->get_status() ) { return; }
		$products[ $sku ] = $product;
	}
	$locked_at = (int) get_option( 'wpmain_complete_catalog_preview_lock', 0 );
	if ( $locked_at && $locked_at < time() - 900 ) { delete_option( 'wpmain_complete_catalog_preview_lock' ); }
	if ( ! add_option( 'wpmain_complete_catalog_preview_lock', time(), '', false ) ) { return; }
	try {
	$guide_ids = array();
	foreach ( array( 'machines' => 'مقایسه دستگاه‌های آموزشی', 'cartridges' => 'خواندن برچسب کارتریج آموزشی', 'inks' => 'انتخاب حجم رنگ در نمونه', 'accessories' => 'چیدمان میز آموزشی' ) as $kind => $title ) {
		$slug = 'complete-demo-guide-' . $kind;
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		$id = $post ? $post->ID : wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title . ' — راهنمای ساختگی', 'post_content' => '<p>راهنمای ساختگی برای بررسی پیوندهای فروشگاه آزمایشی؛ توصیه استفاده از تجهیزات واقعی نیست.</p><h2>چه اطلاعاتی را مقایسه کنیم؟</h2><p>نام مدل، ویژگی‌های قابل‌نمایش، محتویات بسته و وضعیت انتخاب گزینه را کنار هم بخوانید. در این پیش‌نمایش همه مقادیر فرضی هستند.</p><h2>قبل از انتخاب نمونه</h2><ul><li>تفاوت گزینه‌ها را در جدول مشخصات ببینید.</li><li>تصویر گالری را با نام همان نمونه تطبیق دهید.</li><li>در بخش پرسش‌های محصول، محدودیت نمونه را بخوانید.</li></ul>' ) );
		if ( ! $id || is_wp_error( $id ) ) { return; } $guide_ids[ $kind ] = (int) $id;
	}
	add_filter( 'upload_mimes', 'wpmain_complete_demo_svg_mimes' );
	$complete = true;
	try {
		foreach ( $products as $sku => $product ) {
			list( $kind, $label, $intro, $usage, $audience, $specs, $alternative, $compatible ) = $copy[ $sku ];
			$term = get_term_by( 'slug', $kind, 'product_cat' );
			if ( $term ) {
				$category_ids = $product->get_category_ids();
				$has_child = false;
				foreach ( $category_ids as $assigned ) {
					if ( term_is_ancestor_of( $term->term_id, $assigned, 'product_cat' ) ) { $has_child = true; break; }
				}
				// Keep the pens assigned only to children so parent inclusion is genuinely tested.
				if ( $has_child ) { $category_ids = array_diff( $category_ids, array( $term->term_id ) ); }
				elseif ( ! in_array( (int) $term->term_id, $category_ids, true ) ) { $category_ids[] = (int) $term->term_id; }
				$product->set_category_ids( array_values( $category_ids ) );
			}
			$product->set_short_description( '<p class="demo-product-introduction">' . esc_html( $intro ) . ' این کالا صرفاً نمونه ساختگی فروشگاه است.</p>' );
			$product->set_description( '<div class="demo-product-description"><h2>معرفی ' . esc_html( $label ) . '</h2><p>' . esc_html( $intro ) . '</p><p>' . esc_html( $usage ) . '</p><h3>محتویات و انتخاب نمونه</h3><p>تصاویر شماتیک گالری برای همین مدل نمایشی آماده شده‌اند. جدول مشخصات، توضیح کاربرد و مقایسه را کنار هم ببینید؛ مقادیر این صفحه به کالای واقعی یا تأمین‌کننده مشخصی مربوط نیستند.</p><ul><li>شناسه ثابت نمونه: <bdi>' . esc_html( $sku ) . '</bdi></li><li>نام، قیمت و وضعیت فعلی نمونه در این تکمیل محتوا حفظ شده است.</li><li>روابط سازگاری و جایگزین فقط سناریوی ساختگی بررسی رابط فروشگاه هستند.</li></ul><h3>پیش از خرید واقعی</h3><p>این فروشگاه آزمایشی سفارش واقعی دریافت نمی‌کند. در فروشگاه نهایی، اطلاعات فنی و محتویات بسته باید از منبع محصول تأیید شوند.</p></div>' );
			$attributes = $product->get_attributes();
			$specs['شناسه آزمایش'] = $sku;
			foreach ( $specs as $name => $value ) {
				$attribute = new WC_Product_Attribute(); $attribute->set_name( $name ); $attribute->set_options( array( $value ) ); $attribute->set_visible( true ); $attribute->set_position( count( $attributes ) );
				$attributes[ sanitize_title( $name ) ] = $attribute;
			}
			$product->set_attributes( $attributes );
			$product->update_meta_data( '_wpmain_use_cases', $usage ); $product->update_meta_data( '_wpmain_audience', $audience );
			$product->update_meta_data( '_wpmain_faq', array( array( 'question' => 'این نمونه ' . $label . ' برای استفاده واقعی است؟', 'answer' => 'خیر. توضیحات، مقادیر و تصاویر این صفحه ساختگی‌اند و فقط برای بررسی تجربه فروشگاه استفاده می‌شوند.' ), array( 'question' => 'چه نکته‌ای را در این صفحه مقایسه کنم؟', 'answer' => $usage . ' در بخش مشخصات، اطلاعات نمونه و تفاوت گزینه‌ها را ببینید.' ), array( 'question' => 'روابط محصولات در این نمونه چه معنایی دارند؟', 'answer' => 'سازگاری و جایگزین‌ها فقط پیوندهای نمایشی میان همین محصولات فرضی هستند؛ هیچ سازگاری واقعی از آن‌ها نتیجه نمی‌شود.' ) ) );
			$peer = $products[ $alternative ]->get_id();
			$product->update_meta_data( '_wpmain_comparison', array( $peer ) ); $product->update_meta_data( '_wpmain_alternatives', array( $peer ) );
			$product->update_meta_data( '_wpmain_compatible', array_map( function ( $other ) use ( $products ) { return $products[ $other ]->get_id(); }, $compatible ) );
			$old_guides = $product->get_meta( '_wpmain_guides', true );
			$product->update_meta_data( '_wpmain_guides', array_slice( array_values( array_unique( array_merge( is_array( $old_guides ) ? $old_guides : array(), array( $guide_ids[ $kind ] ) ) ) ), 0, 4 ) );
			$gallery = array();
			foreach ( array( 1, 2 ) as $view ) { $image = wpmain_complete_demo_image( $sku, $kind, $view ); if ( $image ) { $gallery[] = $image; } else { $complete = false; } }
			if ( ! $product->get_image_id() ) {
				$type = array( 'machines' => 'pen', 'cartridges' => 'cartridge', 'inks' => 'ink', 'accessories' => 'accessory' )[ $kind ];
				$main = get_posts( array( 'name' => 'demo-' . $type, 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids' ) );
				if ( $main ) { $product->set_image_id( $main[0] ); } else { $complete = false; }
			}
			$product->set_gallery_image_ids( array_values( array_unique( array_merge( $product->get_gallery_image_ids(), $gallery ) ) ) );
			$product->set_reviews_allowed( true ); $product->save();
			if ( 'PREVIEW-3' === $sku && count( $gallery ) === 2 ) {
				foreach ( $product->get_children() as $child_id ) {
					$variation = wc_get_product( $child_id );
					if ( ! $variation || ! $variation->is_type( 'variation' ) ) { continue; }
					$large = '600000' === $variation->get_regular_price();
					$variation->set_image_id( $gallery[ $large ? 1 : 0 ] );
					if ( ! $variation->get_sku( 'edit' ) ) { $variation->set_sku( 'PREVIEW-3-' . ( $large ? '60ML' : '30ML' ) ); }
					$variation->save();
				}
			}
			$reviews = get_comments( array( 'post_id' => $product->get_id(), 'status' => 'all', 'number' => 1, 'meta_key' => '_wpmain_complete_review_sku', 'meta_value' => $sku ) );
			$review = array( 'comment_post_ID' => $product->get_id(), 'comment_author' => 'بازبین نمایشی — دیدگاه ساختگی', 'comment_author_email' => 'demo-review@example.invalid', 'comment_content' => 'این دیدگاه ساختگی است و تجربه خرید واقعی نیست. در بررسی آموزشی «' . $label . '»، خوانایی توضیح کاربرد، تصاویر گالری و مشخصات «' . implode( '، ', array_keys( $specs ) ) . '» را مقایسه کردم. این متن فقط نمونه نمایش دیدگاه محصول است.', 'comment_type' => 'review', 'comment_approved' => 1, 'user_id' => 0 );
			if ( $reviews ) { $review['comment_ID'] = $reviews[0]->comment_ID; wp_update_comment( $review ); $review_id = $review['comment_ID']; } else { $review_id = wp_insert_comment( $review ); }
			if ( $review_id ) { update_comment_meta( $review_id, '_wpmain_complete_review_sku', $sku ); update_comment_meta( $review_id, 'rating', 4 ); update_comment_meta( $review_id, 'verified', 0 ); wp_update_comment_count_now( $product->get_id() ); WC_Comments::clear_transients( $product->get_id() ); } else { $complete = false; }
		}
	} finally { remove_filter( 'upload_mimes', 'wpmain_complete_demo_svg_mimes' ); }
	update_option( 'woocommerce_enable_reviews', 'yes' ); update_option( 'woocommerce_enable_review_rating', 'yes' );
	foreach ( array( 'product_cat', 'product_brand' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) { continue; }
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			if ( 'product_cat' === $taxonomy && ! in_array( $term->slug, array( 'machines', 'cartridges', 'inks', 'accessories', 'wireless', 'rotary' ), true ) ) { continue; }
			if ( 'product_brand' === $taxonomy && false === strpos( $term->name, 'برند نمونه' ) ) { continue; }
			$name = esc_html( $term->name ); $guide_kind = isset( $guide_ids[ $term->slug ] ) ? $term->slug : ( in_array( $term->slug, array( 'wireless', 'rotary' ), true ) ? 'machines' : 'accessories' );
			if ( 'product_brand' === $taxonomy ) {
				foreach ( $products as $sku => $item ) {
					if ( has_term( $term->term_id, 'product_brand', $item->get_id() ) ) { $guide_kind = $copy[ $sku ][0]; break; }
				}
			}
			update_term_meta( $term->term_id, '_wpmain_archive_intro', 'نمونه راهنمای «' . $term->name . '» برای مقایسه کاربرد، بسته و مشخصات محصولات ساختگی این پیش‌نمایش.' );
			update_term_meta( $term->term_id, '_wpmain_archive_highlights', array( 'مقایسه مشخصات نمونه', 'دیدگاه‌ها و پرسش‌های ساختگی', 'تصاویر شماتیک آموزشی' ) );
			update_term_meta( $term->term_id, '_wpmain_archive_content', wpmain_complete_archive_guide( $term->name, $guide_kind, $guide_ids[ $guide_kind ] ) );
		}
	}
	if ( $complete ) { update_option( 'wpmain_complete_catalog_preview', '3', false ); }
	} finally { delete_option( 'wpmain_complete_catalog_preview_lock' ); }
}
if ( did_action( 'init' ) ) { wpmain_complete_catalog_preview(); } else { add_action( 'init', 'wpmain_complete_catalog_preview', 75 ); }
