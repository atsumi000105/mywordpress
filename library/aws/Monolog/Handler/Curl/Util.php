<?php
namespace Monolog\Handler\Curl;
class Util
{
    private static $retriableErrorCodes = array(
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    );
    public static function execute($ch, $retries = 5, $closeAfterDone = true)
    {
        while ($retries--) {
            if (curl_exec($ch) === false) {
                $curlErrno = curl_errno($ch);
                if (false === in_array($curlErrno, self::$retriableErrorCodes, true) || !$retries) {
                    $curlError = curl_error($ch);
                    if ($closeAfterDone) {
                        curl_close($ch);
                    }
                    throw new \RuntimeException(sprintf('Curl error (code %s): %s', $curlErrno, $curlError));
                }
                continue;
            }
            if ($closeAfterDone) {
                curl_close($ch);
            }
            break;
        }
    }
}
