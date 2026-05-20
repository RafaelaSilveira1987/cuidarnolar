<?php

use App\Core\Router;

$router = new Router();

$router->get('/', 'DashboardController@index', ['auth']);
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', ['csrf']);
$router->post('/logout', 'AuthController@logout', ['auth', 'csrf']);
$router->get('/dashboard', 'DashboardController@index', ['auth']);

$router->get('/pacientes', 'PacienteController@index', ['auth']);
$router->get('/pacientes/novo', 'PacienteController@create', ['auth']);
$router->post('/pacientes', 'PacienteController@store', ['auth', 'csrf']);
$router->get('/pacientes/{uuid}', 'PacienteController@show', ['auth']);
$router->get('/pacientes/{uuid}/editar', 'PacienteController@edit', ['auth']);
$router->post('/pacientes/{uuid}', 'PacienteController@update', ['auth', 'csrf']);
$router->post('/pacientes/{uuid}/inativar', 'PacienteController@inativar', ['auth', 'csrf']);

$router->get('/responsaveis', 'ResponsavelController@index', ['auth']);
$router->get('/responsaveis/novo', 'ResponsavelController@create', ['auth']);
$router->post('/responsaveis', 'ResponsavelController@store', ['auth', 'csrf']);
$router->get('/responsaveis/{uuid}', 'ResponsavelController@show', ['auth']);
$router->get('/responsaveis/{uuid}/editar', 'ResponsavelController@edit', ['auth']);
$router->post('/responsaveis/{uuid}', 'ResponsavelController@update', ['auth', 'csrf']);
$router->post('/responsaveis/{uuid}/inativar', 'ResponsavelController@inativar', ['auth', 'csrf']);

$router->get('/cuidadores', 'CuidadorController@index', ['auth']);
$router->get('/cuidadores/novo', 'CuidadorController@create', ['auth']);
$router->post('/cuidadores', 'CuidadorController@store', ['auth', 'csrf']);
$router->get('/cuidadores/{uuid}', 'CuidadorController@show', ['auth']);
$router->get('/cuidadores/{uuid}/editar', 'CuidadorController@edit', ['auth']);
$router->post('/cuidadores/{uuid}', 'CuidadorController@update', ['auth', 'csrf']);
$router->post('/cuidadores/{uuid}/inativar', 'CuidadorController@inativar', ['auth', 'csrf']);

$router->get('/agendamentos', 'AgendamentoController@index', ['auth']);
$router->get('/agendamentos/novo', 'AgendamentoController@create', ['auth']);
$router->post('/agendamentos', 'AgendamentoController@store', ['auth', 'csrf']);
$router->get('/agendamentos/{uuid}', 'AgendamentoController@show', ['auth']);
$router->get('/agendamentos/{uuid}/editar', 'AgendamentoController@edit', ['auth']);
$router->post('/agendamentos/{uuid}', 'AgendamentoController@update', ['auth', 'csrf']);

$router->get('/diario-idoso', 'DiarioIdosoController@index', ['auth']);
$router->get('/diario-paciente', 'DiarioIdosoController@index', ['auth']);
$router->get('/diario-paciente/novo', 'DiarioIdosoController@create', ['auth']);
$router->post('/diario-paciente', 'DiarioIdosoController@store', ['auth', 'csrf']);
$router->get('/diario-paciente/{uuid}', 'DiarioIdosoController@show', ['auth']);
$router->get('/diario-paciente/{uuid}/editar', 'DiarioIdosoController@edit', ['auth']);
$router->post('/diario-paciente/{uuid}', 'DiarioIdosoController@update', ['auth', 'csrf']);

$router->get('/anamneses', 'AnamneseController@index', ['auth']);
$router->get('/anamneses/novo', 'AnamneseController@create', ['auth']);
$router->post('/anamneses', 'AnamneseController@store', ['auth', 'csrf']);
$router->get('/anamneses/{uuid}', 'AnamneseController@show', ['auth']);
$router->get('/anamneses/{uuid}/editar', 'AnamneseController@edit', ['auth']);
$router->post('/anamneses/{uuid}', 'AnamneseController@update', ['auth', 'csrf']);

