<?php
declare(strict_types=1);

namespace App\Controller;

use App\Base\Response;
use App\Exception\RuntimeException;
use App\Exception\ValidationException;
use App\Helper\ValidationHelper;
use App\Model\UserModel;
use App\Storage\FriendStorage;
use App\Storage\UserStorage;
use Throwable;

class UserController extends AbstractController
{
    /**
     * @return Response
     * @throws RuntimeException
     * @throws ValidationException
     * @throws Throwable
     */
    public function register(): Response
    {
        $userModel = ValidationHelper::validateUserRegistration($this->request);

        $storage = $this->getUserStorage();
        $userId = $storage->create($userModel);
        $userModel->setId($userId);

        return $this->responseSuccess([
            'id' => $userModel->getId(),
            'firstName' =>  $userModel->getFirstName(),
            'lastName' => $userModel->getLastName(),
            'years' => $userModel->getYears(),
            'sex' => $userModel->getSex(),
            'city' => $userModel->getCity(),
            'interests' => $userModel->getInterests(),
            'password' => $userModel->getPasswordHash()
        ]);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function get(int $userId): Response
    {
        $storage = $this->getUserStorage();
        $userModel = $storage->get($userId);

        return $this->responseSuccess([
            'id' => $userModel->getId(),
            'firstName' =>  $userModel->getFirstName(),
            'lastName' => $userModel->getLastName(),
            'years' => $userModel->getYears(),
            'sex' => $userModel->getSex(),
            'city' => $userModel->getCity(),
            'interests' => $userModel->getInterests()
        ]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function getRandom(): Response
    {
        $storage = $this->getUserStorage();
        $usersCount = $storage->count();
        $userId = rand(1, $usersCount-1);
        $userModel = $storage->get($userId);

        return $this->responseSuccess([
            'id' => $userModel->getId(),
            'firstName' =>  $userModel->getFirstName(),
            'lastName' => $userModel->getLastName(),
            'years' => $userModel->getYears(),
            'sex' => $userModel->getSex(),
            'city' => $userModel->getCity(),
            'interests' => $userModel->getInterests()
        ]);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function getFriends(int $userId): Response
    {
        $storage = $this->getUserStorage();
        $userModel = $storage->get($userId);
        $friendsModel = (new FriendStorage())->getByUserId($userModel->getId());

        $friends = $this->getUserStorage()->getByIds($friendsModel->getFriendIds());

        return $this->responseSuccess(array_values(array_map(function (UserModel $userModel) {
            return [
                'id' => $userModel->getId(),
                'firstName' =>  $userModel->getFirstName(),
                'lastName' => $userModel->getLastName(),
                'years' => $userModel->getYears(),
                'sex' => $userModel->getSex(),
                'city' => $userModel->getCity(),
                'interests' => $userModel->getInterests()
            ];
        }, $friends)));
    }

    /**
     * @param int $userId
     * @param int $friendId
     * @return Response
     * @throws RuntimeException
     */
    public function makeFriend(int $userId, int $friendId): Response
    {
        $storage = $this->getUserStorage();
        $userModel = $storage->get($userId);
        $friendsModel = (new FriendStorage())->getByUserId($userModel->getId());
        if (in_array($friendId, $friendsModel->getFriendIds())) {
            throw new ValidationException(sprintf('User #%s already a friend of user #%s', $userId, $friendId));
        }

        $friendUserModel = $storage->get($friendId);

        (new FriendStorage())->add($userModel, $friendUserModel);

        return $this->responseSuccess();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function search(): Response
    {
        $term = $this->request->query('term');
        if (empty($term)) {
            return $this->responseSuccess();
        }

        $term = $term . '%';

        $storage = $this->getUserStorage();
        $userIds = $storage->searchByNameAndLastName($term);
        $userModels = $storage->getByIds($userIds);

        return $this->responseSuccess(array_values(array_map(function (UserModel $userModel) {
            return [
                'id' => $userModel->getId(),
                'firstName' =>  $userModel->getFirstName(),
                'lastName' => $userModel->getLastName(),
                'years' => $userModel->getYears(),
                'sex' => $userModel->getSex(),
                'city' => $userModel->getCity(),
                'interests' => $userModel->getInterests()
            ];
        }, $userModels)));
    }
}