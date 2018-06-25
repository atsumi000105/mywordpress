<?php
namespace Aws\S3;
use Aws\Api\Parser\AbstractParser;
use Aws\Api\Parser\Exception\ParserException;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Psr\Http\Message\ResponseInterface;
class RetryableMalformedResponseParser extends AbstractParser
{
    private $parser;
    private $exceptionClass;
    public function __construct(
        callable $parser,
        $exceptionClass = AwsException::class
    ) {
        $this->parser = $parser;
        $this->exceptionClass = $exceptionClass;
    }
    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $fn = $this->parser;
        try {
            return $fn($command, $response);
        } catch (ParserException $e) {
            throw new $this->exceptionClass(
                "Error parsing response for {$command->getName()}:"
                    . " AWS parsing error: {$e->getMessage()}",
                $command,
                ['connection_error' => true, 'exception' => $e],
                $e
            );
        }
    }
}
