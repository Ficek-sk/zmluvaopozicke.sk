<?php

namespace App\Model\SystemModel;

use Nette\Database\Context;

class SystemContext
{
    /** @ Context $context */
    public $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
