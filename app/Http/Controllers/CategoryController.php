<?php


namespace App\Http\Controllers;


use App\Category;
use Laravel\Lumen\Http\Request;

class CategoryController
{
    public function get($id){
        $result = Category::with('allies')->get()->find($id);
        return response()->json($result);
    }
}
