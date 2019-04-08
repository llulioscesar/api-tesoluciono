<?php


namespace App\Http\Controllers;


use App\Category;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Http\Request;

class CategoryController
{

    public function create(Request $req){
        $this->validate($req, [
            "name" => [
                'required'
            ]
        ]);

        $category = Category::create($req->json()->all());
        return response()->json($category);
    }

    public function update(Request $req, $id){
        $this->validate($req, [
            "name" => [
                'required'
            ]
        ]);
        $category = Category::find($id);
        if($category != null){
           $category->name = $req->json('name');
           $category->save();
           return response()->json($category);
        }
        return response()->json([
            "error" => [
                "message" => "Categoria no encontrada"
            ]
        ], 500);
    }

    public function delete($id){
        $category = Category::find($id);
        if($category != null){
            $category->delete();
            return response()->json(["success" => "Categoria eliminada"]);
        }
        return response()->json([
            "error" => [
                "message" => "Categoria no encontrada"
            ]
        ], 500);
    }

    public function index(){
        $categories = Category::all();
        return response()->json($categories);
    }

    public function get($id){
        $result = Category::with('allies')->get()->find($id);
        return response()->json($result);
    }
}
