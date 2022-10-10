<?php
declare(strict_types=1);

namespace App\Base;

use App\Exception\RuntimeException;

class Request
{
    private array $url;
    private array $body = [];
    private bool $bodyParsed = false;

    public function __construct()
    {
        $this->url = parse_url($_SERVER['REQUEST_URI']);
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public function query($key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $_GET;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->url['path'] ?? null;
    }

    /**
     * @return array
     * @throws RuntimeException
     */
    public function getBody(): array
    {
        if (!$this->bodyParsed) {
            $this->parseBody();
        }
        return $this->body;
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function parseBody(): void
    {
        if ($this->method() !== 'POST') {
            $this->bodyParsed = true;
        }
        $inputData = file_get_contents('php://input');
        if ($inputData === false) {
            $this->bodyParsed = true;
            return;
        }

        $data = json_decode($inputData, true);
        if (json_last_error()) {
            throw new RuntimeException('Json body parse error `' . json_last_error_msg() . '`');
        }

        $this->bodyParsed = true;
        $this->body = $data ?? [];
    }

}