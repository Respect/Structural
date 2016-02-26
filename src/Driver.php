<?php

namespace Respect\Structural;

use Respect\Data\Collections\Collection;

interface Driver
{
    public function getConnection();

    /**
     * @param \Iterator $cursor
     * @return array
     */
    public function fetch(\Iterator $cursor);

    /**
     * @param $collection
     * @param array $query
     * @return \Iterator
     */
    public function find($collection, array $query = array());

    /**
     * @param string $collection
     * @param object $document
     * @return void
     */
    public function insert($collection, $document);

    /**
     * @param string $collection
     * @param array $criteria
     * @param object $document
     * @return void
     */
    public function update($collection, $criteria, $document);

    /**
     * @param string $collection
     * @param array $criteria
     * @return void
     */
    public function remove($collection, $criteria);

    /**
     * @param Collection $collection
     * @return array
     */
    public function generateQuery(Collection $collection);
}