<?php

function sendPush($arrayOfDeviceId,$title,$msg){
// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyCVV46WzLcjnhdhmLqp2EFcWQRQwqEIGOM' );


$registrationIds = $arrayOfDeviceId;

// prep the bundle
$msg = array
(
    'message'       => $msg.$title,
    'title'         => 'WorkingAlert!!',
    'subtitle'      => 'This is a subtitle. subtitle',
    'tickerText'    => 'Ticker text here...Ticker text here...Ticker text here',
    'vibrate'   => true,
    'sound'     => true,
    'image' 	=> 'www/img/icon.png',
    'style'		=> "inbox",
    'summaryText' => "There are %n% notifications",
    'soundname' 	=> 'alert.mp3'
    
);

$fields = array
(
    'registration_ids'  => $registrationIds,
    'data'              => $msg
);

$headers = array
(
    'Authorization: key=' . API_ACCESS_KEY,
    'Content-Type: application/json'
);

$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );

return $result;
}
?>