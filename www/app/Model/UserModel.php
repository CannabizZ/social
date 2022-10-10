<?php
declare(strict_types=1);

namespace App\Model;

class UserModel extends AbstractModel
{
    public const SEX_MALE = 'male';
    public const SEX_FEMALE = 'female';

    public const SEX_ENUMS = [
        self::SEX_MALE,
        self::SEX_FEMALE
    ];

    protected string $firstName;
    protected string $lastName;
    protected int $years;
    protected string $sex;
    protected array $interests;
    protected string $city;
    protected string $passwordHash;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return UserModel
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return UserModel
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return int
     */
    public function getYears(): int
    {
        return $this->years;
    }

    /**
     * @param int $years
     * @return UserModel
     */
    public function setYears(int $years): self
    {
        $this->years = $years;
        return $this;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     * @return UserModel
     */
    public function setSex(string $sex): self
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getInterests(): array
    {
        return $this->interests;
    }

    /**
     * @param string[] $interests
     * @return UserModel
     */
    public function setInterests(array $interests): self
    {
        $this->interests = $interests;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return UserModel
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     * @return UserModel
     */
    public function setPasswordHash(string $passwordHash): UserModel
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

}