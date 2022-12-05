<?php
declare(strict_types=1);

namespace App\Storage;

use App\Exception\RuntimeException;
use App\Model\UserModel;
use PDO;
use Throwable;

class UserStorage extends AbstractStorage
{
    public const TABLE = 'user';

    /**
     * @param int $id
     * @return UserModel
     * @throws RuntimeException
     */
    public function get(int $id): UserModel
    {
        $userModel = $this->getByIds([$id])[$id] ?? null;
        if ($userModel === null) {
            throw new RuntimeException(sprintf('User #%s not found', $id));
        }

        return $userModel;
    }

    /**
     * @param int[] $userIds
     * @return UserModel[]
     * @throws RuntimeException
     */
    public function getByIds(array $userIds): array
    {
        $userModels = [];
        if (empty($userIds)) {
            return $userModels;
        }

        $items = $this->query('
            SELECT 
                u.id, 
                u.firstName,
                u.lastName,
                u.years,
                u.sex,
                u.city,
                u.password,
                JSON_OBJECTAGG(IFNULL(i.id, \'\'), i.`name`) AS interests
            FROM 
                ' . self::TABLE . ' AS u
            LEFT JOIN 
                user_interest AS ui
            ON
                ui.userId = u.id
            LEFT JOIN
                interest AS i
            ON
                i.id = ui.interestId                
            WHERE 
                u.id IN (' . implode(',', $userIds) . ')
            GROUP BY
                u.id 
            '
        )->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            return $userModels;
        }

        foreach ($items as $item) {
            $userModels[$item['id']] = (new UserModel())
                ->setId($item['id'])
                ->setFirstName($item['firstName'])
                ->setLastName($item['lastName'])
                ->setYears($item['years'])
                ->setSex($item['sex'])
                ->setCity($item['city'])
                ->setInterests(
                    array_filter(
                        array_values(
                            json_decode($item['interests'], true)
                        )
                    )
                )
                ->setPasswordHash($item['password']);
        }

        return $userModels;
    }

    /**
     * @param UserModel $userModel
     * @return int
     * @throws RuntimeException
     * @throws Throwable
     */
    public function create(UserModel $userModel): int
    {
        try {
            $this->db->beginTransaction();

            $statement = $this->db->prepare("
                INSERT INTO ' . self::TABLE . ' 
                        (firstName,lastName,years,sex,city,password) 
                    VALUES(
                        ?,?,?,?,?,?
                    )
            ");

            $statement->execute([
                $userModel->getFirstName(),
                $userModel->getLastName(),
                $userModel->getYears() ,
                $userModel->getSex(),
                $userModel->getCity(),
                $userModel->getPasswordHash()
            ]);

            $userId = (int) $this->db->lastInsertId();

            $interestIds = [];
            foreach ($userModel->getInterests() as $interest) {
                $interestId = $this->getInterestId($interest) ?? $this->saveInterest($interest);
                $interestIds[] = $interestId;
            }
            !empty($interestIds) && $this->saveUserInterest($userId, $interestIds);

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

        return $userId;
    }

    /**
     * @param string $term
     * @return int[]
     */
    public function searchByNameAndLastName(string $term): array
    {
        $statement = $this->db->prepare('
            SELECT 
                id
            FROM 
                ' . self::TABLE . ' 
            WHERE 
                 firstName LIKE ? and lastName LIKE ?
            ORDER BY id
            '
        );

        $statement->execute([
            $term,
            $term
        ]);

        $userIds = array_column($statement->fetchAll(PDO::FETCH_ASSOC) ?? [], 'id');
        if (empty($userIds)) {
            return [];
        }

        return $userIds;
    }

    /**
     * @return int
     * @throws RuntimeException
     */
    public function count(): int
    {
        return (int) $this->query('
            SELECT COUNT(*) AS cnt FROM ' . self::TABLE
        )->fetch(PDO::FETCH_ASSOC)['cnt'];
    }

    /**
     * @param string $interest
     * @return int|null
     */
    public function getInterestId(string $interest): ?int
    {
        $statement = $this->db->prepare('
            SELECT 
                id
            FROM 
                interest 
            WHERE 
                `name` LIKE ?
            LIMIT 1
            '
        );

        $statement->execute([
            $interest
        ]);

        $data = $statement->fetch(PDO::FETCH_ASSOC);

        return isset($data['id']) ? (int) $data['id'] : null;
    }

    /**
     * @param string $interest
     * @return int
     */
    public function saveInterest(string $interest): int
    {
        $statement = $this->db->prepare('
            INSERT INTO interest 
                (`name`) 
            VALUES 
                (?)
            '
        );

        $statement->execute([
            $interest
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param int $userId
     * @param int[] $interestIds
     * @return bool
     * @throws RuntimeException
     */
    public function saveUserInterest(int $userId, array $interestIds): bool
    {
        $interestsValues = [];
        foreach ($interestIds as $interestId) {
            $interestsValues[] = '(' . $userId . ', ' . $interestId . ')';
        }

        $this->query('DELETE FROM user_interest WHERE userId = ' . $userId);

        if (empty($interestIds)) {
            return true;
        }

        return $this->query('
            INSERT INTO user_interest 
                (userId, interestId) 
            VALUES 
                ' . implode(',', $interestsValues) . '
            '
        )->rowCount() > 0;
    }

    /**
     * @param array $userInterests
     * @return bool
     * @throws RuntimeException
     */
    public function saveUserInterestMass(array $userInterests): bool
    {
        $userIds = [];
        $interestsValues = [];
        foreach ($userInterests as $userId => $interests) {
            $userIds[] = $userId;
            foreach ($interests as $interestId) {
                $interestsValues[] = '(' . $userId . ', ' . $interestId . ')';
            }
        }

        if (!empty($userIds)) {
            $this->query('DELETE FROM user_interest WHERE userId IN (' . implode(',', $userIds) . ')');
        }

        if (empty($interestsValues)) {
            return true;
        }

        return $this->query('
            INSERT INTO user_interest 
                (userId, interestId) 
            VALUES 
                ' . implode(',', $interestsValues) . '
            '
            )->rowCount() > 0;
    }

}