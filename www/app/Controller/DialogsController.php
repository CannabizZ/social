<?php
declare(strict_types=1);

namespace App\Controller;

use App\Base\Response;
use App\Exception\RuntimeException;
use App\Exception\ValidationException;
use App\Storage\DialogsStorage;
use Throwable;

class DialogsController extends AbstractController
{
    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     * @throws Throwable
     * @throws ValidationException
     */
    public function create(int $userId): Response
    {
        $recipientId = (int) ($this->request->getBody()['recipientId'] ?? 0);
        if (empty($recipientId)) {
            throw new ValidationException('Empty recipientId');
        }

        $message = $this->request->getBody()['message'] ?? null;
        if (empty($message)) {
            throw new ValidationException('Empty message');
        }

        $storage = new DialogsStorage();

        $storage->add($userId, $recipientId, $message);

        return $this->responseSuccess();
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function get(int $userId): Response
    {
        $recipientId = (int) ($this->request->getQuery()['recipientId'] ?? 0);
        if (empty($recipientId)) {
            throw new ValidationException('Empty recipientId');
        }

        $limit = (int) ($this->request->getQuery()['limit'] ?? 50);
        if ($limit > 500) {
            throw new ValidationException('Max limit - 500');
        }

        $offset = (int) ($this->request->getQuery()['offset'] ?? 0);

        $storage = new DialogsStorage();
        $messages = $storage->get($userId, $recipientId, $limit, $offset);

        return $this->responseSuccess($messages);
    }
}