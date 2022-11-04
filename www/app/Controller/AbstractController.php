<?php
declare(strict_types=1);

namespace App\Controller;

use App\Base\Request;
use App\Base\Response;
use App\Exception\RuntimeException;
use App\Model\UserModel;
use App\Storage\UserStorage;

abstract class AbstractController
{
    protected Request $request;
    protected Response $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param int $userId
     * @return UserModel
     * @throws RuntimeException
     */
    protected function getUserModelById(int $userId): UserModel
    {
        return $this->getUserStorage()->get($userId);
    }

    /**
     * @return UserStorage
     */
    protected function getUserStorage(): UserStorage
    {
        return new UserStorage();
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function responseSuccess(array $data = []): Response
    {
        return $this->response
            ->setStatus(Response::STATUS_SUCCESS)
            ->setData($data);
    }
}