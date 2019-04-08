<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    public $table = 'token';
    public $timestamps = false;

    protected $fillable = ['token'];

}
