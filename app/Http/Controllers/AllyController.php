<?php


namespace App\Http\Controllers;


use App\Ally;
use function GuzzleHttp\Promise\all;
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

    public function update(Request $req, $id){
        $ally = Ally::find($id);
        if($ally != null){

            $json = $req->json()->all();

            if(array_key_exists('name', $json)){
                $ally->name = $json['name'];
            }
            if(array_key_exists('logo', $json)){
                $ally->logo = $json['logo'];
            }
            if(array_key_exists('enable', $json)){
                $ally->enable = $json['enable'];
            }
            if(array_key_exists('category_key', $json)){
                $this->validate($req,[
                    "category_id" => [
                        'required',
                        Rule::exists('category', 'id')
                    ]
                ]);
                $ally->category_id = $json['category_id'];
            }
            $ally->save();
            return response()->json($ally);
        }

        return response()->json([
            "error" => [
                "message" => "El aliado no existe"
            ]
        ], 500);
    }

    public function delete($id)
    {
        $ally = Ally::find($id);
        if ($ally != null) {
            $ally->delete();
            return response()->json(["success" => "Aliado eliminado"]);
        }
        return response()->json([
            "error" => [
                "message" => "El aliado no existe"
            ]
        ],500);
    }

    public function index(){
        $allies = Ally::all();
        return response()->json($allies);
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
