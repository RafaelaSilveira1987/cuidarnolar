<?php

use App\Core\Router;

$router = new Router();

$router->get('/', 'DashboardController@index', ['auth']);
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', ['csrf']);
$router->post('/logout', 'AuthController@logout', ['auth', 'csrf']);
$router->get('/dashboard', 'DashboardController@index', ['auth']);

$router->get('/pacientes', 'PacienteController@index', ['auth', 'can:pacientes.ver']);
$router->get('/pacientes/novo', 'PacienteController@create', ['auth', 'can:pacientes.criar']);
$router->post('/pacientes', 'PacienteController@store', ['auth', 'csrf', 'can:pacientes.criar']);
$router->get('/pacientes/{uuid}', 'PacienteController@show', ['auth', 'can:pacientes.ver']);
$router->get('/pacientes/{uuid}/editar', 'PacienteController@edit', ['auth', 'can:pacientes.editar']);
$router->post('/pacientes/{uuid}', 'PacienteController@update', ['auth', 'csrf', 'can:pacientes.editar']);
$router->post('/pacientes/{uuid}/inativar', 'PacienteController@inativar', ['auth', 'csrf', 'can:pacientes.inativar']);
$router->post('/pacientes/{uuid}/escala-base', 'PacienteController@salvarEscalaBase', ['auth', 'csrf', 'can:escala.editar']);
$router->post('/pacientes/{uuid}/escala-base/reaplicar', 'PacienteController@reaplicarEscalaBase', ['auth', 'csrf', 'can:escala.editar']);

$router->get('/pacientes/{uuid}/planos/novo', 'PacienteController@planoNovo', ['auth', 'can:planos.criar']);
$router->post('/pacientes/{uuid}/planos', 'PacienteController@planoStore', ['auth', 'csrf', 'can:planos.criar']);
$router->get('/pacientes/{uuid}/planos/{planoUuid}/pdf', 'PacienteController@planoPdf', ['auth', 'can:planos.pdf']);
$router->get('/pacientes/{uuid}/planos/{planoUuid}/editar', 'PacienteController@planoEditar', ['auth', 'can:planos.editar']);
$router->post('/pacientes/{uuid}/planos/{planoUuid}', 'PacienteController@planoUpdate', ['auth', 'csrf', 'can:planos.editar']);
$router->post('/pacientes/{uuid}/planos/{planoUuid}/ativar', 'PacienteController@planoAtivar', ['auth', 'csrf', 'can:planos.ativar']);
$router->post('/pacientes/{uuid}/planos/{planoUuid}/arquivar', 'PacienteController@planoArquivar', ['auth', 'csrf', 'can:planos.editar']);

$router->get('/pacientes/{uuid}/contratos/novo', 'PacienteController@contratoNovo', ['auth', 'can:contratos.criar']);
$router->post('/pacientes/{uuid}/contratos', 'PacienteController@contratoStore', ['auth', 'csrf', 'can:contratos.criar']);
$router->get('/pacientes/{uuid}/contratos/{contratoUuid}/editar', 'PacienteController@contratoEditar', ['auth', 'can:contratos.editar']);
$router->post('/pacientes/{uuid}/contratos/{contratoUuid}', 'PacienteController@contratoUpdate', ['auth', 'csrf', 'can:contratos.editar']);
$router->post('/pacientes/{uuid}/contratos/{contratoUuid}/gerar-financeiro', 'PacienteController@contratoGerarFinanceiro', ['auth', 'csrf', 'can:contratos.gerar_financeiro']);


/*
|--------------------------------------------------------------------------
| MEDICAÇÕES DO PACIENTE
|--------------------------------------------------------------------------
*/

$router->get('/pacientes/{uuid}/medicacoes', 'MedicacaoPacienteController@index', ['auth', 'can:medicacoes.ver']);
$router->get('/pacientes/{uuid}/medicacoes/novo', 'MedicacaoPacienteController@create', ['auth', 'can:medicacoes.editar']);
$router->post('/pacientes/{uuid}/medicacoes', 'MedicacaoPacienteController@store', ['auth', 'csrf', 'can:medicacoes.editar']);
$router->get('/medicacoes/{uuid}/editar', 'MedicacaoPacienteController@edit', ['auth', 'can:medicacoes.editar']);
$router->post('/medicacoes/{uuid}', 'MedicacaoPacienteController@update', ['auth', 'csrf', 'can:medicacoes.editar']);
$router->post('/medicacoes/{uuid}/inativar', 'MedicacaoPacienteController@inativar', ['auth', 'csrf', 'can:medicacoes.editar']);
$router->post('/medicacoes/{uuid}/delete', 'MedicacaoPacienteController@destroy', ['auth', 'csrf', 'can:medicacoes.editar']);

