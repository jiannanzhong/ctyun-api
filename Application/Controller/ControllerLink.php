<?php

namespace CtCloudBackend\Controller;

use CtCloudBackend\Common\C;
use CtCloudBackend\Helper\LinkHelper;
use CtCloudBackend\Helper\ParamChecker;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class ControllerLink
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function getRealLinkByFileRecord(Request $req, Response $rsp, array $args)
    {
        $param = $req->getQueryParams();
        ParamChecker::checkArrayKeyExist(['authCode', 'fileRecord'], $param);

        if ($param['authCode'] !== C::GET_REAL_LINK_AUTH_CODE) {
            return $rsp->withStatus(401);
        }

        return $rsp->write(LinkHelper::getRealLinkByFileRecord($param['fileRecord']));
    }

}