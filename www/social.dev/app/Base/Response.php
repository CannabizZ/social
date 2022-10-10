<?php
declare(strict_types=1);

namespace App\Base;

class Response
{
    public const STATUS_ERROR = 'error';
    public const STATUS_SUCCESS = 'success';

    protected string $status = self::STATUS_ERROR;

    protected array $data = [];

    protected string|null $message = null;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function __toString(): string
    {
        return json_encode([
            'status' => $this->status,
            'data' => $this->data,
            'message' => $this->message
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
}