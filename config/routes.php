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
$router->get('/pacientes/{id}', 'PacienteController@show', ['auth']);
$router->get('/pacientes/{id}/editar', 'PacienteController@edit', ['auth']);
$router->post('/pacientes/{id}', 'PacienteController@update', ['auth', 'csrf']);
$router->post('/pacientes/{id}/inativar', 'PacienteController@inativar', ['auth', 'csrf']);

$router->get('/responsaveis', 'ResponsavelController@index', ['auth']);
$router->get('/responsaveis/novo', 'ResponsavelController@create', ['auth']);
$router->post('/responsaveis', 'ResponsavelController@store', ['auth', 'csrf']);
$router->get('/responsaveis/{id}', 'ResponsavelController@show', ['auth']);
$router->get('/responsaveis/{id}/editar', 'ResponsavelController@edit', ['auth']);
$router->post('/responsaveis/{id}', 'ResponsavelController@update', ['auth', 'csrf']);
$router->post('/responsaveis/{id}/inativar', 'ResponsavelController@inativar', ['auth', 'csrf']);

$router->get('/cuidadores', 'CuidadorController@index', ['auth']);
$router->get('/cuidadores/novo', 'CuidadorController@create', ['auth']);
$router->post('/cuidadores', 'CuidadorController@store', ['auth', 'csrf']);
$router->get('/cuidadores/{id}', 'CuidadorController@show', ['auth']);
$router->get('/cuidadores/{id}/editar', 'CuidadorController@edit', ['auth']);
$router->post('/cuidadores/{id}', 'CuidadorController@update', ['auth', 'csrf']);
$router->post('/cuidadores/{id}/inativar', 'CuidadorController@inativar', ['auth', 'csrf']);

$router->get('/agendamentos', 'AgendamentoController@index', ['auth']);
$router->get('/agendamentos/novo', 'AgendamentoController@create', ['auth']);
$router->post('/agendamentos', 'AgendamentoController@store', ['auth', 'csrf']);
$router->get('/agendamentos/{id}', 'AgendamentoController@show', ['auth']);
$router->get('/agendamentos/{id}/editar', 'AgendamentoController@edit', ['auth']);
$router->post('/agendamentos/{id}', 'AgendamentoController@update', ['auth', 'csrf']);

$router->get('/diario-idoso', 'DiarioIdosoController@index', ['auth']);
$router->get('/diario-paciente', 'DiarioIdosoController@index', ['auth']);
$router->get('/diario-paciente/novo', 'DiarioIdosoController@create', ['auth']);
$router->post('/diario-paciente', 'DiarioIdosoController@store', ['auth', 'csrf']);
$router->get('/diario-paciente/{id}', 'DiarioIdosoController@show', ['auth']);
$router->get('/diario-paciente/{id}/editar', 'DiarioIdosoController@edit', ['auth']);
$router->post('/diario-paciente/{id}', 'DiarioIdosoController@update', ['auth', 'csrf']);

$router->get('/anamneses', 'AnamneseController@index', ['auth']);
$router->get('/anamneses/novo', 'AnamneseController@create', ['auth']);
$router->post('/anamneses', 'AnamneseController@store', ['auth', 'csrf']);
$router->get('/anamneses/{id}', 'AnamneseController@show', ['auth']);
$router->get('/anamneses/{id}/editar', 'AnamneseController@edit', ['auth']);
$router->post('/anamneses/{id}', 'AnamneseController@update', ['auth', 'csrf']);

$router->get('/historicos', 'HistoricoController@index', ['auth']);
$router->get('/historicos/novo', 'HistoricoController@create', ['auth']);
$router->post('/historicos', 'HistoricoController@store', ['auth', 'csrf']);
$router->get('/historicos/{id}', 'HistoricoController@show', ['auth']);
$router->get('/historicos/{id}/editar', 'HistoricoController@edit', ['auth']);
$router->post('/historicos/{id}', 'HistoricoController@update', ['auth', 'csrf']);

$router->get('/financeiro', 'FinanceiroController@index', ['auth']);
$router->get('/financeiro/novo', 'FinanceiroController@create', ['auth']);
$router->post('/financeiro', 'FinanceiroController@store', ['auth', 'csrf']);
$router->get('/financeiro/{id}', 'FinanceiroController@show', ['auth']);
$router->get('/financeiro/{id}/editar', 'FinanceiroController@edit', ['auth']);
$router->post('/financeiro/{id}', 'FinanceiroController@update', ['auth', 'csrf']);

$router->get('/relatorio-plantao', 'RelatorioPlantonController@index', ['auth']);
$router->get('/relatorio-plantao/paciente/{id}', 'RelatorioPlantonController@diario', ['auth']);

return $router;
