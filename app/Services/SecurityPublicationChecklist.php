<?php

namespace App\Services;

class SecurityPublicationChecklist
{
    public function items(): array
    {
        return [
            [
                'grupo' => 'Ambiente',
                'titulo' => 'APP_DEBUG desativado em produção',
                'descricao' => 'Em produção, erros técnicos não devem aparecer na tela.',
                'status' => $this->envBool('APP_DEBUG') ? 'pendente' : 'ok',
            ],
            [
                'grupo' => 'Ambiente',
                'titulo' => 'APP_ENV definido',
                'descricao' => 'Use production no ambiente publicado.',
                'status' => $this->env('APP_ENV', 'local') === 'production' ? 'ok' : 'atencao',
            ],
            [
                'grupo' => 'Sessão',
                'titulo' => 'Cookies de sessão protegidos',
                'descricao' => 'Sessões devem usar HttpOnly, SameSite e Secure quando houver HTTPS.',
                'status' => 'atencao',
            ],
            [
                'grupo' => 'Acesso',
                'titulo' => 'Rotas sensíveis protegidas por permissões',
                'descricao' => 'Execute php tools/security_audit.php e confirme Achados: 0.',
                'status' => 'ok',
            ],
            [
                'grupo' => 'Usuários',
                'titulo' => 'Usuários individuais',
                'descricao' => 'Evite usuário compartilhado. Cada pessoa deve ter seu próprio login.',
                'status' => 'atencao',
            ],
            [
                'grupo' => 'Senhas',
                'titulo' => 'Política de senha ativa',
                'descricao' => 'Senha forte, troca obrigatória e bloqueio de usuário inativo.',
                'status' => 'ok',
            ],
            [
                'grupo' => 'Auditoria',
                'titulo' => 'Auditoria de ações críticas',
                'descricao' => 'Login, edição, exclusão, financeiro, escala e permissões devem gerar log.',
                'status' => 'ok',
            ],
            [
                'grupo' => 'Arquivos',
                'titulo' => 'Pastas sensíveis fora do acesso público',
                'descricao' => 'app, config, vendor, database, storage e .env não devem ser acessíveis pelo navegador.',
                'status' => 'atencao',
            ],
            [
                'grupo' => 'Backup',
                'titulo' => 'Rotina de backup definida',
                'descricao' => 'Banco e arquivos importantes precisam de cópia periódica.',
                'status' => 'pendente',
            ],
            [
                'grupo' => 'LGPD',
                'titulo' => 'Acesso mínimo necessário',
                'descricao' => 'Cada perfil deve acessar apenas o necessário para sua função.',
                'status' => 'ok',
            ],
        ];
    }

    public function resumo(): array
    {
        $items = $this->items();

        $total = count($items);

        $ok = count(array_filter($items, static function (array $item): bool {
            return ($item['status'] ?? '') === 'ok';
        }));

        $atencao = count(array_filter($items, static function (array $item): bool {
            return ($item['status'] ?? '') === 'atencao';
        }));

        $pendente = count(array_filter($items, static function (array $item): bool {
            return ($item['status'] ?? '') === 'pendente';
        }));

        return [
            'total' => $total,
            'ok' => $ok,
            'atencao' => $atencao,
            'pendente' => $pendente,
        ];
    }

    private function env(string $key, mixed $default = null): mixed
    {
        if (function_exists('env')) {
            return env($key, $default);
        }

        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    private function envBool(string $key): bool
    {
        $value = $this->env($key, false);

        if (is_bool($value)) {
            return $value;
        }

        return in_array(
            strtolower((string)$value),
            ['1', 'true', 'yes', 'on'],
            true
        );
    }
}