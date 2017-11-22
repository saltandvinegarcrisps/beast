<?php

namespace Beast\Framework\Http;

use Psr\Http\Message\ResponseInterface;

class ServerResponse
{
    public function emit(ResponseInterface $response)
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers have already been sent');
        }

        header(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        echo $response->getBody();
    }
}
