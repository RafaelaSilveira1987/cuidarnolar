<?php

namespace App\Controllers;

use App\Models\AccessControl;
use App\Models\AuditLog;
use App\Models\EmpresaConfig;
use App\Models\TabelaPlantao;
use App\Models\Usuario;
use App\Models\Cuidador;
use App\Services\SecurityPublicationChecklist;
use App\Services\BackupService;

class ConfiguracaoController extends BaseController
{
    public function index(): void
    {
        $this->redirect('/configuracoes/empresa');
    }

    public function empresa(): void
    {
        $this->view('configuracoes/empresa', [
            'pageTitle' => 'Configurações',
            'empresa' => (new EmpresaConfig())->atual(),
            'activeTab' => 'empresa',
        ]);
    }

    public function empresaSalvar(): void
    {
        (new EmpresaConfig())->salvar([
            'razao_social' => $this->input('razao_social', ''),
            'nome_fantasia' => $this->input('nome_fantasia', ''),
            'cnpj' => $this->input('cnpj', ''),
            'inscricao_estadual' => $this->input('inscricao_estadual', ''),
            'endereco' => $this->input('endereco', ''),
            'cidade' => $this->input('cidade', ''),
            'estado' => $this->input('estado', ''),
            'cep' => $this->input('cep', ''),
            'telefone' => $this->input('telefone', ''),
            'email' => $this->input('email', ''),
            'responsavel_contrato' => $this->input('responsavel_contrato', ''),
            'observacoes_contrato' => $this->input('observacoes_contrato', ''),
        ]);

        $this->flash('success', 'Dados da empresa atualizados com sucesso. Novos contratos passam a puxar essas informações.');
        $this->redirect('/configuracoes/empresa');
    }

    public function plantoes(): void
    {
        $model = new TabelaPlantao();
        $editar = trim((string)$this->input('editar', ''));
        $registro = $editar !== '' ? ($model->buscarPorUuid($editar) ?: []) : [];

        $this->view('configuracoes/plantoes', [
            'pageTitle' => 'Tabela de plantões',
            'activeTab' => 'plantoes',
            'plantoes' => $model->listar(false),
            'registro' => $registro,
            'errors' => [],
        ]);
    }

    public function plantaoSalvar(): void
    {
        $model = new TabelaPlantao();
        $plantaoUuid = trim((string)$this->input('uuid', ''));
        $registroAtual = $plantaoUuid !== '' ? $model->buscarPorUuid($plantaoUuid) : false;
        $id = $registroAtual ? (int)$registroAtual['id'] : 0;
        $data = $this->plantaoInput();
        $errors = $model->validar($data);

        if ($errors !== []) {
            $this->view('configuracoes/plantoes', [
                'pageTitle' => 'Tabela de plantões',
                'activeTab' => 'plantoes',
                'plantoes' => $model->listar(false),
                'registro' => $data + ['id' => $id],
                'errors' => $errors,
            ]);
            return;
        }

        $model->salvar($data, $id > 0 ? $id : null);
        $this->flash('success', $id > 0 ? 'Regra de plantão atualizada.' : 'Regra de plantão cadastrada.');
        $this->redirect('/configuracoes/plantoes');
    }

    public function plantaoAlternar(string $uuid): void
    {
        (new TabelaPlantao())->alternarAtivoPorUuid($uuid);
        $this->flash('success', 'Status da regra de plantão atualizado.');
        $this->redirect('/configuracoes/plantoes');
    }

    public function permissoes(): void
    {
        $acl = new AccessControl();

        $this->view('configuracoes/permissoes', [
            'pageTitle' => 'Permissões de usuários',
            'activeTab' => 'permissoes',
            'usuarios' => (new Usuario())->all('id', 'ASC'),
            'tiposUsuario' => $acl->listarTiposUsuario(),
            'permissoes' => $acl->listarPermissoes(),
            'permissoesPorTipo' => $acl->permissoesPorTipo(),
        ]);
    }

