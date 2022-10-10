<?php
declare(strict_types=1);

namespace App\Storage;

use App\Exception\RuntimeException;
use App\Model\FriendsModel;
use App\Model\UserModel;
use PDO;

class FriendStorage extends AbstractStorage
{
    /**
     * @param int $userId
     * @return FriendsModel
     * @throws RuntimeException
     */
    public function getByUserId(int $userId): FriendsModel
    {
        $data = $this->query('
            SELECT DISTINCT 
                friendUserId
            FROM 
                friends             
            WHERE 
                userId = ' . $userId . '
            ')->fetchAll(PDO::FETCH_ASSOC);

        return (new FriendsModel())
            ->setUserId($userId)
            ->setFriendIds(!empty($data) ? array_unique(array_column($data, 'friendUserId')) : []);
    }

    /**
     * @param UserModel $userModel
     * @param UserModel $friendUserModel
     * @return void
     * @throws RuntimeException
     */
    public function add(UserModel $userModel, UserModel $friendUserModel): void
    {
        $this->query("
                INSERT IGNORE INTO friends 
                    (userId,friendUserId) 
                VALUES
                    (
                        " . $userModel->getId() . ",
                        " . $friendUserModel->getId() . "
                    ),
                    (
                        " . $friendUserModel->getId() . ",
                        " . $userModel->getId() . "
                    )
            ");
    }
}