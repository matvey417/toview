<?php

namespace App\Tests\Unit\Adapter\User;

use App\Adapter\User\UserVkAdapter;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

class UserVkAdapterTest extends TestCase
{
    /**
     * Проверка на наличие ошибки в аргументе
     *
     * @covers UserVkAdapter::__construct
     *
     * @return void
     * @throws Exception
     */
    public function testException()
    {
        $userinfo = new stdClass();
        $userinfo->error = 'ошибочка';
        $userinfo->error_description = 'Описание ошибочки';
        $this->expectException(Exception::class);
        new UserVkAdapter($userinfo, ['helicopter']);
    }

    /**
     * Проверка возвращаемых значений в ДТО
     *
     * @covers UserVkAdapter::getUserVkDTO
     *
     * @return void
     * @throws Exception
     */
    public function testGetUserVkDTO()
    {
        $city = new stdClass();
        $city->title = 'Cуперград';

        $country = new stdClass();
        $country->title = 'Лучшая страна на свете';

        $info = new stdClass();

        $userinfo = new stdClass();
        $response[0] = $userinfo;
        $info->response = $response;
        $userinfo->id = '1122';
        $userinfo->first_name = 'Вася';
        $userinfo->last_name = 'Пуп';
        $userinfo->photo_max_orig = 'Большая фотография';
        $userinfo->sex = 2;
        $userinfo->city = $city;
        $userinfo->country = $country;
        $response['email'] = 'yuo@sob.go';

        $vkDTO = (new UserVkAdapter($info, $response))->getUserVkDTO();

        $this->assertEquals($vkDTO->getId(), $userinfo->id);
        $this->assertEquals($vkDTO->getFirstName(), $userinfo->first_name);
        $this->assertEquals($vkDTO->getLastName(), $userinfo->last_name);
        $this->assertEquals($vkDTO->getPhotoMaxOrig(), $userinfo->photo_max_orig);
        $this->assertEquals($vkDTO->getSex(), $userinfo->sex);
        $this->assertEquals($vkDTO->getCity(), $userinfo->city->title);
        $this->assertEquals($vkDTO->getCountry(), $userinfo->country->title);
        $this->assertEquals($vkDTO->getEmail(), $response['email']);
    }
}
