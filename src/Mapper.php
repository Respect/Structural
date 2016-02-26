<?php

namespace Respect\Structural;

use Respect\Data\AbstractMapper;
use Respect\Data\CollectionIterator;
use Respect\Data\Collections\Collection;
use Exception;
use SplObjectStorage;
use Respect\Data\Collections as c;
use ReflectionProperty;

/** Maps objects to nosql operations */
class Mapper extends AbstractMapper implements
    c\Filterable,
    c\Mixable,
    c\Typable
{
    /** @var \Respect\Structural\Driver Holds our connector* */
    protected $driver;

    /** @var string Namespace to look for entities * */
    public $entityNamespace = '\\';

    /**
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        parent::__construct();

        $this->driver = $driver;
    }

    /**
     * @return Mapper
     */
    public function __get($name)
    {
        return parent::__get($name);
    }

    /**
     * Flushes a single instance into the database. This method supports
     * mixing, so flushing a mixed instance will flush distinct tables on the
     * database
     *
     * @param object $entity Entity instance to be flushed
     *
     * @return null
     */
    protected function flushSingle($entity)
    {
        $coll = $this->tracked[$entity];
        $cols = $this->extractColumns($entity, $coll);

        if ($this->removed->contains($entity)) {
            $this->rawDelete($coll, $entity);
        } elseif ($this->new->contains($entity)) {
            $this->rawInsert($coll, $entity);
        } else {
            $this->rawUpdate($cols, $coll);
        }
    }

    public function persist($object, Collection $onCollection)
    {
        $next = $onCollection->getNext();

        if ($this->filterable($onCollection)) {
            $next->setMapper($this);
            $next->persist($object);
            return;
        }

        if ($next) {
            $remote = $this->getStyle()->remoteIdentifier($next->getName());
            $next->setMapper($this);
            $next->persist($object->$remote);
        }

        foreach ($onCollection->getChildren() as $child) {
            $remote = $this->getStyle()->remoteIdentifier($child->getName());
            $child->persist($object->$remote);
        }

        return parent::persist($object, $onCollection);
    }

    /**
     * Receives columns from an entity and her collection. Returns the columns
     * that belong only to the main entity. This method supports mixing, so
     * extracting mixins will also persist them on their respective
     * tables
     *
     * @param \Respect\Data\Collections\Collection $collection Target collection
     * @param array $cols Entity columns
     *
     * @return array Columns left for the main collection
     */
    protected function extractAndOperateMixins(Collection $collection, $cols)
    {
        if (!$this->mixable($collection)) {
            return $cols;
        }

        foreach ($this->getMixins($collection) as $mix => $spec) {
            //Extract from $cols only the columns from the mixin
            $mixCols = array_intersect_key(
                $cols,
                array_combine( //create array with keys only
                    $spec,
                    array_fill(0, count($spec), '')
                )
            );
            if (isset($cols["{$mix}_id"])) {
                $mixCols['id'] = $cols["{$mix}_id"];
                $cols = array_diff($cols, $mixCols); //Remove mixin columns
                $this->rawUpdate($mixCols, $this->__get($mix));
            } else {
                $mixCols['id'] = null;
                $cols = array_diff($cols, $mixCols); //Remove mixin columns
                $this->rawinsert($mixCols, $this->__get($mix));
            }
        }

        return $cols;
    }

    protected function guessCondition(&$columns, Collection $collection)
    {
        $primaryName = $this->getStyle()->identifier($collection->getName());
        $condition = array($primaryName => $columns[$primaryName]);
        unset($columns[$primaryName]);
        return $condition;
    }

    protected function rawDelete(Collection $collection, $entity)
    {
        $name = $collection->getName();
        $columns = $this->extractColumns($entity, $collection);
        $condition = $this->guessCondition($columns, $collection);

        return $this->driver->remove($name, $condition);
    }

    protected function rawUpdate(array $columns, Collection $collection)
    {
        $columns = $this->extractAndOperateMixins($collection, $columns);
        $name = $collection->getName();
        $condition = $this->guessCondition($columns, $collection);

        $this->driver->update($name, $condition, $columns);
    }

    protected function rawInsert(Collection $collection, $entity = null)
    {
        $name = $collection->getName();
        $this->driver->insert($name, $entity);
    }

    public function flush()
    {
        try {
            foreach ($this->changed as $entity) {
                $this->flushSingle($entity);
            }
        } catch (Exception $e) {
            throw $e;
        }

        $this->reset();
    }

    protected function createStatement(Collection $collection, $withExtra = null)
    {
        $query = $this->generateQuery($collection);

        $withExtraList = (array)$withExtra;

        $withExtraList = array_merge($withExtraList, $query);

        return $this->driver->find($collection->getName(), $withExtraList);
    }

    protected function generateQuery(Collection $collection)
    {
        return $this->driver->generateQuery($collection);
    }

    protected function extractColumns($entity, Collection $collection)
    {
        $primaryName = $this->getStyle()->identifier($collection->getName());
        $cols = get_object_vars($entity);

        return $cols;
    }

    protected function hasComposition($entity, $next, $parent)
    {
        $s = $this->getStyle();
        return $entity === $s->composed($parent, $next)
        || $entity === $s->composed($next, $parent);
    }

    protected function fetchSingle(Collection $collection, $statement)
    {
        $name = $collection->getName();
        $entityName = $name;
        $row = $this->driver->fetch($statement);

        if (!$row) {
            return false;
        }

        if ($this->typable($collection)) {
            $entityName = $row->{$this->getType($collection)};
        }

        $entities = new SplObjectStorage();
        $entities[$this->transformSingleRow($row, $entityName)] = $collection;

        return $entities;
    }

    protected function getNewEntityByName($entityName)
    {
        $entityName = $this->getStyle()->styledName($entityName);
        $entityClass = $this->entityNamespace . $entityName;
        $entityClass = class_exists($entityClass) ? $entityClass : '\stdClass';
        return new $entityClass;
    }

    protected function transformSingleRow($row, $entityName)
    {
        $newRow = $this->getNewEntityByName($entityName);

        foreach ($row as $prop => $value) {
            $this->inferSet($newRow, $prop, $value);
        }
        return $newRow;
    }

    protected function inferSet(&$entity, $prop, $value)
    {
        $setterName = $this->getSetterStyle($prop);
        try {
            $mirror = new \ReflectionProperty($entity, $prop);
            $mirror->setAccessible(true);
            $mirror->setValue($entity, $value);
        } catch (\ReflectionException $e) {
            $entity->$prop = $value;
        }
    }

    protected function fetchMulti(Collection $collection, $statement)
    {
        $entityInstance = null;
        $row = $this->driver->fetch($statement);

        if (!$row) {
            return false;
        }

        $this->postHydrate(
            $entities = $this->createEntities($row, $statement, $collection)
        );

        return $entities;
    }

    protected function createEntities($row, $statement, Collection $collection)
    {

        $entities = new SplObjectStorage();
        $entitiesInstances = $this->buildEntitiesInstances(
            $collection,
            $entities
        );
        $entityInstance = array_pop($entitiesInstances);

        //Reversely traverses the columns to avoid conflicting foreign key names
        foreach (array_reverse($row, true) as $col => $value) {
            $columnMeta = $statement->getColumnMeta($col);
            $columnName = $columnMeta['name'];
            $primaryName = $this->getStyle()->identifier(
                $entities[$entityInstance]->getName()
            );

            $this->inferSet($entityInstance, $columnName, $value);

            if ($primaryName == $columnName) {
                $entityInstance = array_pop($entitiesInstances);
            }
        }
        return $entities;
    }

    protected function buildEntitiesInstances(Collection $collection, SplObjectStorage $entities) {
        $entitiesInstances = array();

        foreach (CollectionIterator::recursive($collection) as $c) {
            if ($this->filterable($c) && !$this->getFilters($c)) {
                continue;
            }

            $entityInstance = $this->getNewEntityByName($c->getName());
            $mixins = array();

            if ($this->mixable($c)) {
                $mixins = $this->getMixins($c);
                foreach ($mixins as $mix) {
                    $entitiesInstances[] = $entityInstance;
                }
            }

            $entities[$entityInstance] = $c;
            $entitiesInstances[] = $entityInstance;
        }
        return $entitiesInstances;
    }

    protected function postHydrate(SplObjectStorage $entities)
    {
        $entitiesClone = clone $entities;

        foreach ($entities as $instance) {
            foreach ($instance as $field => &$v) {
                if ($this->getStyle()->isRemoteIdentifier($field)) {
                    foreach ($entitiesClone as $sub) {
                        $this->tryHydration($entities, $sub, $field, $v);
                    }
                }
            }
        }
    }

    protected function tryHydration($entities, $sub, $field, &$v)
    {
        $tableName = $entities[$sub]->getName();
        $primaryName = $this->getStyle()->identifier($tableName);

        if ($tableName === $this->getStyle()->remoteFromIdentifier($field)
            && $sub->{$primaryName} === $v
        ) {
            $v = $sub;
        }
    }

    protected function getSetterStyle($name)
    {
        $name = str_replace('_', '', $this->getStyle()->styledProperty($name));
        return "set{$name}";
    }

    public function getFilters(Collection $collection)
    {
        return $collection->getExtra('filters');
    }

    public function getMixins(Collection $collection)
    {
        return $collection->getExtra('mixins');
    }

    public function getType(Collection $collection)
    {
        return $collection->getExtra('type');
    }

    public function mixable(Collection $collection)
    {
        return $collection->have('mixins');
    }

    public function typable(Collection $collection)
    {
        return $collection->have('type');
    }

    public function filterable(Collection $collection)
    {
        return $collection->have('filters');
    }

}