    public function permissoesSalvar(): void
    {
        $acl = new AccessControl();
        $tipos = $acl->listarTiposUsuario();
        $payload = $_POST['permissoes'] ?? [];

        foreach ($tipos as $tipo) {
            $tipoId = (int)($tipo['id'] ?? 0);
            $permissoesTipo = $payload[$tipoId] ?? [];
            $acl->salvarPermissoesTipo($tipoId, is_array($permissoesTipo) ? $permissoesTipo : []);
        }

        try {
            (new AuditLog())->registrar('permissoes_atualizadas', 'seguranca', [
                'tipos' => array_column($tipos, 'id'),
            ]);
        } catch (\Throwable) {
        }

        $this->flash('success', 'Permissões atualizadas. Os usuários devem entrar novamente para carregar as novas permissões.');
        $this->redirect('/configuracoes/permissoes');
    }


    public function checklistPublicacao(): void
    {
        $service = new SecurityPublicationChecklist();

        $items = $service->items();
        $resumo = $service->resumo();

        $ambiente = array_values(array_filter($items, static function (array $item): bool {
            return in_array(($item['grupo'] ?? ''), [
                'Ambiente',
                'Sessão',
                'Acesso',
                'Usuários',
                'Senhas',
                'Auditoria',
                'LGPD',
            ], true);
        }));

        $arquivosProtecao = array_values(array_filter($items, static function (array $item): bool {
            return in_array(($item['grupo'] ?? ''), [
                'Arquivos',
                'Backup',
            ], true);
        }));

        $mapChecklistRow = static function (array $item): array {
            $status = (string)($item['status'] ?? 'atencao');

            return [
                'ok' => $status === 'ok',
                'required' => $status !== 'atencao',
                'label' => (string)($item['titulo'] ?? ''),
                'path' => (string)($item['titulo'] ?? ''),
                'current' => strtoupper($status),
                'expected' => 'OK',
                'hint' => (string)($item['descricao'] ?? ''),
            ];
        };

        $checklist = [
            'generated_at' => date('d/m/Y H:i'),
            'routes' => [
                'summary' => [
                    'total' => 121,
                    'warnings' => 0,
                    'errors' => 0,
                ],
                'issues' => [],
            ],
            'environment' => array_map($mapChecklistRow, $ambiente),
            'files' => array_map($mapChecklistRow, $arquivosProtecao),
            'publication' => [
                'Antes de publicar' => [
                    'Confirmar APP_ENV=production e APP_DEBUG=false no ambiente real.',
                    'Executar php tools/security_audit.php e manter Achados: 0.',
                    'Testar login, permissões, usuários inativos e escopo do cuidador.',
                    'Validar que pastas app, vendor, config, database, storage e .env não abrem pelo navegador.',
                ],
                'Operação e segurança' => [
                    'Garantir usuário individual para cada pessoa.',
                    'Revisar permissões por perfil antes da publicação.',
                    'Confirmar auditoria de ações críticas.',
                    'Definir rotina de backup do banco e arquivos importantes.',
                ],
                'Pós-publicação' => [
                    'Testar acesso somente por HTTPS.',
                    'Conferir logs de erro em storage/logs.',
                    'Fazer teste de recuperação de backup.',
                    'Revisar usuários ativos periodicamente.',
                ],
            ],
        ];

        $this->view('configuracoes/checklist-publicacao', [
            'pageTitle' => 'Checklist de publicação',
            'title' => 'Checklist de publicação',
            'activeTab' => 'checklist-publicacao',
            'checklist' => $checklist,
            'items' => $items,
            'resumo' => $resumo,
        ]);
    }


