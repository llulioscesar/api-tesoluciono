<?php


namespace App\Http\Controllers;

use App\Token;
use App\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Bcrypt\Bcrypt;
use PHPMailer\PHPMailer\PHPMailer;

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

            $payload = [
                'iss' => 'auth',
                'sub' => $user,
                'iat' => time(),
                'exp' => time() + 15 * 60
            ];

            $token = JWT::encode($payload, env('JWT_SECRET'));

            return response()->json([
                "token" => $token,
                "user" => $user,
                "resset" => $user->reset_pass
            ]);
        }else {
            return response()->json([
                "error" => [
                    "message" => "Contraseña errada"
                ]
            ]);
        }
    }

    public function renew(Request $req){

        $this->validate($req, [
            "username" => 'required',
            "password" => 'required'
        ]);

        $token = $req->get('Authorization');
        if(!$token){
            return response()->json([
                "error" => [
                    "message" => "Token no proporcionado."
                ]
            ], 401);
        }

        $user = User::where('username', '=', $req->json('username'))->first();
        if($user != null){
            $bcrypt = new Bcrypt();
            if($bcrypt->verify($req->json('password'), $user->password)){
                try{
                    $credential = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
                    return response()->json([
                        "error" => [
                            "message" => "Token valido"
                        ]
                    ], 400);
                }catch (ExpiredException $exception){
                    $token = Token::where('token', '=', $token)->first();
                    if($token == null){
                        Token::create(["token" => $token]);
                        $payload = [
                            'iss' => 'auth',
                            'sub' => $user,
                            'iat' => time(),
                            'exp' => time() + 15 * 60
                        ];
                        $token = JWT::encode($payload, env('JWT_SECRET'));
                        return response()->json([
                            "token" => $token
                        ]);
                    }else {
                        return response()->json([
                            "error" => [
                                "message" => "El token ya fue renovado"
                            ]
                        ], 400);
                    }
                } catch (Exception $exception){
                    return response()->json([
                        "error" => [
                            "message" => "Un error al decodificar el token."
                        ]
                    ], 400);
                }
            } else {
                return response()->json([
                    "error" => [
                        "message" => "No se puede renovar, contraseña incorrecta"
                    ]
                ], 400);
            }
        }else {
            return response()->json([
                "error" => [
                    "message" => "No existe el usuario"
                ]
            ], 400);
        }
    }

    public function update(Request $req, $id){
        $user = User::find($id);

        if ($user != null){

            $json = $req->json()->all();

            if(array_key_exists('email', $json)){
                $user->email = $json['email'];
            }
            if(array_key_exists('username', $json)){
                $user->username = $json['username'];
            }
            if(array_key_exists('password', $json)){
                $bcrypt = new Bcrypt();
                $user->password = $bcrypt->hash($json['password']);
            }
            $user->save();
            return response()->json($user);
        }

        return response()->json([
            "error" => [
                "message" => "El usuario no existe"
            ]
        ], 500);

    }

    public function delete($id)
    {
        $user = User::find($id);
        if ($user != null) {
            $user->delete();

            return response()->json(["success" => 'Usuario eliminado']);
        }
        return response()->json([
            "error" => [
                "message" => "El usuario no existe"
            ]
        ]);
    }

    public function index(){
        $users = User::all();
        return response()->json($users);
    }

    public function resetPass(Request $req){
        $this->validate($req, [
            'email' => 'required',
            "username" => 'required'
        ]);

        $user = User::where('email', '=', $req->json('email'))->first();
        if($user != null) {

            $payload = [
                'iss' => 'password',
                'sub' => $user,
                'iat' => time(),
                'exp' => time() + (24 * (60 * 60))
            ];

            $token = JWT::encode($payload, env('JWT_SECRET'));

            $mail = new PHPMailer;

            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'norespondertesoluciono@gmail.com';   // SMTP username
            $mail->Password = '*tesoluciono';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable encryption, only 'tls' is accepted
            $mail->Port = 587;


            $mail->From = 'norespondertesoluciono@gmail.com';
            $mail->FromName = 'Te Soluciono';
            $mail->addAddress($user->email);                             // Set word wrap to 50 characters

            $mail->Subject = 'Restablecer contraseña';
            $mail->WordWrap = 50;                            // Set word wrap to 50 characters
            $mail->Body = 'Click <a href="https://api.tesoluciono.com.co/v1/auth/password/'.$token.'">AQUI</a> para recuperar la contraseña. Esta solicitud tiene validez de 24 horas';
            $mail->isHTML(true);                             // Set email format to HTML
            $mail->CharSet = "UTF-8";

            if(!$mail->send()) {
                return response()->json([
                    'error' => [
                        'message' => $mail->ErrorInfo
                    ]
                ], 500);
            } else {
                return response()->json([
                    'success' => 'Correo enviado, revise su bandeja de entrada o spam'
                ]);
            }

            //noresponder@api.tesoluciono.com.co
            //G]Psh,kIs?-m
        } else {
            return response()->json([
                'error' => [
                    'message' => 'No se encontro el usuario'
                ]
            ], 401);
        }
    }

    public function enableResetPass(Request $req, $token){
        try{
            $credential = JWT::decode($token, env('JWT_SECRET'), ['HS256']);

            $json = json_decode(json_encode($credential->sub));

            $user = User::find($credential->sub->id);

            $user->reset_pass = true;
            $user->save();


            return redirect('https://admin.tesoluciono.com.co/password?id='.$user->id);

        }catch (ExpiredException $exception){
            return response()->json([
                "error" => [
                    "message" => "La solicitud ha caducado."
                ]
            ], 500);
        } catch (Exception $exception){
            return response()->json([
                "error" => [
                    "message" => "Error al obtener la solicitud"
                ]
            ], 500);
        }
    }

    public function newPass(Request $req, $id){
        $this->validate($req,[
            'password' => [
                'required'
            ]
        ]);

        $user = User::find($id);
        if($user != null){
            if($user->reset_pass == 1){
                $bcrypt = new Bcrypt();
                $user->password = $bcrypt->hash($req->json('password'));
                $user->save();
                return response()->json([
                    "success" => 'Contraseña actualizada'
                ]);
            }else{
                return response()->json([
                   'error' => [
                       'message' => 'El usuario no esta autorizado para crear una nueva contraseña'
                   ]
                ], 401);
            }
        }

        return response()->json([
            'error' => [
                'message' => 'No se encontro el usuario'
            ]
        ], 404);
    }

}
