<?php
declare(strict_types=1);

namespace App\Controller;

use App\Base\Response;
use App\Exception\RuntimeException;
use App\Storage\MigrateStorage;

class MigrateController extends AbstractController
{
    /**
     * @return Response
     * @throws RuntimeException
     */
    public function migrate(): Response
    {
        (new MigrateStorage())->migrate();
        return $this->responseSuccess();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function seedUsers(): Response
    {
        (new MigrateStorage())->seedUsers();
        return $this->responseSuccess();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function seedInterests(): Response
    {
        (new MigrateStorage())->seedInterests();
        return $this->responseSuccess();
    }
}