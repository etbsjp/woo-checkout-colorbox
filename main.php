<?php
/**
 * Plugin Name: WooCommerce Checkout Colorbox
 * Version: 1.0.6
 * Description: WooCommerce 注文確定ページに確認用のダイアログを表示します。
 * Author: DAI
 * Author URI: https://etbs.jp
 * Plugin URI: https://etbs.jp/product-category/wordpress-tools/
 * Text Domain: woo-checkout-colorbox
 * Domain Path: /languages
 * @package woo-checkout-colorbox
 */

// プラグインファイルのパスを定数化しておく（js/css の URL 生成をディレクトリ名固定にしないため）
define( 'CLBX_PLUGIN_FILE', __FILE__ );

$clbx_version = '1.0.6';

// 設定
require_once( dirname( __FILE__ ) . '/tools/setting.php' );

$option = get_option('woochksetting', Woo_Chkbox_Settings::options_default());
$clbx_order_text = $option['clbx_order_text'];
$clbx_checkout_page = $option['clbx_checkout_page'];
$clbx_dialog_title = $option['clbx_dialog_title'];
$clbx_dialog_text = $option['clbx_dialog_text'];

if ( ! function_exists( 'clrbx_styles' ) ){
	function clrbx_styles() {
		global $clbx_version;
		global $clbx_checkout_page;
		if( is_page( $clbx_checkout_page ) ) {
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'col-scripts', plugins_url( 'js/col.js', CLBX_PLUGIN_FILE ), array( 'jquery' ), $clbx_version, true );

			// 設定値（表示チェック対象のセレクタ）をJSへ渡す。wp_json_encode()でJSON化することで
			// セレクタ文字列の外に出て任意のJSコードを書けてしまう経路を塞ぐ。
			$option = get_option( 'woochksetting', Woo_Chkbox_Settings::options_default() );
			$clbx_script_data = array(
				'inputareaChk' => $option['clbx_inputarea_chk'],
				'displayArea'  => Woo_Chkbox_Settings::display_area(),
			);
			wp_add_inline_script( 'col-scripts', 'var clbxScriptData = ' . wp_json_encode( $clbx_script_data ) . ';', 'before' );

			wp_enqueue_style( 'jquery-ui-dialog-min-css', includes_url().'css/jquery-ui-dialog.min.css' );
			wp_enqueue_style( 'col-style', plugins_url( 'css/col.css', CLBX_PLUGIN_FILE ), array(), $clbx_version, 'all');
		}
	}
	add_action( 'wp_enqueue_scripts', 'clrbx_styles' );
}

if ( ! function_exists( 'clrbx_button_html' ) ){
	function clrbx_button_html() {
		global $clbx_order_text;
		$order_button_text = $clbx_order_text;
		return '<input type="button" onclick="javascript:confirm_order();" class="button alt" name="woocommerce_checkout_place_order" id="place_order2" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '"><button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" style="display:none">';
	}
	add_filter( 'woocommerce_order_button_html', 'clrbx_button_html' );
}

if ( ! function_exists( 'add_clrbx_action' ) ){
	function add_clrbx_action() {
		global $clbx_dialog_title;
		global $clbx_dialog_text;
		// 設定画面由来の値を出力する際は必ずエスケープする（属性値はesc_attr、本文はesc_html）
		$clbx_dialog_title_esc = esc_attr( $clbx_dialog_title );
		$clbx_dialog_text_esc = esc_html( $clbx_dialog_text );
		$html = <<<EOF
		<div style="display: none;">
		<section id="clrbx" title="{$clbx_dialog_title_esc}">
		<p>{$clbx_dialog_text_esc}</p>
		<table id="clbx_dialog_table"><tbody>
		</tbody></table>
		<ul>
		<li><input id="yes" type="button" onclick="javascript:order_submit();" value="はい"></li>
		<li><input id="no" type="button" onclick="javascript:confirm_close();"value="いいえ"</li>
		</ul>
		</section>
		</div>
		EOF;
		global $clbx_checkout_page;
		if( is_page( $clbx_checkout_page ) ) {
			echo $html;
		}
	}
	add_action( 'wp_footer', 'add_clrbx_action' );
}

/*-------------------------------------------*/
/*  プラグインのアップデートチェック
/*-------------------------------------------*/
require 'inc/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/etbsjp/woo-checkout-colorbox/',
	__FILE__,
	'woo-checkout-colorbox'
);
$myUpdateChecker->setBranch( 'dist' );

?>