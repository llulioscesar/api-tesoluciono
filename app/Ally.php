<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ally extends Model{
    protected $fillable = ['name', 'logo', 'enable', 'category_id'];
    protected $table = 'ally';
    public $timestamps = false;
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
