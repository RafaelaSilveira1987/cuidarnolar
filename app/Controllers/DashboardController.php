<?php

namespace App\Controllers;

use App\Models\Dashboard;
use App\Models\Evento;
use App\Models\Financeiro;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $dashboard = new Dashboard();
        $financeiro = new Financeiro();
        $eventos = new Evento();

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'resumo' => $dashboard->resumo(),
            'notificacoes' => $dashboard->notificacoes(),
            'financeiro' => $financeiro->resumo(),
            'proximosEventos' => $eventos->proximos(),
        ]);
    }
}
