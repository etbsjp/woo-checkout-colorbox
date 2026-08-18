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
		$( '#clbx_dialog_table tbody tr' ).remove();

		var data = ( typeof window.clbxScriptData !== 'undefined' ) ? window.clbxScriptData : {};
		var inputareaChk = data.inputareaChk || [];
		var displayArea = data.displayArea || {};
		var chk;
		var i;

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

	$( 'div.ppc-button-wrapper' ).insertAfter( '#clbx_dialog_table' );

	/**
	 * PayPal決済が選択されている場合は「はい」ボタンを非表示にする
	 * （PayPalの決済ボタン経由で注文が完了するため）
	 */
	function paypal_yes_none() {
		var v = $( 'input[name=payment_method]:checked' ).val();
		if ( v === 'ppcp-gateway' ) {
			$( 'input#yes' ).css( 'display', 'none' );
		} else {
			$( 'input#yes' ).css( 'display', 'inline-block' );
		}
	}

	$( function () {
		paypal_yes_none();
	} );

}( jQuery ) );
