<?php

namespace Vendor\Package\Traits;

use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Model;

trait Hierarchy
{
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function getHierarchyNodes()
    {
        return $this;
    }

    public function createChildOf($targetObj)
    {
        if (!is_object($targetObj)) {
            $targetObj = self::find($targetObj);
        }

        $this->parent_id = $targetObj->id;
        $this->depth = $targetObj->depth + 1;

        $insertionPoint = $targetObj->rgt;

        $this->getConnection()->table($this->getTable())
            ->where('lft', '>=', $insertionPoint)
            ->update([
                'lft' => \DB::raw('lft + 2'),
                'rgt' => \DB::raw('rgt + 2'),
            ]);

        $this->lft = $insertionPoint;
        $this->rgt = $insertionPoint + 1;

        $this->save();

        $targetObj->shiftParentRgt();
    }

    public function shiftParentRgt()
    {
        $maxRgt = $this->newQuery()
            ->where('parent_id', $this->id)
            ->max('rgt');

        $this->update([
            'rgt' => $maxRgt + 1,
        ]);
    }

    public function createParent()
    {
        $maxRgt = $this->newQuery()->max('rgt');
        $lft = $maxRgt + 1;
        $rgt = $lft + 1;

        $this->lft = $lft;
        $this->rgt = $rgt;

        $this->save();
    }

    public function buildTree($selection = ['*'], $conditions = null)
    {
        if(!$selection)$selection=['*'];

        $query = $this->orderBy('lft');

        if ($conditions instanceof \Closure) {
            $query = $conditions($query);
        }
        $collection = $query->get($selection);

        $allItemsHaveNullParent = $collection->every(function ($item) {
            return $item->parent_id === null;
        });

        if ($allItemsHaveNullParent) {
            return collect($collection);
        }
        return $this->buildNestedSet($collection);
    }

    protected function buildNestedSet($items)
    {
        $dict = $this->getDictionary($items);

        uasort($dict, function ($a, $b) {
            return ($a->lft >= $b->lft) ? 1 : -1;
        });

        return new BaseCollection($this->hierarchical($dict));
    }

    protected function hierarchical($result)
    {
        foreach ($result as $key => $node) {
            $node->setRelation('children', new BaseCollection);
        }

        $nestedKeys = [];

        foreach ($result as $key => $node) {
            $parentKey = $node->parent_id;

            if (!is_null($parentKey) && array_key_exists($parentKey, $result)) {
                if (!isset($result[$parentKey]->children)) {
                    $result[$parentKey]->setRelation('children', new BaseCollection);
                }

                $result[$parentKey]->children[] = $node;

                $nestedKeys[] = $node->getKey();
            }
        }

        foreach ($nestedKeys as $key) {
            unset($result[$key]);
        }

        return $result;
    }

    protected function getDictionary($items)
    {
        $dict = [];

        foreach ($items as $item) {
            $dict[$item->getKey()] = $item;
        }

        return $dict;
    }
}