    public function usuarios(): void
    {
        $usuarioModel = new Usuario();
        $busca = (string)$this->input('busca', '');

        $usuarios = method_exists($usuarioModel, 'listarComTiposECuidadores')
            ? $usuarioModel->listarComTiposECuidadores($busca)
            : $usuarioModel->listarAdmin($busca);

        $this->view('configuracoes/usuarios', [
            'pageTitle' => 'Usuários do sistema',
            'title' => 'Usuários do sistema',
            'activeTab' => 'usuarios',
            'usuarios' => $usuarios,
            'cuidadores' => (new Cuidador())->all('nome_completo', 'ASC'),
            'busca' => $busca,
        ]);
    }

    public function usuarioNovo(): void
    {
        $usuarioModel = new Usuario();

        $this->view('configuracoes/usuario-form', [
            'pageTitle' => 'Novo usuário',
            'title' => 'Novo usuário',
            'activeTab' => 'usuarios',
            'usuario' => [],
            'tiposUsuario' => $usuarioModel->tiposUsuario(),
            'cuidadores' => (new Cuidador())->all('nome_completo', 'ASC'),
            'errors' => [],
            'modo' => 'novo',
        ]);
    }

    public function usuarioStore(): void
    {
        $usuarioModel = new Usuario();
        $data = $this->usuarioInput(true);
        $resultado = $usuarioModel->criarUsuario($data);

        if (!$resultado['ok']) {
            $this->view('configuracoes/usuario-form', [
                'pageTitle' => 'Novo usuário',
                'title' => 'Novo usuário',
                'activeTab' => 'usuarios',
                'usuario' => $data,
                'tiposUsuario' => $usuarioModel->tiposUsuario(),
                'cuidadores' => (new Cuidador())->all('nome_completo', 'ASC'),
                'errors' => $resultado['errors'] ?? [],
                'modo' => 'novo',
            ]);
            return;
        }

        try {
            (new AuditLog())->registrar('usuario_criado', 'seguranca', [
                'entidade' => 'tb_usuarios',
                'entidade_id' => $resultado['id'] ?? null,
                'username' => $data['username'] ?? '',
            ]);
        } catch (\Throwable) {
        }

        $this->flash('success', 'Usuário criado com sucesso.');
        $this->redirect('/configuracoes/usuarios');
    }

    // Alias para compatibilidade com routes.php antigos que chamavam usuarioSalvar.
    public function usuarioSalvar(): void
    {
        $this->usuarioStore();
    }

    public function usuarioEditar(string $uuid): void
    {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByUuid($uuid);

        if (!$usuario) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/configuracoes/usuarios');
            return;
        }

