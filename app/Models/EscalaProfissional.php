<?php

namespace App\Models;

class EscalaProfissional extends BaseModuleModel
{
    protected string $table = 'tb_escala_profissionais';

    protected array $fillable = [
        'escala_base_id',
        'cuidador_id',
        'ordem_revezamento',
        'cor_escala',
        'ativo',
    ];
}