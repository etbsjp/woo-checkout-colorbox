<?php
/**
 * アンインストール処理。
 *
 * Woo Checkout Colorbox はオプション `woochksetting` に設定値を保存している
 * （`tools/setting.php:131`）。これは利用者が設定した値なので、削除時には消さない
 * （task-queue #108 の案A決定）。プラグインを入れ直したときに設定が残っているほうが、
 * 消えているより害が小さい。
 *
 * 独自テーブルも cron も持たないため、案Aに従うと「何もしない」が正しい実装になる。
 * 空関数ではなくこの docblock を残しているのは、「まだ書いていない」と読まれないようにするため。
 *
 * @package woo-checkout-colorbox
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// 意図的に何もしない。
