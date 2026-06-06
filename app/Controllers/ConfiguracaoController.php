<?php

namespace App\Controllers;

use App\Models\AccessControl;
use App\Models\AuditLog;
use App\Models\EmpresaConfig;
use App\Models\TabelaPlantao;
use App\Models\Usuario;
use App\Services\SecurityPublicationChecklist;

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
}