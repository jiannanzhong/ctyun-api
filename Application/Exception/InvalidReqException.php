<?php

namespace CtCloudBackend\Exception;


class InvalidReqException extends ApiException
{
    public function __construct($msg)
    {
        parent::__construct(1001, $msg);
    }
}