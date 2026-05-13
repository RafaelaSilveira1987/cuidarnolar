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
$router->get('/responsaveis/{id}', 'ResponsavelController@show', ['auth']);
$router->get('/responsaveis/{id}/editar', 'ResponsavelController@edit', ['auth']);

$router->get('/cuidadores', 'CuidadorController@index', ['auth']);
$router->get('/cuidadores/novo', 'CuidadorController@create', ['auth']);
$router->get('/cuidadores/{id}', 'CuidadorController@show', ['auth']);
$router->get('/cuidadores/{id}/editar', 'CuidadorController@edit', ['auth']);

$router->get('/agendamentos', 'AgendamentoController@index', ['auth']);
$router->get('/agendamentos/novo', 'AgendamentoController@create', ['auth']);
$router->get('/agendamentos/{id}', 'AgendamentoController@show', ['auth']);
$router->get('/agendamentos/{id}/editar', 'AgendamentoController@edit', ['auth']);

$router->get('/diario-idoso', 'DiarioIdosoController@index', ['auth']);
$router->get('/diario-idoso/novo', 'DiarioIdosoController@create', ['auth']);
$router->get('/diario-idoso/{id}', 'DiarioIdosoController@show', ['auth']);
$router->get('/diario-idoso/{id}/editar', 'DiarioIdosoController@edit', ['auth']);

$router->get('/anamneses', 'AnamneseController@index', ['auth']);
$router->get('/anamneses/novo', 'AnamneseController@create', ['auth']);
$router->get('/anamneses/{id}', 'AnamneseController@show', ['auth']);
$router->get('/anamneses/{id}/editar', 'AnamneseController@edit', ['auth']);

$router->get('/historicos', 'HistoricoController@index', ['auth']);
$router->get('/historicos/novo', 'HistoricoController@create', ['auth']);
$router->get('/historicos/{id}', 'HistoricoController@show', ['auth']);
$router->get('/historicos/{id}/editar', 'HistoricoController@edit', ['auth']);

$router->get('/financeiro', 'FinanceiroController@index', ['auth']);
$router->get('/financeiro/novo', 'FinanceiroController@create', ['auth']);
$router->get('/financeiro/{id}', 'FinanceiroController@show', ['auth']);
$router->get('/financeiro/{id}/editar', 'FinanceiroController@edit', ['auth']);

return $router;
