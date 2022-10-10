<?php
declare(strict_types=1);

namespace App\Model;

class PageModel extends AbstractModel
{
    protected int $userId;
    protected string $content;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return PageModel
     */
    public function setUserId(int $userId): PageModel
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return PageModel
     */
    public function setContent(string $content): PageModel
    {
        $this->content = $content;
        return $this;
    }
}