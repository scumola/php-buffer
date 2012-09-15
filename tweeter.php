#!/usr/bin/php
<?php
require_once(__DIR__ . '/amqp.inc');
include(__DIR__ . '/config.php');
$exchange = 'tweet_queue';
$queue = 'tweets';
$consumer_tag = 'consumer';
$response="";
$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', false, true, false);
$ch->queue_bind($queue, $exchange);

function post_tweet($tweet_text) {

	global $response;
	require_once('tmhOAuth.php');
	$consumer_key = "xxxx";
	$consumer_secret = "xxxx";

	$connection = new tmhOAuth(array(
		'consumer_key' => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'user_token' => "xxxx",
		'user_secret' => "xxxx",
	)); 

	$connection->request('POST', $connection->url('1.1/statuses/update'), array('status' => $tweet_text));
	$response = $connection->response['response'];
	print ("Response: $response\n");
	return $connection->response['code'];
}

function process_message($msg) {
	global $response;

	$tweet = $msg->body;
	echo "$tweet\n";
	print "Posting...\n";
	$result = post_tweet($tweet);
#	print "Response code: " . $result . "\n";

	if ($result == 200) {
		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
	} else {
#		mail('user@host.com', '[auto-tweeter FAILED] $tweet', "$result - $response");	
		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
	}

	$hour = date('H');
	if ($hour > 21) {
		sleep (60 * 60 * 12); # 12 hours
	} else {
		sleep (60 * 60); # 1 hour
	}
}

$ch->basic_consume($queue, $consumer_tag, false, false, false, false, 'process_message');

function shutdown($ch, $conn){
    $ch->close();
    $conn->close();
}

register_shutdown_function('shutdown', $ch, $conn);
while(count($ch->callbacks)) {
    $ch->wait();
}
?>