$router->get('/responsaveis', 'ResponsavelController@index', ['auth', 'can:responsaveis.ver']);
$router->get('/responsaveis/novo', 'ResponsavelController@create', ['auth', 'can:responsaveis.criar']);
$router->post('/responsaveis', 'ResponsavelController@store', ['auth', 'csrf', 'can:responsaveis.criar']);
$router->get('/responsaveis/{uuid}', 'ResponsavelController@show', ['auth', 'can:responsaveis.ver']);
$router->get('/responsaveis/{uuid}/editar', 'ResponsavelController@edit', ['auth', 'can:responsaveis.editar']);
$router->post('/responsaveis/{uuid}', 'ResponsavelController@update', ['auth', 'csrf', 'can:responsaveis.editar']);
$router->post('/responsaveis/{uuid}/inativar', 'ResponsavelController@inativar', ['auth', 'csrf', 'can:responsaveis.editar']);

$router->get('/cuidadores', 'CuidadorController@index', ['auth', 'can:cuidadores.ver']);
$router->get('/cuidadores/novo', 'CuidadorController@create', ['auth', 'can:cuidadores.criar']);
$router->post('/cuidadores', 'CuidadorController@store', ['auth', 'csrf', 'can:cuidadores.criar']);
$router->get('/cuidadores/{uuid}', 'CuidadorController@show', ['auth', 'can:cuidadores.ver']);
$router->get('/cuidadores/{uuid}/editar', 'CuidadorController@edit', ['auth', 'can:cuidadores.editar']);
$router->post('/cuidadores/{uuid}', 'CuidadorController@update', ['auth', 'csrf', 'can:cuidadores.editar']);
$router->post('/cuidadores/{uuid}/inativar', 'CuidadorController@inativar', ['auth', 'csrf', 'can:cuidadores.editar']);

$router->get('/agendamentos', 'AgendamentoController@index', ['auth', 'can:agenda.ver']);
$router->get('/agendamentos/novo', 'AgendamentoController@create', ['auth', 'can:agenda.editar']);
$router->post('/agendamentos', 'AgendamentoController@store', ['auth', 'csrf', 'can:agenda.editar']);
$router->get('/agendamentos/{uuid}', 'AgendamentoController@show', ['auth', 'can:agenda.ver']);
$router->get('/agendamentos/{uuid}/editar', 'AgendamentoController@edit', ['auth', 'can:agenda.editar']);
$router->post('/agendamentos/{uuid}', 'AgendamentoController@update', ['auth', 'csrf', 'can:agenda.editar']);

$router->get('/diario-idoso', 'DiarioIdosoController@index', ['auth', 'can:diario.ver']);
$router->get('/diario-paciente', 'DiarioIdosoController@index', ['auth', 'can:diario.ver']);
$router->get('/diario-paciente/novo', 'DiarioIdosoController@create', ['auth', 'can:diario.editar']);
$router->post('/diario-paciente', 'DiarioIdosoController@store', ['auth', 'csrf', 'can:diario.editar']);
$router->get('/diario-paciente/{uuid}', 'DiarioIdosoController@show', ['auth', 'can:diario.ver']);
$router->get('/diario-paciente/{uuid}/editar', 'DiarioIdosoController@edit', ['auth', 'can:diario.editar']);
$router->post('/diario-paciente/{uuid}', 'DiarioIdosoController@update', ['auth', 'csrf', 'can:diario.editar']);

$router->get('/anamneses', 'AnamneseController@index', ['auth', 'can:anamneses.ver']);
$router->get('/anamneses/novo', 'AnamneseController@create', ['auth', 'can:anamneses.editar']);
$router->post('/anamneses', 'AnamneseController@store', ['auth', 'csrf', 'can:anamneses.editar']);
$router->get('/anamneses/{uuid}', 'AnamneseController@show', ['auth', 'can:anamneses.ver']);
$router->get('/anamneses/{uuid}/editar', 'AnamneseController@edit', ['auth', 'can:anamneses.editar']);
$router->post('/anamneses/{uuid}', 'AnamneseController@update', ['auth', 'csrf', 'can:anamneses.editar']);

$router->get('/historicos', 'HistoricoController@index', ['auth', 'can:historicos.ver']);
$router->get('/historicos/novo', 'HistoricoController@create', ['auth', 'can:historicos.editar']);
$router->post('/historicos', 'HistoricoController@store', ['auth', 'csrf', 'can:historicos.editar']);
$router->get('/historicos/{uuid}', 'HistoricoController@show', ['auth', 'can:historicos.ver']);
$router->get('/historicos/{uuid}/editar', 'HistoricoController@edit', ['auth', 'can:historicos.editar']);
$router->post('/historicos/{uuid}', 'HistoricoController@update', ['auth', 'csrf', 'can:historicos.editar']);

