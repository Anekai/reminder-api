<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {   
        $query = Permission::query();

        if ($req['where']) {
            foreach ($req['where'] as $filter) {
                $where = json_decode($filter);
                if (isset($where->whereType) && $where->whereType == "or") {
                    $query->orWhere($where->field, $where->operator, $where->value);
                } else {
                    $query->where($where->field, isset($where->operator) ? $where->operator : '=', $where->value);
                }
            }
        }

        if ($req['orderby']) {
            foreach ($req['orderby'] as $orderby) {
                $order = json_decode($orderby);
                $query->orderBy($order->field, $order->type);
            }
        } else {
            $query->orderBy('name', 'ASC');
        }

        $count = $query->count();
        $list = $query->offset($req['offset'])->limit($req['limit'])->get();

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
    public function store(PermissionRequest $req)
    {
        Permission::create($req->validated());

        return response()->json(
            'Permissão cadastrada com sucesso!!',
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
        $model = Permission::findOrFail($id);

        return response()->json($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionRequest $request, $id)
    {
        $validated = $request->validated();

        $model = Permission::findOrFail($id);

        $model->fill($validated);

        if ($model->save()) {
            return response()->json('Permissão atualizada com sucesso!!');
        } else {
            return response()->json('Erro ao atualizar a permissão!!');
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
        $model = Permission::findOrFail($id);

        if ($model->delete()) {
            return response()->json('Permissão excluida com sucesso!!');
        } else {
            return response()->json('Erro ao excluir permissão!!');
        }
    }

    public function checkPermission(Request $request)
    {
        if (Auth::user()->hasRole('super') || 
            Auth::user()->can($request->permission)) {
            return response()->json(['message' => 'Autorizado'], 200);
        } else {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
    }
}
