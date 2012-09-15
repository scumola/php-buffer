<?php
require_once(__DIR__ . '/amqp.inc');
include(__DIR__ . '/config.php');
$exchange = 'tweet_queue';
$queue = 'tweets';
$source = $_GET['source'];
$title = $_GET['title'];
$url = $_GET['url'];
$shorturl = $_GET['shorturl'];
$bitly_key = "xxxx";

function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1')
{
  $bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;
  
  $response = file_get_contents($bitly);
  if(strtolower($format) == 'json') {
    $json = @json_decode($response,true);
    return $json['results'][$url]['shortUrl'];
  } else {
    $xml = simplexml_load_string($response);
    return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
  }
}

$short = make_bitly_url($url,'xxxx',$bitly_key,'json');
$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();
$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', false, true, false);
$ch->queue_bind($queue, $exchange);
$msg_body = "$title $short";
$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
$ch->basic_publish($msg, $exchange);
$ch->close();
$conn->close();
?>
Done.
<script language="javascript"> 
<!-- 
setTimeout("self.close();",0) 
//--> 
</script>
