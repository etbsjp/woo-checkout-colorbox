<?php
header('Content-Type: application/x-javascript; charset=utf-8');
require_once( dirname( __FILE__ , 5) . '/wp-load.php' );

$clbx_chk = '';
$clbx_displayarea = Woo_Chkbox_Settings::display_area();
foreach($clbx_displayarea as $key => $value){
	$clbx_chk .= <<< EOF

	chk = jQuery("#{$value}").val();
	if(chk){
		jQuery("#clbx_dialog_table tbody").append("<tr><th>{$key}</th><td>" + chk + "</td></tr>");
	} 

	EOF;
}
$clbx_chk .= 'chk = jQuery("table.woocommerce-checkout-review-order-table tbody").html();' . "\n";
$clbx_chk .= 'jQuery("#clbx_dialog_table tbody").append("<tr><th colspan=\"2\">商品</th></tr>" + chk + "");';
$clbx_chk .= 'chk = jQuery(".order-total td").html();' . "\n";
$clbx_chk .= 'jQuery("#clbx_dialog_table tbody").append("<tr><th>合計金額</th><td><span class=\"woocommerce-Price-amount\">" + chk + "</span></td></tr>");';

$option = get_option('woochksetting', Woo_Chkbox_Settings::options_default());
$clbx_scr = '';
$clbx_inputarea_chk = $option['clbx_inputarea_chk'];
foreach($clbx_inputarea_chk as $value){
	$clbx_scr .= <<< EOF

	chk = jQuery("#{$value}").val();
	if(!chk){
		document.getElementById("place_order").click();
		exit;
	} 

	EOF;
}

$script = <<< EOF

var wd = wdtchk();
jQuery(window).resize(function(){
	wd = wdtchk();
});

function wdtchk() {
	var wdt = jQuery(window).width();
	jQuery(window).width();
	if(wdt > 660) {
		wdt = 620;
	} else {
		wdt = wdt - 40;
	}
	return wdt;
}

function confirm_order() {
	jQuery("#clbx_dialog_table tbody tr").remove();
	var chk;
	{$clbx_scr}
	{$clbx_chk}

	jQuery("#clrbx").dialog({
		width: wd,
		modal: true
	});
}

function order_submit() {
	document.getElementById("place_order").click();
	jQuery("#clrbx").dialog("close");
}

function confirm_close() {
	jQuery("#clrbx").dialog("close");
}

jQuery( document ).on( "click", ".ui-widget-overlay", function(){
	jQuery(this).prev().find(".ui-dialog-content").dialog("close");
});

jQuery(function(){
	jQuery("input").on("keydown", function(e) {
		if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
			return false;
		} else {
			return true;
		}
	});
});

jQuery('div.ppc-button-wrapper').insertAfter('#clbx_dialog_table');

function paypal_yes_none() {
	var v = jQuery('input[name=payment_method]:checked').val();
	if (v == 'ppcp-gateway') {
		jQuery('input#yes').css('display', 'none');
	} else {
		jQuery('input#yes').css('display', 'inline-block');
	}
}

jQuery(function(){
	paypal_yes_none();
});

EOF;
echo $script;