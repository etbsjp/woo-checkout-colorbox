# WooCommerce Checkout Colorbox

WooCommerce 注文確定ページに確認用ダイアログ表示します。

## できる事

WooCommerce 注文確定ページに確認用ダイアログ表示する簡易プラグインです。ダイアログのカスタマイズなども個別にお受けしております。


カスタマイズなど、個別に承っております
気軽にご連絡ください
https://etbs.jp/contact/

## カスタマイズ用の差し込み口（フィルター）

サイトごとの項目・案内文・表示行は、このプラグインを書き換えず、別のプラグインから差し込めます。名前と引数は公開 API なので変更しません。対応の有無は定数 `CLBX_API_VERSION` で判定できます。

PHP

| フィルター | 内容 |
|---|---|
| `clbx_display_area` | 確認ダイアログに表示する項目（表示名 => 入力欄の id）を差し替える |
| `clbx_dialog_footer_html` | 表のあと・「はい」「いいえ」の前に差し込む HTML（`wp_kses_post` を通る） |
| `clbx_script_data` | JS へ渡す設定値（`inputareaChk` / `displayArea` など）を差し替える |
| `clbx_own_button_gateway_prefixes` | 自分のボタンを持つ支払い方法（確認ダイアログを出さない）の id の前方一致を差し替える |

JavaScript（`window.clbx.addFilter( name, fn )`。`fn( html, ctx )` は文字列を返す。値は `window.clbx.escapeHtml()` で包む。要素の中身用で、属性値には使えない）

| 名前 | 内容 |
|---|---|
| `fieldRowHtml` | 項目 1 行の HTML。`ctx` は `label` / `selectorId` / `value` / `escapedLabel` / `escapedValue` |
| `rowsAfterItems` | 商品明細の直後に足す行 |
| `rowsAfterTotal` | 合計金額の直後に足す行 |
