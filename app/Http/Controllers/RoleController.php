<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $query = Role::query();

        $query->where('name', '!=', 'master');

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
        $list = $query->offset($request['offset'])->limit($request['limit'])->get();

        return response()->json([
            'itens' => $list,
            'max' => $count
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        $model = Role::create($request->validated());

        if (isset($request->permissions)) {
            $permissions = Permission::whereIn('id', array_column($request->permissions, 'id'))->get();

            $model->syncPermissions($permissions);
        }

        return response()->json(
            'Grupo cadastrado com sucesso!!',
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
        $model = Role::with('permissions')->findOrFail($id);

        return response()->json($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, $id)
    {
        $model = Role::findOrFail($id);

        $model->fill($request->validated());

        if ($model->save()) {
            $permissions = [];

            if (isset($request->permissions)) {
                $permissions = Permission::whereIn('id', array_column($request->permissions, 'id'))->get();
            }

            $model->syncPermissions($permissions);

            return response()->json([
                'message' => 'Grupo atualizado com sucesso!!',
                'permissions' => $request->permissions
            ]);
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
        $model = Role::findOrFail($id);

        if ($model->delete()) {
            return response()->json('Grupo excluido com sucesso!!');
        } else {
            return response()->json('Erro ao excluir grupo!!');
        }
    }
}
