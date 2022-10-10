<?php
declare(strict_types=1);

namespace App\Model;

class FriendsModel extends AbstractModel
{
    protected int $userId;
    protected array $friendIds;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return FriendsModel
     */
    public function setUserId(int $userId): FriendsModel
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getFriendIds(): array
    {
        return $this->friendIds;
    }

    /**
     * @param int[] $friendIds
     * @return FriendsModel
     */
    public function setFriendIds(array $friendIds): FriendsModel
    {
        $this->friendIds = $friendIds;
        return $this;
    }
}