$router->get('/historicos', 'HistoricoController@index', ['auth']);
$router->get('/historicos/novo', 'HistoricoController@create', ['auth']);
$router->post('/historicos', 'HistoricoController@store', ['auth', 'csrf']);
$router->get('/historicos/{uuid}', 'HistoricoController@show', ['auth']);
$router->get('/historicos/{uuid}/editar', 'HistoricoController@edit', ['auth']);
$router->post('/historicos/{uuid}', 'HistoricoController@update', ['auth', 'csrf']);

$router->get('/financeiro', 'FinanceiroController@hub', ['auth']);
$router->get('/financeiro/lancamentos', 'FinanceiroController@lancamentos', ['auth']);
$router->get('/financeiro/contas-receber', 'FinanceiroController@contasReceber', ['auth']);
$router->get('/financeiro/contas-pagar', 'FinanceiroController@contasPagar', ['auth']);
$router->get('/financeiro/contratos', 'FinanceiroController@contratos', ['auth']);
$router->get('/financeiro/contratos/novo', 'FinanceiroController@contratoNovo', ['auth']);
$router->post('/financeiro/contratos', 'FinanceiroController@contratoStore', ['auth', 'csrf']);
$router->get('/financeiro/relatorios/extrato', 'FinanceiroController@relatorioExtrato', ['auth']);
$router->get('/financeiro/relatorios/fluxo-caixa', 'FinanceiroController@relatorioFluxoCaixa', ['auth']);
$router->get('/financeiro/relatorios/inadimplencia', 'FinanceiroController@relatorioInadimplencia', ['auth']);
$router->get('/financeiro/relatorios/dre', 'FinanceiroController@relatorioDre', ['auth']);
$router->get('/financeiro/novo', 'FinanceiroController@create', ['auth']);
$router->post('/financeiro', 'FinanceiroController@store', ['auth', 'csrf']);
$router->get('/financeiro/{uuid}', 'FinanceiroController@show', ['auth']);
$router->get('/financeiro/{uuid}/editar', 'FinanceiroController@edit', ['auth']);
$router->post('/financeiro/{uuid}', 'FinanceiroController@update', ['auth', 'csrf']);

$router->get('/relatorio-plantao', 'RelatorioPlantaoController@index', ['auth']);
$router->get('/relatorio-plantao/novo', 'RelatorioPlantaoController@create', ['auth']);
$router->get('/relatorio-plantao/paciente/{uuid}/novo', 'RelatorioPlantaoController@create', ['auth']);
$router->get('/relatorio-plantao/paciente/{uuid}', 'RelatorioPlantaoController@paciente', ['auth']);
$router->get('/relatorios-plantao/{uuid}', 'RelatorioPlantaoController@paciente', ['auth']);

// routes.php
$router->post('/relatorio-plantao/paciente/{uuid}/store', 'RelatorioPlantaoController@store', ['auth', 'csrf']);
$router->post('/relatorio-plantao', 'RelatorioPlantaoController@store', ['auth', 'csrf']);

/* VISUALIZAR RELATÓRIO */
$router->get('/relatorio-plantao/plantao/{uuid}', 'RelatorioPlantaoController@show', ['auth']);

/* EDITAR RELATÓRIO */
$router->get('/relatorio-plantao/plantao/{uuid}/editar', 'RelatorioPlantaoController@edit', ['auth']);
$router->post('/relatorio-plantao/plantao/{uuid}/atualizar', 'RelatorioPlantaoController@update', ['auth', 'csrf']);
$router->post('/relatorio-plantao/update/{uuid}', 'RelatorioPlantaoController@update', ['auth', 'csrf']);

return $router;