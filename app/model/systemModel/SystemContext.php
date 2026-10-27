<?php

namespace App\Model\SystemModel;

use Nette\Database\Explorer;

class SystemContext
{
    /** @var Explorer */
    public $context;

    /**
     * @param Explorer $context
     */
    public function __construct(Explorer $context)
    {
        $this->context = $context;
    }
}
