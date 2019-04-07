<?php


namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Bcrypt\Bcrypt;

class UserController extends Controller
{
    public function create(Request $req){

        $this->validate($req,[
            "username" => [
                'required',
                Rule::unique('user')
            ],
            "email" => [
                'required',
                Rule::unique('user')
            ],
            "password" => 'required'
        ]);


        $bcrypt = new Bcrypt();

        $user = new User();
        $user->username = $req->json('username');
        $user->email = $req->json('email');
        $user->password = $bcrypt->hash($req->json('password'));
        $user->save();
        return response()->json($user);
    }

    public function auth(Request $req){
        $this->validate($req, [
            "username" => [
                'required',
                Rule::exists('user', 'username')
            ],
            "password" => 'required'
        ]);

        $user = User::where('username', '=', $req->json('username'))->first();

        $bcrypt = new Bcrypt();
        if( $bcrypt->verify($req->json('password'), $user->password)){
            return response()->json($user);
        }else {
            return response()->json([
                "message" => "Contraseña errada"
            ]);
        }

    }

    public function update(Request $req, $id){
        $user = User::find($id);

        if ($user != null){
            if($req->json('email') != null){
                $user->email = $req->json('email');
            }
            if($req->json('username') != null){
                $user->username = $req->json('username');
            }
            if($req->json('password') != null){
                $bcrypt = new Bcrypt();
                $user->password = $bcrypt->hash($req->json('password'));
            }
            $user->save();
            return response()->json($user);
        }

        return response()->json([
            "message" => "El usuario no existe"
        ], 500);

    }

    public function delete($id){
        $user = User::find($id);
        $user->delete();

        return response()->json('Remove user success.');
    }

    public function index(){
        $users = User::all();
        return response()->json($users);
    }

}
