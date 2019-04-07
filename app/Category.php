<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function allies(){
        return $this->hasMany(Ally::class);
    }
}
