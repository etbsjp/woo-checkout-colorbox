<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/*-------------------------------------------*/
/* ダッシュボードウィジェット（使い方・注意事項・サポート案内）
/* 設定画面（WooCommerce > 注文確認設定）と同じ権限（publish_pages）で出し分ける。
/*-------------------------------------------*/
if ( ! function_exists( 'clbx_add_dashboard_widget' ) ) {
	/**
	 * ダッシュボードにウィジェットを登録する
	 *
	 * 設定画面のメニュー登録に使っている権限（publish_pages）と揃えて出し分ける。
	 *
	 * @return void
	 */
	function clbx_add_dashboard_widget() {
		if ( ! current_user_can( 'publish_pages' ) ) { return; }
		wp_add_dashboard_widget(
			'clbx_dashboard_widget',
			'ETBS Checkout Colorbox',
			'clbx_render_dashboard_widget'
		);
	}
	add_action( 'wp_dashboard_setup', 'clbx_add_dashboard_widget' );
}

if ( ! function_exists( 'clbx_render_dashboard_widget' ) ) {
	/**
	 * ダッシュボードウィジェットの本文を出力する
	 *
	 * 概要・使い方・注意事項・サポート案内を表示する（リンクのみの箱にしない）。
	 *
	 * @return void
	 */
	function clbx_render_dashboard_widget() {
		$settings_url = admin_url( 'admin.php?page=woochksetting-page' );
		?>
		<p>WooCommerceのチェックアウトページに、注文確定前の確認ダイアログ（姓・名・メールアドレス・商品明細・合計金額）を表示するプラグインです。</p>

		<strong>使い方</strong>
		<ul style="margin:6px 0 12px 1.2em;list-style:disc;">
			<li><strong>WooCommerce &gt; 注文確認設定</strong>で、注文ボタンのテキスト・対象ページのスラッグ・確認ダイアログのタイトルと本文を設定します。</li>
			<li>「実行時に空かどうか判定するテキストボックス」に指定したフィールド（例：姓・名・メールアドレス）は、未入力のまま注文しようとすると確認ダイアログを開かず、そのまま送信を止めます。</li>
		</ul>

		<strong>注意事項</strong>
		<ul style="margin:6px 0 12px 1.2em;list-style:disc;">
			<li>「実行時に空かどうか判定するテキストボックス」に、存在しないフィールド名や空白行を指定するとエラーになります。ご注意ください。</li>
			<li>「注文ページのスラッグ」には、実際のチェックアウトページのスラッグを指定してください。異なるスラッグを指定すると確認ダイアログが表示されません。</li>
		</ul>

		<strong>サポート</strong>
		<p style="margin:6px 0 12px;">有償サポートやカスタマイズは<a href="https://etbs.jp/product-category/wordpress-tools/?utm_source=woo-checkout-colorbox&utm_medium=plugin" target="_blank" rel="noopener noreferrer">こちらのページ</a>からお問い合わせください。開発の継続は<a href="https://etbs.jp/product/donate/?utm_source=woo-checkout-colorbox&utm_medium=plugin" target="_blank" rel="noopener noreferrer">ご支援</a>で応援いただけます。</p>

		<a href="<?php echo esc_url( $settings_url ); ?>" class="button button-primary">注文確認設定を開く</a>
		<?php
	}
}

/*-------------------------------------------*/
/* 寄付・開発依頼リンク（プラグイン一覧行）
/*-------------------------------------------*/
if ( ! function_exists( 'clbx_plugin_row_meta' ) ) {
	/**
	 * プラグイン一覧の自プラグイン行に「開発を支援」「開発のご依頼」リンクを追加する
	 *
	 * @param string[] $links プラグイン一覧の行に表示されるリンクの配列
	 * @param string   $file  プラグインのベースファイル名（plugin_basename() の値）
	 * @param array    $plugin_data readme等から取得したプラグインのメタデータ
	 * @param string   $status 現在表示されているステータス（all/active等）
	 * @return string[] リンクの配列
	 */
	function clbx_plugin_row_meta( $links, $file, $plugin_data, $status ) {
		if ( plugin_basename( CLBX_PLUGIN_FILE ) !== $file ) { return $links; }
		$links[] = '<a href="https://etbs.jp/product/donate/?utm_source=woo-checkout-colorbox&utm_medium=plugin" target="_blank" rel="noopener noreferrer">'
			. esc_html__( '開発を支援', 'woo-checkout-colorbox' ) . '</a>';
		$links[] = '<a href="https://etbs.jp/product-category/wordpress-tools/?utm_source=woo-checkout-colorbox&utm_medium=plugin" target="_blank" rel="noopener noreferrer">'
			. esc_html__( '開発のご依頼', 'woo-checkout-colorbox' ) . '</a>';
		return $links;
	}
	add_filter( 'plugin_row_meta', 'clbx_plugin_row_meta', 10, 4 );
}

/*-------------------------------------------*/
/* 寄付・開発依頼リンク（専用画面のフッター）
/* 画面IDは推測せず実測した値（woocommerce_page_woochksetting-page）で限定する。
/* 限定しないと全管理画面のフッターを乗っ取るため必須。
/*-------------------------------------------*/
if ( ! function_exists( 'clbx_admin_footer_text' ) ) {
	/**
	 * 注文確認設定画面のフッターに支援・依頼リンクを表示する
	 *
	 * @param string $text 元のフッター文言
	 * @return string フッター文言
	 */
	function clbx_admin_footer_text( $text ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'woocommerce_page_woochksetting-page' !== $screen->id ) { return $text; }
		return 'WooCommerce Checkout Colorboxが役に立ったら <a href="https://etbs.jp/product/donate/?utm_source=woo-checkout-colorbox&utm_medium=plugin" target="_blank" rel="noopener noreferrer">開発を支援</a>、カスタマイズは <a href="https://etbs.jp/product-category/wordpress-tools/?utm_source=woo-checkout-colorbox&utm_medium=plugin" target="_blank" rel="noopener noreferrer">開発のご依頼</a> からどうぞ。';
	}
	add_filter( 'admin_footer_text', 'clbx_admin_footer_text' );
}
