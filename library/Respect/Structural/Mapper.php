<?php

namespace Respect\Structural;

use Respect\Data\AbstractMapper;
use Respect\Data\Collection;

class Mapper extends AbstractMapper
{
    protected $db;
    
    public function __construct(MongoDb $db=null) //remove null from param list TODO
    {
        $this->db = $db;
    }
    
    public function persist($object, Collection $onCollection)
    {
        
    }

    public function remove($object, Collection $fromCollection)
    {
        
    }

    public function fetch(Collection $fromCollection, $withExtra=null)
    {
        
    }

    public function fetchAll(Collection $fromCollection, $withExtra=null)
    {
        
    }
    
    public function generateQuery(Collection $collection)
    {
        return array(
            $collection->getName() => array()
        );
    }
}