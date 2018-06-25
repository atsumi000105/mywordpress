<?php
namespace Monolog\Handler;
use Monolog\Logger;
class IFTTTHandler extends AbstractProcessingHandler
{
    private $eventName;
    private $secretKey;
    public function __construct($eventName, $secretKey, $level = Logger::ERROR, $bubble = true)
    {
        $this->eventName = $eventName;
        $this->secretKey = $secretKey;
        parent::__construct($level, $bubble);
    }
    public function write(array $record)
    {
        $postData = array(
            "value1" => $record["channel"],
            "value2" => $record["level_name"],
            "value3" => $record["message"],
        );
        $postString = json_encode($postData);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https:
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));
        Curl\Util::execute($ch);
    }
}
