<?php

namespace Respect\Structural\Tests;

use Respect\Structural\Driver\MongoDb\Style;

class StyleTest extends \PHPUnit_Framework_TestCase
{
    public function testIdentifierShouldReturnsFormattedName()
    {
        $style = new Style();
        $this->assertEquals('_id', $style->identifier('id'));
    }
}
