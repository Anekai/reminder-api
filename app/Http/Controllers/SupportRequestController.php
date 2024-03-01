<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportRequestRequest;
use App\Mail\NovaSolicitacaoSuporteMail;
use App\Models\SupportRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportRequestController extends Controller
{

    public function index(Request $request)
    {
        $query = SupportRequest::query();

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

        $solicitacoes = SupportRequest::
            where('status', 'EM_ANDAMENTO')->
            orderBy('start_date', 'ASC')->
            get();

        if ($request['orderby']) {
            foreach ($request['orderby'] as $orderby) {
                $order = json_decode($orderby);
                $query->orderBy($order->field, $order->type);
            }
        } else {
            $query->orderBy('start_date', 'ASC');
        }

        $count = $query->count();
        $result = $query->offset($request['offset'])->limit($request['limit'])->get();
        
        return response()->json([
            'itens' => $result,
            'max' => $count,
            'teste' =>$solicitacoes
        ]);
    }

    public function store(SupportRequestRequest $request)
    {
        $validated = $request->validated();
        $validated['status'] = 'ABERTA';
        $validated['user_id'] = Auth::user()->id;

        SupportRequest::create($validated);

        Mail::
            to(env('EMAIL_SUPORTE'))->
            send(new NovaSolicitacaoSuporteMail(
                Auth::user()->name,
                $validated['title'],
                $validated['description'],
                $validated['type'],
                $validated['priority']
            ));

        return response()->json('Solicitação cadastrada com sucesso!!');
    }

    public function show($id)
    {
        $model = SupportRequest::findOrFail($id);

        return response()->json($model);
    }

    public function update(SupportRequestRequest $request, $id)
    {
        $validated = $request->validated();

        $model = SupportRequest::findOrFail($id);

        $model->fill($validated);

        if ($model->save()) {
            return response()->json('Solicitação atualizada com sucesso!!');
        } else {
            return response()->json('Erro ao atualizar a solicitação!!');
        }
    }

    public function destroy($id)
    {
        $model = SupportRequest::findOrFail($id);

        $model->status = 'CANCELADA';
        $model->cancellation_date = Carbon::now();

        if ($model->save()) {
            return response()->json('Solicitação cancelada com sucesso!!');
        } else {
            return response()->json('Erro ao cancelar a solicitação!!');
        }
    }

    public function concluirSolicitacao(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'response' => 'required',
        ], [
            'response.required' => 'A resposta da solicitação deve ser informada',
        ])->validate();

        $validated['status'] = 'CONCLUIDA';
        $validated['conclusion_date'] = Carbon::now();

        $model = SupportRequest::findOrFail($id);

        $model->fill($validated);

        if ($model->save()) {
            return response()->json('Solicitação concluida com sucesso!!');
        } else {
            return response()->json('Erro ao concluir a solicitação!!');
        }
    }

    public function recusarSolicitacao(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'reason_refusal' => 'required',
        ], [
            'reason_refusal.required' => 'O motivo do recuso da solicitação deve ser informado',
        ])->validate();

        $validated['support_user_id'] = Auth::user()->id;
        $validated['status'] = 'RECUSADA';
        $validated['refusal_date'] = Carbon::now();

        $model = SupportRequest::findOrFail($id);

        $model->fill($validated);

        if ($model->save()) {
            return response()->json('Solicitação recusada com sucesso!!');
        } else {
            return response()->json('Erro ao recusar a solicitação!!');
        }
    }

    public function iniciarSolicitacao($id)
    {
        $model = SupportRequest::findOrFail($id);

        $model['support_user_id'] = Auth::user()->id;
        $model['status'] = 'EM_ANDAMENTO';
        $validated['start_date'] = Carbon::now();

        if ($model->save()) {
            return response()->json('Solicitação iniciada com sucesso!!');
        } else {
            return response()->json('Erro ao iniciar a solicitação!!');
        }
    }

    public function solicitacoesUsuario(Request $request)
    {
        $user = Auth::user();

        $query = SupportRequest::query();

        $query->where('user_id', $user->id);

        if ($request['status']) {
            $query->where('status', $request['status']);
        }

        if ($request['type']) {
            $query->where('type', $request['type']);
        }

        if ($request['priority']) {
            $query->where('priority', $request['priority']);
        }

        if ($request['dataAberturaInicial']) {
            $query->where('created_at', '>=', $request['dataAberturaInicial']);
        }
        
        if ($request['dataAberturaFinal']) {
            $query->where('created_at', '<=', $request['dataAberturaInicial']);
        }

        $query->orderBy('start_date', 'ASC');
        
        $result = $query->get();
        
        return response()->json($result);
    }

    public function monitoramentoSolicitacoes()
    {
        $user = Auth::user();

        $quantidadeSolicitacoes = SupportRequest::count();
        $quantidadeSolicitacoesAbertas = SupportRequest::where('status', 'ABERTA')->count();
        $quantidadeSolicitacoesEmAndamento = SupportRequest::where('status', 'EM_ANDAMENTO')->count();
        $quantidadeSolicitacoesConcluidas = SupportRequest::where('status', 'CONCLUIDA')->count();
        $quantidadeSolicitacoesCanceladas = SupportRequest::where('status', 'CANCELADA')->count();
        $quantidadeSolicitacoesRecusadas = SupportRequest::where('status', 'RECUSADA')->count();

        $solicitacoesEmAndamento = SupportRequest::
            with('usuario')->
            where('status', 'EM_ANDAMENTO')->
            where('support_user_id', $user->id)->
            orderBy('start_date', 'ASC')->
            get();

        $solicitacoesAbertas = SupportRequest::
            with('usuario')->
            where('status', 'ABERTA')->
            orderBy('created_at', 'ASC')->
            get();
        
        return response()->json([
            'quantidadeSolicitacoes'            => $quantidadeSolicitacoes,
            'quantidadeSolicitacoesAbertas'     => $quantidadeSolicitacoesAbertas,
            'quantidadeSolicitacoesEmAndamento' => $quantidadeSolicitacoesEmAndamento,
            'quantidadeSolicitacoesConcluidas'  => $quantidadeSolicitacoesConcluidas,
            'quantidadeSolicitacoesCanceladas'  => $quantidadeSolicitacoesCanceladas,
            'quantidadeSolicitacoesRecusadas'   => $quantidadeSolicitacoesRecusadas,
            'solicitacoesEmAndamento'           => $solicitacoesEmAndamento,
            'solicitacoesAbertas'               => $solicitacoesAbertas,
        ]);
    }
}
