<?php

namespace Respect\Structural\Driver\DynamoDb;

use Respect\Data\Styles\Standard;

class Style extends Standard
{
    public function identifier($name)
    {
        return '_'.parent::identifier($name);
    }
}
