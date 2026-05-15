<?php

namespace App\Models;

use PDO;

class PlantaoModel
{
    private $pdo;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'cuidar_no_lar';
        $user = 'root';
        $pass = '';

        $this->pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8",
            $user,
            $pass
        );
    }

    public function listarPorPaciente($pacienteId)
    {
        $sql = "
            SELECT
                p.*,
                c.nome_completo AS enfermeiro
            FROM tb_plantoes p
            LEFT JOIN tb_cuidador c
                ON c.id = p.cuidador_id
            WHERE p.paciente_id = ?
            ORDER BY p.data_inicio DESC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$pacienteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPacientes()
    {
        $sql = "
            SELECT DISTINCT
                p.paciente_id,
                pr.nome_completo,
                pr.cpf,
                pr.cuidador_id,
                p.data_inicio AS ultima_data,
                p.status,
                d.nome_completo AS enfermeiro
            FROM tb_plantoes p
            JOIN tb_pacientes pr ON pr.id = p.paciente_id
            LEFT JOIN tb_cuidador d ON d.id = p.cuidador_id
            ORDER BY pr.nome_completo ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPlantaoCompleto($id)
    {
        $sql = "
            SELECT *
            FROM tb_plantoes
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assinarPlantao($plantaoId, $enfermeiroId)
    {
        $sql = "
            UPDATE tb_plantoes
            SET
                status = 'assinado',
                data_assinatura = NOW(),
                cuidador_id = ?
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$enfermeiroId, $plantaoId]);
    }
}