$router->get('/financeiro', 'FinanceiroController@hub', ['auth', 'can:financeiro.ver']);
$router->get('/financeiro/lancamentos', 'FinanceiroController@lancamentos', ['auth', 'can:financeiro.ver']);
$router->get('/financeiro/contas-receber', 'FinanceiroController@contasReceber', ['auth', 'can:financeiro.ver']);
$router->get('/financeiro/contas-pagar', 'FinanceiroController@contasPagar', ['auth', 'can:financeiro.ver']);
$router->get('/financeiro/contas-pagar/gerar', 'FinanceiroController@gerarContasPagar', ['auth', 'can:financeiro.gerar']);
$router->post('/financeiro/contas-pagar/gerar', 'FinanceiroController@storeContasPagar', ['auth', 'csrf', 'can:financeiro.gerar']);
$router->get('/financeiro/contratos', 'FinanceiroController@contratos', ['auth', 'can:contratos.ver']);
$router->get('/financeiro/contratos/novo', 'FinanceiroController@contratoNovo', ['auth', 'can:contratos.criar']);
$router->post('/financeiro/contratos', 'FinanceiroController@contratoStore', ['auth', 'csrf', 'can:contratos.criar']);

$router->get('/financeiro/relatorios/extrato', 'FinanceiroController@relatorioExtrato', ['auth', 'can:financeiro.relatorios']);
$router->get('/financeiro/relatorios/fluxo-caixa', 'FinanceiroController@relatorioFluxoCaixa', ['auth', 'can:financeiro.relatorios']);
$router->get('/financeiro/relatorios/inadimplencia', 'FinanceiroController@relatorioInadimplencia', ['auth', 'can:financeiro.relatorios']);
$router->get('/financeiro/relatorios/dre', 'FinanceiroController@relatorioDre', ['auth', 'can:financeiro.relatorios']);
$router->get('/financeiro/{uuid}/receber', 'FinanceiroController@receber', ['auth', 'can:financeiro.baixar']);
$router->post('/financeiro/{uuid}/receber', 'FinanceiroController@registrarRecebimento', ['auth', 'csrf', 'can:financeiro.baixar']);
$router->get('/financeiro/novo', 'FinanceiroController@create', ['auth', 'can:financeiro.editar']);
$router->post('/financeiro', 'FinanceiroController@store', ['auth', 'csrf', 'can:financeiro.editar']);
$router->get('/financeiro/{uuid}', 'FinanceiroController@show', ['auth', 'can:financeiro.ver']);
$router->get('/financeiro/{uuid}/editar', 'FinanceiroController@edit', ['auth', 'can:financeiro.editar']);
$router->post('/financeiro/{uuid}', 'FinanceiroController@update', ['auth', 'csrf', 'can:financeiro.editar']);


$router->get('/configuracoes', 'ConfiguracaoController@index', ['auth', 'can:configuracoes.ver']);
$router->get('/configuracoes/empresa', 'ConfiguracaoController@empresa', ['auth', 'can:configuracoes.ver']);
$router->post('/configuracoes/empresa', 'ConfiguracaoController@empresaSalvar', ['auth', 'csrf', 'can:configuracoes.editar']);
$router->get('/configuracoes/plantoes', 'ConfiguracaoController@plantoes', ['auth', 'can:configuracoes.ver']);
$router->post('/configuracoes/plantoes', 'ConfiguracaoController@plantaoSalvar', ['auth', 'csrf', 'can:configuracoes.editar']);
$router->post('/configuracoes/plantoes/{uuid}/alternar', 'ConfiguracaoController@plantaoAlternar', ['auth', 'csrf', 'can:configuracoes.editar']);
$router->get('/configuracoes/permissoes', 'ConfiguracaoController@permissoes', ['auth', 'can:usuarios.permissoes']);
$router->post('/configuracoes/permissoes', 'ConfiguracaoController@permissoesSalvar', ['auth', 'csrf', 'can:usuarios.permissoes']);
$router->get('/configuracoes/checklist-publicacao', 'ConfiguracaoController@checklistPublicacao', ['auth', 'can:configuracoes.ver']);
$router->get('/configuracoes/backups', 'ConfiguracaoController@backups', ['auth', 'can:configuracoes.ver']);
$router->post('/configuracoes/backups/gerar', 'ConfiguracaoController@backupGerar', ['auth', 'csrf', 'can:configuracoes.editar']);
$router->get('/configuracoes/backups/download/{filename}', 'ConfiguracaoController@backupDownload', ['auth', 'can:configuracoes.ver']);
$router->post('/configuracoes/backups/excluir/{filename}', 'ConfiguracaoController@backupExcluir', ['auth', 'csrf', 'can:configuracoes.editar']);

