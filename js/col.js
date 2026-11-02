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

	var filters = {};

	/**
	 * サイト専用プラグイン向けの差し込み口。確認ダイアログの表示行の組み立てを applyFilters() 経由にしている。
	 * 名前と引数は公開 API なので変えないこと。
	 *   fieldRowHtml(html, ctx)  項目1行。ctx = { label, selectorId, value, escapedLabel, escapedValue }
	 *   rowsAfterItems(html)     商品明細の直後に足す行
	 *   rowsAfterTotal(html)     合計金額の直後に足す行
	 */
	window.clbx = window.clbx || {};

	/**
	 * HTML に連結する文字列をエスケープする
	 *
	 * @param {string} text エスケープ前の文字列。
	 * @return {string} エスケープ後の文字列。
	 */
	window.clbx.escapeHtml = function ( text ) {
		return $( '<div>' ).text( text ).html();
	};

	/**
	 * フィルターを登録する
	 *
	 * @param {string}   name 差し込み口の名前。
	 * @param {Function} fn   (value, ctx) を受け取り、文字列を返す関数。
	 */
	window.clbx.addFilter = function ( name, fn ) {
		( filters[ name ] = filters[ name ] || [] ).push( fn );
	};

	/**
	 * 登録済みのフィルターを順に適用する。例外や文字列以外の戻り値は無視し、確認ダイアログを止めない
	 *
	 * @param {string} name  差し込み口の名前。
	 * @param {string} value 初期値（HTML）。
	 * @param {Object} ctx   差し込み口ごとの文脈。
	 * @return {string} 適用後の HTML。
	 */
	window.clbx.applyFilters = function ( name, value, ctx ) {
		var list = filters[ name ] || [];
		for ( var j = 0; j < list.length; j++ ) {
			try {
				var next = list[ j ]( value, ctx );
				if ( typeof next === 'string' ) {
					value = next;
				}
			} catch ( e ) {
				if ( window.console ) {
					window.console.error( 'clbx filter error: ' + name, e );
				}
			}
		}
		return value;
	};

	/**
	 * jQuery のセレクタに連結してよい id か判定する
	 *
	 * @param {*} id 入力欄の id。
	 * @return {boolean} 英数字・ハイフン・アンダースコアのみの文字列なら true。
	 */
	function isValidId( id ) {
		return typeof id === 'string' && /^[A-Za-z0-9_-]+$/.test( id );
	}

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

		// 表は開くときに作る。ページを開いた時点であると、テーマに写された旧版のコード
		// （.ppc-button-wrapper を表の後ろへ移す）が PayPal のボタンを隠れたダイアログへ移してしまう。
		if ( ! $( '#clbx_dialog_table' ).length ) {
			$( '#clrbx p' ).first().after( '<table id="clbx_dialog_table"><tbody></tbody></table>' );
		}
		$( '#clbx_dialog_table tbody tr' ).remove();

		// 実行時に空かどうか判定する項目（未入力があれば確認ダイアログを開かずそのまま送信する）
		for ( i = 0; i < inputareaChk.length; i++ ) {
			if ( ! isValidId( inputareaChk[ i ] ) ) {
				continue;
			}
			chk = $( '#' + inputareaChk[ i ] ).val();
			if ( ! chk ) {
				document.getElementById( 'place_order' ).click();
				return;
			}
		}

		// ダイアログに表示する項目（姓・名・メールアドレスなど）
		$.each( displayArea, function ( label, selectorId ) {
			if ( ! isValidId( selectorId ) ) {
				return;
			}
			chk = $( '#' + selectorId ).val();
			if ( chk ) {
				var escapedLabel = window.clbx.escapeHtml( label );
				var escapedValue = window.clbx.escapeHtml( chk );
				var rowHtml = '<tr><th>' + escapedLabel + '</th><td>' + escapedValue + '</td></tr>';
				rowHtml = window.clbx.applyFilters( 'fieldRowHtml', rowHtml, {
					label: label,
					selectorId: selectorId,
					value: chk,
					escapedLabel: escapedLabel,
					escapedValue: escapedValue
				} );
				$( '#clbx_dialog_table tbody' ).append( rowHtml );
			}
		} );

		// 商品明細
		chk = $( 'table.woocommerce-checkout-review-order-table tbody' ).html();
		$( '#clbx_dialog_table tbody' ).append( '<tr><th colspan="2">商品</th></tr>' + chk );

		$( '#clbx_dialog_table tbody' ).append( window.clbx.applyFilters( 'rowsAfterItems', '' ) );

		// 合計金額
		chk = $( '.order-total td' ).html();
		$( '#clbx_dialog_table tbody' ).append( '<tr><th>合計金額</th><td><span class="woocommerce-Price-amount">' + chk + '</span></td></tr>' );
		$( '#clbx_dialog_table tbody' ).append( window.clbx.applyFilters( 'rowsAfterTotal', '' ) );

		// テーマに写された旧版のコードが PayPal 選択時に「はい」を隠していても、押せるように戻す
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
