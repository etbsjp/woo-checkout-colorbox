<?php
class Woo_Chkbox_Settings {

	public static function init() {
		add_action( 'admin_menu', array(__CLASS__, 'etbs_woochkbox_menu_page'), 10, 2 );
		add_action( 'admin_init', array(__CLASS__, 'woochksetting_admin_init'), 10, 2 );
	}

	public static function etbs_woochkbox_menu_page() {
		add_submenu_page( 'woocommerce', '注文確認設定', '注文確認設定', 'publish_pages', 'woochksetting-page', array(__CLASS__, 'add_woochksetting_page') ); 
	}

	public static function add_woochksetting_page() {
		$option = get_option('woochksetting', Woo_Chkbox_Settings::options_default());
		// フォームのvalue属性に出力するため、属性値エスケープ（esc_attr）を通す
		$op1 = esc_attr( $option['clbx_order_text'] );
		$op2 = esc_attr( $option['clbx_checkout_page'] );
		$op3 = esc_attr( $option['clbx_dialog_title'] );
		$op4 = esc_attr( $option['clbx_dialog_text'] );
		$html = <<< EOF
		<style>#woochksetting-form label{font-weight:bold}input.clbx{width:350px}textarea.clbx{ width: 400px; }</style>
		<h1>注文確認画面設定</h1>
		<form id="woochksetting-form" method="post" action="">
		EOF;
		echo $html;
		wp_nonce_field( 'woochksetting-nonce-key', 'woochksetting-page' );
		$html = <<< EOF
		<p><label>ボタンのテキスト：</label>
		<input type="text" id="clbx_order_text" class="clbx clbx_order_text" name="woochksetting[clbx_order_text]" value="{$op1}"></p>
		<p><label>注文ページのスラッグ：</label>
		<input type="text" id="clbx_checkout_page" class="clbx clbx_order_text" name="woochksetting[clbx_checkout_page]" value="{$op2}"></p>
		<p><label>注文確認画面のタイトル：</label>
		<input type="text" id="clbx_dialog_title" class="clbx clbx_dialog_title" name="woochksetting[clbx_dialog_title]" value="{$op3}"></p>
		<p><label>注文確認画面のテキスト：</label>
		<input type="text" id="clbx_dialog_text" class="clbx clbx_dialog_text" name="woochksetting[clbx_dialog_text]" value="{$op4}"></p>
		EOF;
		echo $html;
		echo '<p><label>実行時に空かどうか判定するテキストボックス（空白行や存在しない名前を指定するとエラーします。ご注意ください。）：</label></p>';
		echo '<textarea id="clbx_inputarea_chk" class="clbx clbx_inputarea_chk" name="woochksetting[clbx_inputarea_chk]" rows="20">';
		echo Woo_Chkbox_Settings::clrset_gets();
		echo '</textarea>';
		echo '<p><input type="submit" value="設定を保存" class="button button-primary button-large"></p>';
		echo '</form>';
	}

	public static function options_default() {
		$default = array(
			'clbx_order_text' => '注文する',
			'clbx_checkout_page' => 'checkout',
			'clbx_dialog_title' => '内容確認',
			'clbx_dialog_text' => 'この内容で注文を確定します。よろしいですか？',
			'clbx_inputarea_chk' => 
				array(
					'billing_last_name', 
					'billing_first_name',
					'billing_email'
				)
		);
		return $default;
	}

	public static function display_area() {
		$displayarea = array(
			'姓' => 'billing_last_name', 
			'名' => 'billing_first_name',
			'ﾒｰﾙｱﾄﾞﾚｽ' => 'billing_email'
		);
		return $displayarea;
	}

	public static function clrset_gets(){
		$option = get_option('woochksetting', Woo_Chkbox_Settings::options_default());
		$arr = $option['clbx_inputarea_chk'];
		$inv = '';
		foreach($arr as $val){
			$inv .= $val . "\n";
		}
		return $inv;
	}

	public static function woochksetting_admin_init() {
		if ( isset( $_POST['woochksetting-page'] ) && $_POST['woochksetting-page'] ) {
			if ( check_admin_referer( 'woochksetting-nonce-key', 'woochksetting-page' ) ) {
				// 保存には設定画面メニューと同じ権限（publish_pages）を要求する
				if ( ! current_user_can( 'publish_pages' ) ) {
					wp_die( esc_html__( 'この操作を行う権限がありません。', 'woo-checkout-colorbox' ) );
				}
				// 保存処理
				if ( isset( $_POST['woochksetting'] ) && is_array( $_POST['woochksetting'] ) ) {
					// $_POST はスラッシュが付与されているため unslash してから扱う
					$posted = wp_unslash( $_POST['woochksetting'] );
					$saved  = array();

					// 既知キーのみを許可する（未知キーは保存しない）。テキスト系項目はsanitize_text_fieldで無害化する
					$text_keys = array( 'clbx_order_text', 'clbx_checkout_page', 'clbx_dialog_title', 'clbx_dialog_text' );
					foreach ( $text_keys as $text_key ) {
						if ( isset( $posted[ $text_key ] ) ) {
							$saved[ $text_key ] = sanitize_text_field( $posted[ $text_key ] );
						}
					}

					// clbx_inputarea_chk の各行はHTMLのidとしてそのまま使われるため、sanitize_keyで無害化し空行は除外する
					$saved['clbx_inputarea_chk'] = array();
					if ( isset( $posted['clbx_inputarea_chk'] ) ) {
						$lines = preg_split( '/\r\n|\r|\n/', (string) $posted['clbx_inputarea_chk'] );
						foreach ( $lines as $line ) {
							$sanitized_line = sanitize_key( trim( $line ) );
							if ( '' !== $sanitized_line ) {
								$saved['clbx_inputarea_chk'][] = $sanitized_line;
							}
						}
					}

					update_option( 'woochksetting', $saved );
				} else {
					update_option( 'woochksetting', '' );
				}
				wp_safe_redirect( menu_page_url( 'woochksetting-page', false ) );
				exit;
			}
		}
	}

}
Woo_Chkbox_Settings::init();
?>
