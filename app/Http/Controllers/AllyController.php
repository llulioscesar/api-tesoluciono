<?php


namespace App\Http\Controllers;


use App\Ally;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AllyController extends Controller
{

    public function create(Request $req){

        $this->validate($req, [
            'name' => 'required',
            'category_id' => [
                'required',
                Rule::exists('category', 'id')
            ]
        ]);

        $ally = Ally::create($req->json()->all());
        return response()->json($ally);
    }

    public function update(Request $req){

    }

    public function saveLogo(Request $req){
        if ($req->hasFile('logo')) {
            $image = $req->file('logo');
            $name = time().'.'.$image->getClientOriginalExtension();
            $req->file('logo')->move('logos', $name);
            return response()->json(['url'=>"api.tesoluciono.com.co/logos/" . $name]);
        }
        return response()->json(['message' => "Especifique el logo a cargar"], 500);
    }

}
