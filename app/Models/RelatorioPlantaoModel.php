<?php

class RelatorioPlantaoModel extends Model
{

    public function salvar($plantaoId, $evolucao)
    {

        $sql = "
            INSERT INTO tb_relatorio_plantao
            (
                plantao_id,
                evolucao
            )
            VALUES
            (?, ?)
        ";

        return $this->execute(
            $sql,
            [
                $plantaoId,
                $evolucao
            ]
        );
    }
}