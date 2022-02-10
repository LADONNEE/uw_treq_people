<?php
/** 
 * Base class for application entities
 * Uses Laravel Eloquent ORM
 */
namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;

class AbstractModel extends Model
{

    /**
     * Expand Eloquent date converter which limits date formats
     * @param mixed $value
     * @return Carbon
     * @throws \Exception
     */
    protected function asDateTime($value)
    {
        if ($value == '0000-00-00 00:00:00') {
            return null;
        }
        // Eloquent\Model does fine with these
        if (empty($value)) {
            return parent::asDateTime($value);
        }
        if ($value instanceof Carbon) {
            return parent::asDateTime($value);
        }
        if ($value instanceof DateTime) {
            return parent::asDateTime($value);
        }
        if (is_numeric($value)) {
            return parent::asDateTime($value);
        }
        // Use general constructor instead of format specific constructor
        return parent::asDateTime(new Carbon($value));
    }

    /**
     * Convert empty values to nulls
     * Null, 0, '', [] become null
     * @param $value
     * @return mixed
     */
    protected function makeEmptyNull($value)
    {
        if (empty($value)) {
            return null;
        }
        return $value;
    }

    /**
     * Add pre and post Save, Insert, Update hooks to Eloquent\Model save()
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $update = $this->exists;
        $ok = $this->preSave();
        if ($ok === false) {
            return false;
        }
        if ($update) {
            $ok = $this->preUpdate();
        } else {
            $ok = $this->preInsert();
        }
        if ($ok === false) {
            return false;
        }
        $ok = parent::save($options);
        $this->postSave();
        if ($update) {
            $this->postUpdate();
        } else {
            $this->postInsert();
        }
        return $ok;
    }

    /**
     * Add preDelete and postDelete hooks to Eloquent\Model delete()
     * @return bool|null
     */
    public function delete()
    {
        $ok = $this->preDelete();
        if ($ok === false) {
            return false;
        }
        $ok = parent::delete();
        $this->postDelete();
        return $ok;
    }

    protected function preSave()
    {
        return true;
    }

    protected function postSave()
    {
        return true;
    }

    protected function preInsert()
    {
        return true;
    }

    protected function postInsert()
    {
        return true;
    }

    protected function preUpdate()
    {
        return true;
    }

    protected function postUpdate()
    {
        return true;
    }

    protected function preDelete()
    {
        return true;
    }

    protected function postDelete()
    {
        return true;
    }

}

