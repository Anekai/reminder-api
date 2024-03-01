<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $query = User::query();

        $query->whereHas("roles", function($q) {
            $q->whereNotIn("name", ["master"]);
        });

        if ($request['where']) {
            foreach ($request['where'] as $filter) {
                $where = json_decode($filter);
                if (isset($where->whereType) && $where->whereType == "or") {
                    $query->orWhere($where->field, isset($where->operator) ? $where->operator : '=', $where->value);
                } else {
                    $query->where($where->field, isset($where->operator) ? $where->operator : '=', $where->value);
                }
            }
        }

        if ($request['orderby']) {
            foreach ($request['orderby'] as $orderby) {
                $order = json_decode($orderby);
                $query->orderBy($order->field, $order->type);
            }
        } else {
            $query->orderBy('name', 'ASC');
        }

        $count = $query->count();
        $usuarios = $query->offset($request['offset'])->limit($request['limit'])->get();
        
        return response()->json([
            'itens' => $usuarios,
            'max' => $count
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {   
        $validated = $request->validated();
        $validated['password'] = bcrypt($request->password);

        $role = Role::findOrFail($validated['role_id'])->name;

        User::create($validated)->assignRole($role);
        
        return response()->json(
            'Usuário cadastrado com sucesso!!',
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = User::findOrFail($id);

        $model->role_id = $model->roles[0]->id;

        return response()->json($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);

        if ($request->password) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $role = Role::findOrFail($validated['role_id'])->name;

        $updateRole = $user->roles[0]->name != $role;

        $user->fill($validated);

        if ($user->save()) {
            if ($updateRole) {
                $user->syncRoles([$role]);
            }
            return response()->json('Usuário atualizado com sucesso!!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        $user = User::findOrFail($id);

        if ($user->delete()) {
            return response()->json('Usuário excluido com sucesso!!');
        } else {
            return response()->json('Erro ao excluir usuário!!');
        }
    }

    public function dadosUsuario()
    {
        $usuario = User::findOrFail(Auth::id());

        return response()->json([
            'success' => true,
            'usuario' => $usuario
        ]);
    }

    public function atualizarDadosUsuario(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'name'  => ['required'],
            'email' => ['required', 'email', 'unique:users,email,' . $userId],
        ],
        [
            'name.required'  => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email'    => 'O email deve ser válido.',
            'email.unique'   => 'Este email já está cadastrado.',
        ]);

        $user = User::findOrFail($userId);

        $user->fill($validated);

        if ($user->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!!',
            ]);
        }
    }
}
