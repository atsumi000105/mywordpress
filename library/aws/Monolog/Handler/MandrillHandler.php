<?php
namespace Monolog\Handler;
use Monolog\Logger;
class MandrillHandler extends MailHandler
{
    protected $message;
    protected $apiKey;
    public function __construct($apiKey, $message, $level = Logger::ERROR, $bubble = true)
    {
        parent::__construct($level, $bubble);
        if (!$message instanceof \Swift_Message && is_callable($message)) {
            $message = call_user_func($message);
        }
        if (!$message instanceof \Swift_Message) {
            throw new \InvalidArgumentException('You must provide either a Swift_Message instance or a callable returning it');
        }
        $this->message = $message;
        $this->apiKey = $apiKey;
    }
    protected function send($content, array $records)
    {
        $message = clone $this->message;
        $message->setBody($content);
        $message->setDate(time());
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https:
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'key' => $this->apiKey,
            'raw_message' => (string) $message,
            'async' => false,
        )));
        Curl\Util::execute($ch);
    }
}
