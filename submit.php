<?php

$source = $_GET['source'];
$title = $_GET['title'];
$url = $_GET['url'];
$shorturl = $_GET['shorturl'];

$bitly_key = "xxxx";

function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1')
{
  //create the URL
  $bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;
  
  //get the url
  //could also use cURL here
  $response = file_get_contents($bitly);
  
  //parse depending on desired format
  if(strtolower($format) == 'json')
  {
    $json = @json_decode($response,true);
    return $json['results'][$url]['shortUrl'];
  }
  else //xml
  {
    $xml = simplexml_load_string($response);
    return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
  }
}

$short = make_bitly_url($url,'xxxx',$bitly_key,'json');


require_once(__DIR__ . '/amqp.inc');
include(__DIR__ . '/config.php');

$exchange = 'tweet_queue';
$queue = 'tweets';

$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

/*
    The following code is the same both in the consumer and the producer.
    In this way we are sure we always have a queue to consume from and an
        exchange where to publish messages.
*/

/*
    name: $queue
    passive: false
    durable: true // the queue will survive server restarts
    exclusive: false // the queue can be accessed in other channels
    auto_delete: false //the queue won't be deleted once the channel is closed.
*/
$ch->queue_declare($queue, false, true, false, false);

/*
    name: $exchange
    type: direct
    passive: false
    durable: true // the exchange will survive server restarts
    auto_delete: false //the exchange won't be deleted once the channel is closed.
*/

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
