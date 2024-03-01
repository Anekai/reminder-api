<?php

namespace Database\Seeders;

use App\Models\SupportRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SupportRequestSeeder extends Seeder
{
    
    public function run()
    {
        SupportRequest::create([
            'title' => 'Falha ao logar no sistema',
            'description' => 'Não consigo logar no sistema.',
            'type' => 'ERRO',
            'priority' => 'URGENTE',
            'status' => 'ABERTA',
            'user_id' => 3,
        ]);

        SupportRequest::create([
            'title' => 'Não consigo recuperar minha senha',
            'description' => 'Estou tentando recuperar minha senha mas não estou recebendo o email de recuperação.',
            'type' => 'ERRO',
            'priority' => 'URGENTE',
            'status' => 'EM_ANDAMENTO',
            'user_id' => 3,
            'support_user_id' => 2,
            'start_date' => Carbon::now(),
        ]);

        SupportRequest::create([
            'title' => 'Não consigo ligar meu computador',
            'description' => 'Não estou conseguindo ligar meu computador.',
            'response' => 'Computador não estava ligado na tomada.',
            'type' => 'ERRO',
            'priority' => 'URGENTE',
            'status' => 'CONCLUIDA',
            'user_id' => 3,
            'support_user_id' => 2,
            'start_date' => Carbon::now(),
            'conclusion_date' => Carbon::now(),
        ]);
    }
    
}
