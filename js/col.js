/**
 * WooCommerce Checkout Colorbox
 * 注文確認ダイアログの表示・入力チェックを行うスクリプト。
 *
 * 設定値（必須チェック対象のセレクタ、ダイアログに表示する項目）は
 * wp_add_inline_script() 経由で埋め込まれる window.clbxScriptData（JSON）から読み込む。
 * これにより設定値がセレクタ文字列の外に出て任意のJSを書けてしまう経路を塞いでいる。
 */
( function ( $ ) {
	'use strict';

	var wd = wdtchk();

	$( window ).resize( function () {
		wd = wdtchk();
	} );

	/**
	 * 画面幅に応じたダイアログ幅を計算する
	 *
	 * @return {number} ダイアログ幅（px）
	 */
	function wdtchk() {
		var wdt = $( window ).width();
		if ( wdt > 660 ) {
			wdt = 620;
		} else {
			wdt = wdt - 40;
		}
		return wdt;
	}

	/**
	 * 注文確認ダイアログを表示する。
	 * 必須チェック対象（clbxScriptData.inputareaChk）に未入力があれば
	 * ダイアログを開かずそのまま注文ボタンをクリックする。
	 */
	window.confirm_order = function () {
		var data = ( typeof window.clbxScriptData !== 'undefined' ) ? window.clbxScriptData : {};
		var inputareaChk = data.inputareaChk || [];
		var displayArea = data.displayArea || {};
		var chk;
		var i;

		// 表は開くときに作る。ページを開いた時点であると、サイト側に残る旧コードが
		// PayPal のボタンの置き場所を隠れたダイアログへ移してしまう。
		if ( ! $( '#clbx_dialog_table' ).length ) {
			$( '#clrbx p' ).first().after( '<table id="clbx_dialog_table"><tbody></tbody></table>' );
		}
		$( '#clbx_dialog_table tbody tr' ).remove();

		// 実行時に空かどうか判定する項目（未入力があれば確認ダイアログを開かずそのまま送信する）
		for ( i = 0; i < inputareaChk.length; i++ ) {
			chk = $( '#' + inputareaChk[ i ] ).val();
			if ( ! chk ) {
				document.getElementById( 'place_order' ).click();
				return;
			}
		}

		// ダイアログに表示する項目（姓・名・メールアドレスなど）
		$.each( displayArea, function ( label, selectorId ) {
			chk = $( '#' + selectorId ).val();
			if ( chk ) {
				$( '#clbx_dialog_table tbody' ).append( '<tr><th>' + label + '</th><td>' + chk + '</td></tr>' );
			}
		} );

		// 商品明細
		chk = $( 'table.woocommerce-checkout-review-order-table tbody' ).html();
		$( '#clbx_dialog_table tbody' ).append( '<tr><th colspan="2">商品</th></tr>' + chk );

		// 合計金額
		chk = $( '.order-total td' ).html();
		$( '#clbx_dialog_table tbody' ).append( '<tr><th>合計金額</th><td><span class="woocommerce-Price-amount">' + chk + '</span></td></tr>' );

		// サイト側に残る旧コードが「はい」を隠していても、ここへ来る支払い方法では必ず押せるようにする
		$( '#yes' ).css( 'display', 'inline-block' );

		$( '#clrbx' ).dialog( {
			width: wd,
			modal: true
		} );
	};

	/**
	 * 「はい」押下時：実際の注文ボタンをクリックしてダイアログを閉じる
	 */
	window.order_submit = function () {
		document.getElementById( 'place_order' ).click();
		$( '#clrbx' ).dialog( 'close' );
	};

	/**
	 * 「いいえ」押下時：ダイアログを閉じるのみ
	 */
	window.confirm_close = function () {
		$( '#clrbx' ).dialog( 'close' );
	};

	$( document ).on( 'click', '.ui-widget-overlay', function () {
		$( this ).prev().find( '.ui-dialog-content' ).dialog( 'close' );
	} );

	$( function () {
		// フォーム内でのEnterキーによる誤送信を防止する
		$( 'input' ).on( 'keydown', function ( e ) {
			if ( ( e.which && e.which === 13 ) || ( e.keyCode && e.keyCode === 13 ) ) {
				return false;
			}
			return true;
		} );
	} );

	/**
	 * 選択中の支払い方法が自分のボタンを持つか判定する
	 *
	 * @return {boolean} 自分のボタンを持つ支払い方法なら true
	 */
	function has_own_button() {
		var data = ( typeof window.clbxScriptData !== 'undefined' ) ? window.clbxScriptData : {};
		var prefixes = data.ownButtonGatewayPrefixes || [];
		var method = $( 'input[name=payment_method]:checked' ).val() || '';
		var i;

		for ( i = 0; i < prefixes.length; i++ ) {
			if ( method.indexOf( prefixes[ i ] ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 支払い方法に合わせて、差し替えボタンと本物のボタンのどちらを見せるかを切り替える。
	 * 支払い欄は updated_checkout で作り直されるので、残り続ける form の class で持つ。
	 */
	function sync_order_button() {
		$( 'form.checkout' ).toggleClass( 'clbx-own-button', has_own_button() );
	}

	$( document.body ).on( 'payment_method_selected updated_checkout', sync_order_button );
	sync_order_button();

}( jQuery ) );
