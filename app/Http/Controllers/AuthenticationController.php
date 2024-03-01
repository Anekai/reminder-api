<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Rules\ValidatePassword;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthenticationController extends Controller
{
    protected $login = 'login';

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email deve ser válido',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha teve ter no mínimo 8 caracteres',
            'password.unique' => 'Já existe um usuário com este email',
            'password.confirmed' => 'A confirmação da senha está incorreta',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        if (Auth::attempt($request->only('email', 'password'), $request['remember_token'])) {
            $user = Auth::user();

            $success['token'] = $user->createToken('Reminder')->plainTextToken;
            $success['name']  = $user->name;

            return response()->json($success, 200)->withCookie(cookie('token', $success['token'], 0, null, null, false, true, false, 'none'));
        } else {
            return response()->json([
                'message' => 'Erro ao efetuar o login'
            ], 404);
        }
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            if (Auth::attempt($request->only('email', 'password'), $request['remember_token'])) {
                $user = Auth::user();
    
                $success['token'] = $user->createToken('Reminder')->plainTextToken;
                $success['name']  = $user->name;
    
                return response()->json($success, 200)->withCookie(cookie('token', $success['token'], 0, null, null, false, true, false, 'none'));
            } else {
                return response()->json([
                    'message' => 'Login ou senha inválidos'
                ], 404);
            }
        } catch(Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Usuário deslogado'
        ], 200);
    }

    public function getAuthenticatedUser() {
        $user = Auth::user();

        if ($user->hasRole('usuario')) {
            $user->homePage = '/cadastro-solicitacoes';
        } else {
            $user->homePage = '/painel-monitoramento';
        }

        return response()->json($user, 200);
    }

    public function checkAuthentication() {
        return response()->json('Autenticado', 200);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => ['required', new ValidatePassword],
            'new_password' => ['required', 'min:8'],
            'new_confirm_password' => ['same:new_password'],
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'new_password.required' => 'Informe a nova senha.',
            'new_password.min' => 'A nova senha deve conter no mínimo 8 caracteres.',
            'new_confirm_password.same' => 'A confirmação da senha está incorreta.',
        ]);
    
        $user = User::findOrFail(Auth::id());
    
        $user->update(['password' => bcrypt($request['new_password'])]);
    
        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso.'
        ],200);
    }

    public function forgotPassword(Request $request) {
        $request->validate([
            'login' => ['required']
        ], [
            'login.required' => 'O email é obrigatório.'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $status = Password::sendResetLink(
                ['email' => $user->email]
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Email de recuperação de senha enviado com sucesso.'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Erro ao enviar email de recuperação de senha.',
                    'error'   => $status
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }
    }

    public function recoverPassword(Request $request) {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required',
            'password'              => 'required|min:8',
            'password_confirmation' => 'same:password',
        ], [
            'token.required'             => 'O token é obrigatório.',
            'email.required'             => 'O email é obrigatório.',
            'password.required'          => 'A senha é obrigatória.',
            'password.min'               => 'A nova senha deve conter no mínimo 8 caracteres.',
            'password_confirmation.same' => 'A confirmação da senha está incorreta.',
        ]);
     
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );
     
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Senha alterada com sucesso.'
            ], 200);
        } else if ($status === Password::INVALID_TOKEN) {
            return response()->json([
                'message' => 'Token inválido.',
            ], 422);
        } else {
            return response()->json([
                'message' => 'Token inválido.',
            ], 400);
        }
    }

    public function menu()
    {
        $user = Auth::user();
        $menu = [];
        $isSuperUser = $user->hasRole('super');
        
        $administration = [];

//        $menu[] = [
//            "label" => "Home",
//            "icon" => "fas fa-home",
//            "path" => "/home",
//        ];

        if ($isSuperUser || $user->can('painel-monitoramento')) {
            $menu[] = [
                "label" => "Monitoramento",
                "icon" => "fas fa-chart-bar",
                "path" => "/painel-monitoramento"
            ];
        }

        if ($isSuperUser || $user->can('cadastro-solicitacoes')) {
            $menu[] = [
                "label" => "Solicitações",
                "icon" => "fas fa-plus",
                "path" => "/cadastro-solicitacoes"
            ];
        }

        if ($isSuperUser || $user->can('cadastro-usuarios')) {
            $administration[] = [
                "label" => "Usuários",
                "path" => "/admin/users/list"
            ];
        }

        if ($isSuperUser) {
            $administration[] = [
                "label" => "Grupos",
                "path" => "/admin/roles/list"
            ];
        }

        if ($isSuperUser) {
            $administration[] = [
                "label" => "Permissões",
                "path" => "/admin/permissions/list"
            ];
        }

        if ($administration) {
            $menu[] = [
                "label" => "Administração",
                "icon" => "fas fa-user-cog",
                "submenus" => $administration
            ];
        }

        return response()->json([
            'menu' => $menu
        ], 200);
    }

}