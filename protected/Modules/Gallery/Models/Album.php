<?php

namespace App\Modules\Gallery\Models;


use T4\Core\Collection;
use T4\Core\Std;
use T4\Orm\Model;

class Album
    extends Model
{

    protected static $schema = [
        'columns' => [
            'title' => ['type' => 'string'],
            'url' => ['type' => 'string'],
            'published' => ['type' => 'datetime'],
        ],
        'relations' => [
            'photos' => ['type' => self::HAS_MANY, 'model' => Photo::class],
            'cover' => ['type' => self::HAS_ONE, 'model' => Photo::class],
        ]
    ];

    static protected $extensions = ['tree'];

    public function beforeSave()
    {
        if ($this->isNew()) {
            $this->published = date('Y-m-d H:i:s', time());
        }

        return parent::beforeSave();
    }

    public function afterDelete()
    {
        $this->photos->delete();
    }

    public function isCover()
    {
        if ($this->__photo_id) {
            return $this->cover->image;
        } else {
            if (is_array($this->photos->collect('published'))) {
                $key = array_search(max($this->photos->collect('published')), $this->photos->collect('published'));
                return $this->photos->collect('image')[$key];
            } else {
                return $this->photos->collect('image');
            }
        }
    }

    public function getBreadCrumbs()
    {
        $ret = new Collection();
        foreach ($this->findAllParents() as $i => $parent) {
            $p = new Std;
            $p->url = $parent->url;
            $p->title = $parent->title;
            $ret[] = $p;
        }
        return $ret;
    }

    public function getChildren()
    {
        $ret = new Collection();
        foreach ($this->findAllChildren() as $i => $child) {
            $p = new Std;
            $p->Pk = $child->Pk;
            $p->title = $child->title;
            $p->prt = $child->__prt;
            $ret[] = $p;
        }
        return $ret;
    }

    public function countPhotos()
    {
        return count($this->photos->collect('image'));
    }
}