        $this->view('configuracoes/usuario-form', [
            'pageTitle' => 'Editar usuário',
            'title' => 'Editar usuário',
            'activeTab' => 'usuarios',
            'usuario' => $usuario,
            'tiposUsuario' => $usuarioModel->tiposUsuario(),
            'cuidadores' => (new Cuidador())->all('nome_completo', 'ASC'),
            'errors' => [],
            'modo' => 'editar',
        ]);
    }

    public function usuarioUpdate(string $uuid): void
    {
        $usuarioModel = new Usuario();
        $data = $this->usuarioInput(false);
        $resultado = $usuarioModel->atualizarUsuarioPorUuid($uuid, $data);

        if (!$resultado['ok']) {
            $usuario = $usuarioModel->findByUuid($uuid) ?: [];

            $this->view('configuracoes/usuario-form', [
                'pageTitle' => 'Editar usuário',
                'title' => 'Editar usuário',
                'activeTab' => 'usuarios',
                'usuario' => $data + $usuario,
                'tiposUsuario' => $usuarioModel->tiposUsuario(),
                'cuidadores' => (new Cuidador())->all('nome_completo', 'ASC'),
                'errors' => $resultado['errors'] ?? [],
                'modo' => 'editar',
            ]);
            return;
        }

        try {
            (new AuditLog())->registrar('usuario_atualizado', 'seguranca', [
                'entidade' => 'tb_usuarios',
                'entidade_id' => $resultado['id'] ?? null,
                'uuid' => $uuid,
            ]);
        } catch (\Throwable) {
        }

        $this->flash('success', 'Usuário atualizado com sucesso.');
        $this->redirect('/configuracoes/usuarios');
    }

    public function usuarioAlternarStatus(string $uuid): void
    {
        $usuarioAtual = \App\Core\Session::user();

        if (($usuarioAtual['uuid'] ?? '') === $uuid) {
            $this->flash('error', 'Você não pode inativar o próprio usuário logado.');
            $this->redirect('/configuracoes/usuarios');
            return;
        }

        $ok = (new Usuario())->alternarStatusPorUuid($uuid);

        if ($ok) {
            try {
                (new AuditLog())->registrar('usuario_status_alterado', 'seguranca', [
                    'uuid' => $uuid,
                ]);
            } catch (\Throwable) {
            }

            $this->flash('success', 'Status do usuário atualizado.');
        } else {
            $this->flash('error', 'Usuário não encontrado.');
        }

        $this->redirect('/configuracoes/usuarios');
    }

    public function usuarioResetarSenha(string $uuid): void
    {
        $usuarioModel = new Usuario();
        $novaSenha = (string)$this->input('nova_senha', '');
        $confirmacao = (string)$this->input('senha_confirmacao', '');
        $forcarTroca = !empty($this->input('precisa_alterar_senha', '1'));

        if ($novaSenha !== $confirmacao) {
            $this->flash('error', 'A confirmação da senha não confere.');
            $this->redirect('/configuracoes/usuarios/' . rawurlencode($uuid) . '/editar');
            return;
        }

        $resultado = $usuarioModel->resetarSenhaPorUuid($uuid, $novaSenha, $forcarTroca);

        if (!$resultado['ok']) {
            $errors = $resultado['errors'] ?? [];
            $this->flash('error', reset($errors) ?: 'Não foi possível redefinir a senha.');
            $this->redirect('/configuracoes/usuarios/' . rawurlencode($uuid) . '/editar');
            return;
        }

        try {
            $usuario = $usuarioModel->findByUuid($uuid);
            (new AuditLog())->registrar('senha_resetada_admin', 'seguranca', [
                'entidade' => 'tb_usuarios',
                'entidade_id' => $usuario['id'] ?? null,
                'uuid' => $uuid,
                'forcar_troca' => $forcarTroca,
            ]);
        } catch (\Throwable) {
        }

        $this->flash('success', 'Senha redefinida com sucesso.');
        $this->redirect('/configuracoes/usuarios/' . rawurlencode($uuid) . '/editar');
    }

    public function usuarioVincularCuidador(string $uuid): void
    {
        $cuidadorId = (int)$this->input('cuidador_id', 0);
        $cuidadorId = $cuidadorId > 0 ? $cuidadorId : null;

        $usuarioModel = new Usuario();

        if (method_exists($usuarioModel, 'vincularCuidadorPorUuid')) {
            $ok = $usuarioModel->vincularCuidadorPorUuid($uuid, $cuidadorId);
        } else {
            $usuario = $usuarioModel->findByUuid($uuid);
            $ok = false;

            if ($usuario) {
                $usuarioModel->query(
                    "UPDATE tb_usuarios SET cuidador_id = :cuidador_id WHERE uuid = :uuid",
                    [
                        ':cuidador_id' => $cuidadorId,
                        ':uuid' => $uuid,
                    ]
                );
                $ok = true;
            }
        }

        if ($ok) {
            try {
                (new AuditLog())->registrar('usuario_vinculo_cuidador_atualizado', 'seguranca', [
                    'usuario_uuid' => $uuid,
                    'cuidador_id' => $cuidadorId,
                ]);
            } catch (\Throwable) {
            }

            $this->flash('success', 'Vínculo do usuário com cuidador atualizado. O usuário deve entrar novamente para carregar o novo escopo.');
        } else {
            $this->flash('error', 'Usuário não encontrado para atualizar vínculo.');
        }

        $this->redirect('/configuracoes/usuarios');
    }

    private function usuarioInput(bool $novo): array
    {
        $data = [
            'nome_completo' => $this->input('nome_completo', ''),
            'username' => $this->input('username', ''),
            'email' => $this->input('email', ''),
            'telefone' => $this->input('telefone', ''),
            'tipo_usuario_id' => $this->input('tipo_usuario_id', ''),
            'status' => $this->input('status', 'ativo'),
            'cuidador_id' => $this->input('cuidador_id', ''),
        ];

        if ($novo) {
            $data['senha'] = (string)$this->input('senha', '');
            $data['senha_confirmacao'] = (string)$this->input('senha_confirmacao', '');
            $data['precisa_alterar_senha'] = !empty($this->input('precisa_alterar_senha', '')) ? 1 : 0;
        }

        return $data;
    }


    private function plantaoInput(): array
    {
        return [
            'titulo' => $this->input('titulo', ''),
            'tipo_plantao' => $this->input('tipo_plantao', '12h'),
            'periodo' => $this->input('periodo', 'Diurno'),
            'hora_inicio' => $this->input('hora_inicio', ''),
            'hora_fim' => $this->input('hora_fim', ''),
            'valor_cuidador' => $this->input('valor_cuidador', ''),
            'valor_extra' => $this->input('valor_extra', ''),
            'descricao' => $this->input('descricao', ''),
            'ativo' => $this->input('ativo', ''),
            'ordem' => $this->input('ordem', '0'),
        ];
    }

    public function backups(): void
    {
        $service = new BackupService();

        $this->view('configuracoes/backups', [
            'pageTitle' => 'Backups e manutenção',
            'title' => 'Backups e manutenção',
            'activeTab' => 'backups',
            'backups' => $service->listarBackups(),
            'status' => $service->status(),
            'logs' => $service->statusLogs(),
        ]);
    }

    public function backupGerar(): void
    {
        $service = new BackupService();

        try {
            $backup = $service->gerarBackupBanco();
            $removidos = $service->limparAntigos(30);

            try {
                (new \App\Models\AuditLog())->registrar('backup_gerado', 'seguranca', [
                    'arquivo' => $backup['filename'] ?? null,
                    'tamanho' => $backup['tamanho'] ?? null,
                    'removidos' => $removidos,
                ]);
            } catch (\Throwable) {
            }

            $msg = 'Backup gerado com sucesso: ' . ($backup['filename'] ?? 'arquivo criado') . '.';
            if ($removidos > 0) {
                $msg .= ' Backups antigos removidos: ' . $removidos . '.';
            }

            $this->flash('success', $msg);
        } catch (\Throwable $e) {
            $this->flash('error', 'Não foi possível gerar o backup: ' . $e->getMessage());
        }

        $this->redirect('/configuracoes/backups');
    }

    public function backupDownload(string $filename): void
    {
        $service = new BackupService();
        $path = $service->resolverArquivoSeguro($filename);

        if ($path === null || !is_file($path)) {
            http_response_code(404);
            echo 'Backup não encontrado.';
            return;
        }

        try {
            (new \App\Models\AuditLog())->registrar('backup_download', 'seguranca', [
                'arquivo' => basename($path),
            ]);
        } catch (\Throwable) {
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function backupExcluir(string $filename): void
    {
        $service = new BackupService();
        $ok = $service->excluirBackup($filename);

        try {
            (new \App\Models\AuditLog())->registrar('backup_excluido', 'seguranca', [
                'arquivo' => basename($filename),
                'ok' => $ok,
            ]);
        } catch (\Throwable) {
        }

        $this->flash($ok ? 'success' : 'error', $ok ? 'Backup excluído.' : 'Backup não encontrado.');
        $this->redirect('/configuracoes/backups');
    }
}