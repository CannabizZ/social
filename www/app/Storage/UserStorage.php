<?php
declare(strict_types=1);

namespace App\Storage;

use App\Exception\RuntimeException;
use App\Model\UserModel;
use PDO;
use Throwable;

class UserStorage extends AbstractStorage
{

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
                user AS u
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
                INSERT INTO user 
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
     * @param string $interest
     * @return int|null
     */
    protected function getInterestId(string $interest): ?int
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
    protected function saveInterest(string $interest): int
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
    protected function saveUserInterest(int $userId, array $interestIds): bool
    {
        $interestsValues = [];
        foreach ($interestIds as $interestId) {
            $interestsValues[] = '(' . $userId . ', ' . $interestId . ')';
        }

        return $this->query('
            INSERT INTO user_interest 
                (userId, interestId) 
            VALUES 
                ' . implode(',', $interestsValues) . '
            '
        )->execute();
    }
}