<?php

declare(strict_types=1);

namespace App\Storage;

use App\Exception\RuntimeException;
use App\Helper\PasswordHelper;

class MigrateStorage extends AbstractStorage
{
    /**
     * @return void
     * @throws RuntimeException
     */
    public function seedUsers(): void
    {
        ini_set('memory_limit', '1G');

        $names = json_decode(file_get_contents(__DIR__ . '/../../config/russian_names.json'), true);
        $surnames = array_column(json_decode(file_get_contents(__DIR__ . '/../../config/russian_surnames.json'), true),'Surname');
        $namesCount = count($names) - 1;
        $surnamesCount = count($surnames) - 1;
        $cities = ['spb','msk','xyz'];
        $password = PasswordHelper::getHash('pass');
        $values = [];
        $i = 0;

        while (true) {
            $name = $names[rand(0, $namesCount)];
            $surname = $surnames[rand(0,$surnamesCount)];
            $values[] = "('" . implode("','", [
                    'firstName' => $name['Name'],
                    'lastName' => $surname,
                    'years' => rand(1,80),
                    'sex' => $name['Sex'] === 'Ж' ? 'female' : 'male',
                    'city' => $cities[rand(0,2)],
                    'password' => $password
                ])  . "')";
            ++$i;
            if ($i > 1000000) {
                break;
            }
        }

        foreach (array_chunk($values, 500) as $chunk) {
            $this->query('
                INSERT INTO ' . UserStorage::TABLE . ' 
                        (firstName,lastName,years,sex,city,password) 
                    VALUES ' . implode(',', $chunk) . '
            ');
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    public function migrate(): void
    {
        $sql = '
            CREATE DATABASE IF NOT EXISTS social;
            
            -- DROP TABLE IF EXISTS social.user;
            -- DROP TABLE IF EXISTS social.interest;
            -- DROP TABLE IF EXISTS social.user_interest;
            -- DROP TABLE IF EXISTS social.user_page;
            -- DROP TABLE IF EXISTS social.friends;
            
            CREATE TABLE IF NOT EXISTS social.user
            (
                id int unsigned auto_increment primary key,
                firstName text not null,
                lastName text not null,
                years tinyint unsigned NOT NULL,
                sex enum(\'male\', \'female\') null,
                city text not null,
                password char(60) not null
            );
            
            CREATE TABLE IF NOT EXISTS social.interest
            (
                id int unsigned auto_increment primary key,
                name text not null
            );
            
            CREATE TABLE IF NOT EXISTS social.user_interest
            (
                userId     int unsigned not null,
                interestId int unsigned not null,
                primary key (userId, interestId)
            );
            
            CREATE TABLE IF NOT EXISTS social.user_page
            (
                id int unsigned auto_increment primary key,
                userId int unsigned not null,
                content text not null
            );
            
            CREATE TABLE IF NOT EXISTS social.friends
            (
                userId       int unsigned not null,
                friendUserId int unsigned not null,
                primary key (userId, friendUserId)
            );
        ';

        $this->query($sql);
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    public function seedInterests(): void
    {
        $storage = new UserStorage();
        $usersCount = $storage->count();

        $interests = [
            'football','car','fishing','tourism','ski'
        ];

        $interestIds = [];
        foreach ($interests as $interest) {
            $interestId = $storage->getInterestId($interest) ?? $storage->saveInterest($interest);
            $interestIds[] = $interestId;
        }

        $i = 1;
        $userInterests = [];
        $userInterestsCount = 0;
        while ($i < $usersCount) {
            shuffle($interestIds);
            $userInterestsIds = array_slice($interestIds, 0, rand(0,5));
            $userInterests[$i] = $userInterestsIds;
            ++$userInterestsCount;
            if ($userInterestsCount >= 500) {
                $storage->saveUserInterestMass($userInterests);
                $userInterests = [];
                $userInterestsCount = 0;
            }
            ++$i;
        }

        $storage->saveUserInterestMass($userInterests);
    }
}
