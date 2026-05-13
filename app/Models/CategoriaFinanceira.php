<?php

namespace App\Models;

class CategoriaFinanceira extends BaseModel
{
    protected string $table = 'tb_categorias_financeiro';

    /** Opções para select no formulário de lançamento (id => rótulo). */
    public function listForSelect(): array
    {
        $rows = $this->rawAll(
            "SELECT id, nome, tipo FROM {$this->table} WHERE ativo = 1 ORDER BY tipo ASC, nome ASC"
        );
        $out = [];
        foreach ($rows as $r) {
            $tag = ($r['tipo'] ?? '') === 'entrada' ? 'Entrada' : 'Saída';
            $out[(string) $r['id']] = "[{$tag}] {$r['nome']}";
        }

        return $out;
    }
}
