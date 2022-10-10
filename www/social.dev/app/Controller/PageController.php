<?php
declare(strict_types=1);

namespace App\Controller;

use App\Base\Response;
use App\Exception\RuntimeException;
use App\Exception\ValidationException;
use App\Storage\PageStorage;
use Throwable;

class PageController extends AbstractController
{
    /**
     * @param int $pageId
     * @return Response
     * @throws RuntimeException
     */
    public function get(int $pageId): Response
    {
        $storage = new PageStorage();
        $pageModel = $storage->get($pageId);
        return $this->responseSuccess()->setData([
            'id' => $pageModel->getId(),
            'userId' =>  $pageModel->getUserId(),
            'content' => $pageModel->getContent()
        ]);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     * @throws Throwable
     * @throws ValidationException
     */
    public function create(int $userId): Response
    {
        $userModel = $this->getUserModelById($userId);

        $content = $this->request->getBody()['content'] ?? null;
        if (empty($content)) {
            throw new ValidationException('Empty content');
        }

        $storage = new PageStorage();
        $pageId = $storage->create($userModel->getId(), $content);

        return $this->responseSuccess(['pageId' => $pageId]);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function getByUser(int $userId): Response
    {
        $userModel = $this->getUserModelById($userId);

        $storage = new PageStorage();
        $pageModels = $storage->getPagesByUser($userModel->getId());

        $pages = [];
        foreach ($pageModels as $pageModel) {
            $pages[] = [
                'id' => $pageModel->getId(),
                'content' => $pageModel->getContent()
            ];
        }

        return $this->responseSuccess()->setData($pages);
    }
}