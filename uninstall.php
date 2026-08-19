<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function clbx_woochksetting_uninstall() {
	// アンインストール時にデータは消さない方針のため、ここでは何もしない
}

clbx_woochksetting_uninstall();

?>