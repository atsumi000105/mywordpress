<?php
namespace Aws\S3;
use Aws\Api\Parser\AbstractParser;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Psr\Http\Message\ResponseInterface;
class AmbiguousSuccessParser extends AbstractParser
{
    private static $ambiguousSuccesses = [
        'UploadPartCopy' => true,
        'CopyObject' => true,
        'CompleteMultipartUpload' => true,
    ];
    private $parser;
    private $errorParser;
    private $exceptionClass;
    public function __construct(
        callable $parser,
        callable $errorParser,
        $exceptionClass = AwsException::class
    ) {
        $this->parser = $parser;
        $this->errorParser = $errorParser;
        $this->exceptionClass = $exceptionClass;
    }
    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        if (200 === $response->getStatusCode()
            && isset(self::$ambiguousSuccesses[$command->getName()])
        ) {
            $errorParser = $this->errorParser;
            $parsed = $errorParser($response);
            if (isset($parsed['code']) && isset($parsed['message'])) {
                throw new $this->exceptionClass(
                    $parsed['message'],
                    $command,
                    ['connection_error' => true]
                );
            }
        }
        $fn = $this->parser;
        return $fn($command, $response);
    }
}
