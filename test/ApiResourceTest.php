<?php
/**
 * Created by PhpStorm.
 * User: cwioro
 * Date: 22.06.15
 * Time: 13:57
 */


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/BitcodinApiTestBaseClass.php';

use bitcodin\Bitcodin;
use bitcodin\Input;
use bitcodin\HttpInputConfig;
use bitcodin\FtpInputConfig;

class ConcreteApiResource extends \bitcodin\ApiResource
{
    public static function postRequest($url, $body, $expectedCode)
    {
        return self::_postRequest($url, $body, $expectedCode);
    }

    public static function patchRequest($url, $expectedCode)
    {
        return self::_patchRequest($url, $expectedCode);
    }


    public static function deleteRequest($url, $expectedCode)
    {
        return self::_patchRequest($url, $expectedCode);
    }
}


class ApiResourceTest extends BitcodinApiTestBaseClass
{
    public function testErrorPostRequest()
    {
        Bitcodin::setApiToken($this->getApiKey());
        $this->setExpectedException('bitcodin\exceptions\BitcodinResourceNotFoundException');
        ConcreteApiResource::postRequest('/lkajljow/', '', 201);
    }

    public function testErrorDeleteRequest()
    {
        Bitcodin::setApiToken($this->getApiKey());
        $this->setExpectedException('bitcodin\exceptions\BitcodinResourceNotFoundException');
        ConcreteApiResource::deleteRequest('/lkajljow/', 201);
    }

    public function testErrorPatchRequest()
    {
        Bitcodin::setApiToken($this->getApiKey());
        $this->setExpectedException('bitcodin\exceptions\BitcodinResourceNotFoundException');
        ConcreteApiResource::patchRequest('/lkajljow/', 201);
    }

}
