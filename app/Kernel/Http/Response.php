<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Http;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response
{
    public const OK = 0;

    protected ResponseInterface $response;

    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(ResponseInterface::class);
    }

    /**
     * @param $extra = [
     *     'options' => []
     * ]
     */
    public function success(mixed $data = [], array $extra = []): PsrResponseInterface
    {
        return $this->response->json(array_merge(
            [
                'ecode' => 0,
                'data' => $data,
            ],
            $extra
        ));
    }

    public function fail(int $code, string $message = ''): PsrResponseInterface
    {
        return $this->response->json([
            'ecode' => $code,
            'message' => $message,
        ]);
    }

    public function image(string $pathname): PsrResponseInterface
    {
        // header("Content-type: application/octet-stream");
        // header("Accept-Ranges: bytes");
        // header("Accept-Length:" . filesize($filename));
        // header("Content-Disposition: attachment; filename=" . $displayname);
        return $this->response()->withBody(new SwooleFileStream($pathname));
    }

    public function redirect($url, int $status = 302): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Location', (string) $url)
            ->withStatus($status);
    }

    public function cookie(Cookie $cookie)
    {
        $response = $this->response()->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Server', 'Hyperf')
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function response(): PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }
}
