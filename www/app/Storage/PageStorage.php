<?php
declare(strict_types=1);

namespace App\Storage;

use App\Exception\RuntimeException;
use App\Model\PageModel;
use PDO;
use Throwable;

class PageStorage extends AbstractStorage
{
    /**
     * @param int $id
     * @return PageModel
     * @throws RuntimeException
     */
    public function get(int $id): PageModel
    {
        $data = $this->query('
            SELECT 
                id,
                userId,
                content
            FROM 
                user_page           
            WHERE 
                id = ' . $id . '
            ')->fetch(PDO::FETCH_ASSOC);

        if (empty($data)) {
            throw new RuntimeException(sprintf('Page #%s not found', $id));
        }

        return (new PageModel())
            ->setId($data['id'])
            ->setUserId($data['userId'])
            ->setContent($data['content']);
    }

    /**
     * @param int $userId
     * @param string $content
     * @return int
     * @throws RuntimeException
     * @throws Throwable
     */
    public function create(int $userId, string $content): int
    {
        try {
            $this->db->beginTransaction();

            $statement = $this->db->prepare("
                INSERT INTO user_page 
                    (userId, content) 
                VALUES
                    (?,?)
            ");

            $statement->execute([
                $userId,
                $content
            ]);

            $pageId = (int) $this->db->lastInsertId();

            if (empty($pageId)) {
                throw new RuntimeException('Failed create page');
            }

            if (!$this->db->commit()) {
                throw new RuntimeException(
                    "PDO error code: " . $this->db->errorCode() .
                    "\n info: " . var_export($this->db->errorInfo(), true)
                );
            }

        } catch (Throwable $throwable) {
            $this->db->rollBack();
            throw $throwable;
        }

        return $pageId;
    }

    /**
     * @param int $userId
     * @return PageModel[]
     * @throws RuntimeException
     */
    public function getPagesByUser(int $userId): array
    {
        $pages = [];
        $pagesData = $this->query('
            SELECT 
                id,
                userId,
                content
            FROM 
                user_page           
            WHERE 
                userId = ' . $userId . '
            ')->fetchAll(PDO::FETCH_ASSOC);

        if (empty($pagesData)) {
            return $pages;
        }

        foreach ($pagesData as $pageData) {
            $pages[$pageData['id']] = (new PageModel())
                ->setId($pageData['id'])
                ->setUserId($pageData['userId'])
                ->setContent($pageData['content']);
        }

        return $pages;
    }
}