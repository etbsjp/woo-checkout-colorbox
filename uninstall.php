<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function etbs_woochksetting_uninstall() {
	delete_option('woochksetting');
}

etbs_woochksetting_uninstall();

?>