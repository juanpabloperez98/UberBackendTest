<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// Modelos
use App\Models\User;

// Clases propias
use App\Enums\RoleEnum;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    // Metodo de registrarse
    public function register(Request $request)
    {

        try {
            // Validar datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string',
                'role' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!in_array($value, RoleEnum::toArray())) {
                            $fail('El role ' . $attribute . ' no es valido.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 400);
            }

            // Buscar si ya existe un usuario con ese email
            $email = User::where('email', $request->email)->first();
            if ($email) {
                return response()->json([
                    'success' => false,
                    'data' => "Ya hay un usuario con este email"
                ], 400);
            }

            // Se crea el usuario nuevo
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Usuario registrado
            return response()->json([
                'success' => true,
                'data' => 'Usuario registrado con exito!!'
            ], 201);
        } catch (\Throwable $th) {
            // Manejo de error
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }
    }

    // Metodo de login
    public function login(Request $request)
    {

        try {
            // Validar datos
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 400);
            }
            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'data' => 'Unauthorized'
                ], 401);
            }

            // Genero el token
            $user = Auth::user();
            $token = $user->createToken('user-login')-> accessToken;

            // Respuesta
            return response()->json([
                'success' => true,
                'data' => $token
            ], 200);

        } catch (\Throwable $th) {
            // Manejo de error
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }
    }

    // Metodo de logout
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'success' => true,
            'data' => 'Sesión cerrada con exito!!'
        ], 200);
    }
}
