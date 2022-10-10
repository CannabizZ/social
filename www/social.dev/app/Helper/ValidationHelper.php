<?php
declare(strict_types=1);

namespace App\Helper;

use App\Base\Request;
use App\Exception\RuntimeException;
use App\Exception\ValidationException;
use App\Model\UserModel;

class ValidationHelper
{
    /**
     * @param Request $request
     * @return UserModel
     * @throws ValidationException
     * @throws RuntimeException
     */
    public static function validateUserRegistration(Request $request): UserModel
    {
        $body = $request->getBody();
        if (empty($body)) {
            throw new ValidationException('Empty form');
        }

        $requiredFields = [
            'firstName' => 'string',
            'lastName' => 'string',
            'years' => 'integer',
            'sex' => 'string',
            'interests' => 'array',
            'city' => 'string',
            'password' => 'string'
        ];

        foreach ($requiredFields as $key => $type) {
            if (gettype($body[$key]) !== $type) {
                throw new ValidationException('Field `' . $key . '` must be of type `' . $type . '`, `' . gettype($body[$key]) . '` given');
            }

            if ($type !== 'array' && empty($body[$key])) {
                throw new ValidationException('Empty required field `' . $key . '`');
            }
        }

        if (!in_array($body['sex'], UserModel::SEX_ENUMS)) {
            throw new ValidationException('User sex must be of one in values: ' . implode(', ', UserModel::SEX_ENUMS) . '.');
        }

        $password = PasswordHelper::getHash($body['password']);

        return (new UserModel())
            ->setFirstName($body['firstName'])
            ->setLastName($body['lastName'])
            ->setYears($body['years'])
            ->setSex($body['sex'])
            ->setInterests($body['interests'])
            ->setCity($body['city'])
            ->setPasswordHash($password)
        ;
    }

}