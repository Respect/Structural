<?php

namespace Respect\Structural\tests\Driver\DynamoDb;

use Respect\Structural\Driver\DynamoDb\Style;

class StyleTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldRetrieveIdenfier()
    {
        $this->assertEquals('_id', (new Style())->identifier('id'));
    }
}
