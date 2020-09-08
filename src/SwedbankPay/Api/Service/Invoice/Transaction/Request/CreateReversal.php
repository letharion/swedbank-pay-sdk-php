<?php

namespace SwedbankPay\Api\Service\Invoice\Transaction\Request;

use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\ReversalObject;
use SwedbankPay\Api\Service\Request;

class CreateReversal extends Request
{
    public function setup()
    {
        $this->setOperationRel('create-reversal');
        $this->setResponseResourceFQCN(ReversalObject::class);
    }
}