$router->get('/relatorio-plantao', 'RelatorioPlantaoController@index', ['auth', 'can:relatorios.ver']);

// O relatório deve nascer vinculado ao paciente.
$router->get('/relatorio-plantao/paciente/{uuid}', 'RelatorioPlantaoController@paciente', ['auth', 'can:relatorios.ver']);
$router->get('/relatorio-plantao/paciente/{uuid}/novo', 'RelatorioPlantaoController@create', ['auth', 'can:relatorios.editar']);
$router->post('/relatorio-plantao/paciente/{uuid}/store', 'RelatorioPlantaoController@store', ['auth', 'csrf', 'can:relatorios.editar']);

// Compatibilidade com link antigo usado em algumas telas.
$router->get('/relatorios-plantao/{uuid}', 'RelatorioPlantaoController@paciente', ['auth', 'can:relatorios.ver']);

// A visualização principal fica dentro do paciente; show() apenas redireciona.
$router->get('/relatorio-plantao/plantao/{uuid}/pdf', 'RelatorioPlantaoController@generatePdf', ['auth', 'can:relatorios.pdf']);
$router->get('/relatorio-plantao/plantao/{uuid}/editar', 'RelatorioPlantaoController@edit', ['auth', 'can:relatorios.editar']);
$router->post('/relatorio-plantao/plantao/{uuid}/atualizar', 'RelatorioPlantaoController@update', ['auth', 'csrf', 'can:relatorios.editar']);
$router->post('/relatorio-plantao/update/{uuid}', 'RelatorioPlantaoController@update', ['auth', 'csrf', 'can:relatorios.editar']);
$router->get('/relatorio-plantao/plantao/{uuid}', 'RelatorioPlantaoController@show', ['auth', 'can:relatorios.ver']);

/*Escalas*/
$router->get('/escala', 'EscalaController@index', ['auth', 'can:escala.ver']);
$router->post('/escala/salvar', 'EscalaController@salvar',  ['auth', 'csrf', 'can:escala.editar']);
$router->post('/escala/substituir', 'EscalaController@substituir', ['auth', 'csrf', 'can:escala.editar']);
$router->post('/escala/mover', 'EscalaController@mover', ['auth', 'csrf', 'can:escala.editar']);
$router->get('/escala/paciente/{uuid}', 'EscalaController@paciente', ['auth', 'can:escala.ver']);
$router->get('/escala/colaborador/{uuid}', 'EscalaController@colaborador', ['auth', 'can:escala.ver']);
$router->post('/escala/excluir', 'EscalaController@excluir', ['auth', 'csrf', 'can:escala.editar']);
$router->post('/escala/trocar', 'EscalaController@trocar', ['auth', 'csrf', 'can:escala.editar']);
$router->post('/escala/aprovar', 'EscalaController@aprovar', ['auth', 'csrf', 'can:escala.aprovar']);
$router->post('/escala/fechar', 'EscalaController@fechar', ['auth', 'csrf', 'can:escala.fechar']);

// Usuários
$router->get('/configuracoes/usuarios', 'ConfiguracaoController@usuarios', ['auth', 'can:usuarios.gerenciar']);
$router->get('/configuracoes/usuarios/novo', 'ConfiguracaoController@usuarioNovo', ['auth', 'can:usuarios.gerenciar']);
$router->post('/configuracoes/usuarios', 'ConfiguracaoController@usuarioSalvar', ['auth', 'csrf', 'can:usuarios.gerenciar']);
$router->get('/configuracoes/usuarios/{uuid}/editar', 'ConfiguracaoController@usuarioEditar', ['auth', 'can:usuarios.gerenciar']);
$router->post('/configuracoes/usuarios/{uuid}', 'ConfiguracaoController@usuarioUpdate', ['auth', 'csrf', 'can:usuarios.gerenciar']);
$router->post('/configuracoes/usuarios/{uuid}/status', 'ConfiguracaoController@usuarioAlternarStatus', ['auth', 'csrf', 'can:usuarios.gerenciar']);
$router->post('/configuracoes/usuarios/{uuid}/resetar-senha', 'ConfiguracaoController@usuarioResetarSenha', ['auth', 'csrf', 'can:usuarios.gerenciar']);
$router->post('/configuracoes/usuarios/{uuid}/cuidador', 'ConfiguracaoController@usuarioVincularCuidador', ['auth', 'csrf', 'can:usuarios.gerenciar']);


return $router;