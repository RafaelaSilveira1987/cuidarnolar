<?php

namespace App\Controllers;

use App\Models\Dashboard;
use App\Models\Evento;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $dashboard = new Dashboard();
        $eventos = new Evento();

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'title' => 'Dashboard',
            'resumo' => $dashboard->resumo(),
            'alertasOperacionais' => $dashboard->alertasOperacionais(),
            'alertasFinanceiros' => $dashboard->alertasFinanceiros(),
            'operacaoHoje' => $dashboard->operacaoHoje(),
            'notificacoes' => $dashboard->notificacoes(), // compatibilidade com telas antigas
            'proximosEventos' => $eventos->proximos(),
        ]);
    }
}
