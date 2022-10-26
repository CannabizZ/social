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

        $userId = (new UserStorage())->create($userModel);
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
        $userModel = $this->getUserModelById($userId);

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
        $userModel = $this->getUserModelById($userId);
        $friendsModel = (new FriendStorage())->getByUserId($userModel->getId());

        $friends = (new UserStorage())->getByIds($friendsModel->getFriendIds());

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
        $userModel = $this->getUserModelById($userId);
        $friendsModel = (new FriendStorage())->getByUserId($userModel->getId());
        if (in_array($friendId, $friendsModel->getFriendIds())) {
            throw new ValidationException(sprintf('User #%s already a friend of user #%s', $userId, $friendId));
        }

        $friendUserModel = $this->getUserModelById($friendId);

        (new FriendStorage())->add($userModel, $friendUserModel);

        return $this->responseSuccess();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function seed(): Response
    {
        //(new UserStorage())->seed();
        return $this->responseSuccess();
    }
}