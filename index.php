<?php
require __DIR__ . '/vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
 
// set false for production
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "zF23D8tznVoMC8ALhGJ/jkwJhgLUF4/tMF2ADfiwOEqb/dRvPzwLNrhjziWv1irXweT1xd1NwfBAyhhOU2x/qMOjKMidgJOtq21xh4tecmC/MWrxhjj1mli58xjSGKAYcgaccljjfLMlwrTQ4ABqmgdB04t89/1O/w1cDnyilFU=";
$channel_secret = "1e5fd4c4deeffca838ecb48a4ca76df2";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
 
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});
 
// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);
 
    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    // send same message as reply to user
                    // $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                    // $result = $bot->replyText($event['replyToken'], 'ini pesan balasan');
    
                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    $textMessageBuilder1 = new TextMessageBuilder('ini pesan balasan pertama');
                    $textMessageBuilder2 = new TextMessageBuilder('ini pesan balasan kedua');
                    $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
                    
                    $multiMessageBuilder = new MultiMessageBuilder();
                    $multiMessageBuilder->add($textMessageBuilder1);
                    $multiMessageBuilder->add($textMessageBuilder2);
                    $multiMessageBuilder->add($stickerMessageBuilder);
                    $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
    
                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
            }
        } 
    }
});
 
$app->run();