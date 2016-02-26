<?php

namespace Respect\Structural\Tests\Unit;

use Respect\Structural\Driver;
use Respect\Structural\Mapper;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    public function testInsertANewDocument()
    {
        $driver = $this->getMockForAbstractClass(Driver::class);
        $driver->expects($this->once())->method('insert')->willReturnCallback(function ($collection, $document) {
            $document->id = 1;
        });

        $mapper = new Mapper($driver);

        $author = new \stdClass();
        $author->name = 'Respect';
        $mapper->authors->persist($author);
        $mapper->flush();

        return $author;
    }

    /**
     * @depends testInsertANewDocument
     */
    public function testUpdateADocument($author)
    {
        $driver = $this->getMockForAbstractClass(Driver::class);
        $driver->expects($this->once())->method('update');
        $driver->expects($this->once())->method('fetch')->willReturnCallback(function (\Iterator $statement) {
            return $statement->current();
        });
        $driver->expects($this->once())->method('find')->willReturn((new \ArrayObject([$author]))->getIterator());
        $driver->expects($this->once())->method('generateQuery')->willReturn(['id' => 1]);

        $mapper = new Mapper($driver);

        $author = $mapper->authors[1]->fetch();
        $author->name = 'Respect Structural';
        $mapper->authors->persist($author);
        $mapper->flush();
        return $author;
    }

    /**
     * @param $author
     * @depends testUpdateADocument
     */
    public function testRemoveADocument($author)
    {
        $driver = $this->getMockForAbstractClass(Driver::class);
        $driver->expects($this->once())->method('remove');
        $driver->expects($this->once())->method('fetch')->willReturnCallback(function (\Iterator $statement) {
            return $statement->current();
        });
        $driver->expects($this->once())->method('find')->willReturn((new \ArrayObject([$author]))->getIterator());
        $driver->expects($this->once())->method('generateQuery')->willReturn(['id' => 1]);

        $mapper = new Mapper($driver);

        $author = $mapper->authors[1]->fetch();
        $mapper->authors->remove($author);
        $mapper->flush();
    }
}