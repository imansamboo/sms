<?php
require('Twilio/autoload.php');
use Twilio\Rest\Client;
function SMS_user_Twilio($AccountSid, $AuthToken, $to, $from, $text) {
	$client = new Client($AccountSid, $AuthToken);
    try {
		$client->messages->create(
			$to,
			array(
				'from' => $from,
				'body' => $text,
			)
		);	
		$output = 1;
    } catch (Exception $e) {
        return $e;
    }
    return $output;
}

