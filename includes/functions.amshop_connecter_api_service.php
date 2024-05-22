<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'AMSHOP_CONNECTER_VERSION' ) ) {
	exit; // Exit if accessed directly
}

global $amshop_connecter_api_helper_version;

$amshop_connecter_api_helper_version = '0.0.1';
if ( !function_exists( 'amshop_connecter_api_success_send' ) ) {
	function amshop_connecter_api_success_send( $Order_Amount, $Commission_Amount, $ORDER_ID, $RID, $Click_ID, $DateTime ) {
		$url_content = 'https://api.hasoffers.com/Api?Format=json&Target=Conversion&Method=create&Service=HasOffers&Version=2&NetworkId=marktamerica&NetworkToken=NETPYKNAYOswzsboApxaL6GPQRiY2s&data[offer_id]='.AMSHOP_OFFER_ID.'&data[advertiser_id]='.AMSHOP_ADVERTISER_ID.'&data[sale_amount]='.$Order_Amount.'&data[affiliate_id]=12&data[payout]='.$Commission_Amount.'&data[revenue]='.$Commission_Amount.'&data[advertiser_info]='.$ORDER_ID.'&data[affiliate_info1]='.$RID.'&data[ad_id]='.$Click_ID.'&data[session_datetime]='.$DateTime;
		amshop_connecter_debug_message('COMPLETED : $sent :'.$url_content );
		$response = file_get_contents($url_content);
		amshop_connecter_debug_message('COMPLETED : $response :'.$response );
	}
}


if ( !function_exists( 'amshop_connecter_api_cancel_send' ) ) {
	function amshop_connecter_api_cancel_send( $Refund_Amount, $Commission_Amount, $ORDER_ID, $RID, $Click_ID, $DateTime ) {
		$url_content = 'https://api.hasoffers.com/Api?Format=json&Target=Conversion&Method=create&Service=HasOffers&Version=2&NetworkId=marktamerica&NetworkToken=NETPYKNAYOswzsboApxaL6GPQRiY2s&data[offer_id]='.AMSHOP_OFFER_ID.'&data[advertiser_id]='.AMSHOP_ADVERTISER_ID.'&data[sale_amount]=-'.$Refund_Amount.'&data[affiliate_id]=12&data[payout]=-'.$Commission_Amount.'&data[revenue]=-'.$Commission_Amount.'&data[advertiser_info]='.$ORDER_ID.'&data[affiliate_info1]='.$RID.'&data[ad_id]='.$Click_ID.'&data[is_adjustment]=1&data[session_datetime]='.$DateTime;
		amshop_connecter_debug_message('CANCELLED : sent :'.$url_content );
		$response = file_get_contents($url_content);
		amshop_connecter_debug_message('CANCELLED : $response :'.$response );
	}
}

