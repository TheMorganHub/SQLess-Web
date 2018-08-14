<?php

namespace sqless;

use Illuminate\Database\Eloquent\Model;

class Googleuser extends Model {
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'google_id', 'email'
    ];

//    public function role() {
//        return $this->hasOne('sqless\Role', 'id', 'id_roles');
//    }
}
