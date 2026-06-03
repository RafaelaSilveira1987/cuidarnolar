<?php
$empresa = $empresa ?? [];
$value = static fn(string $key, string $default = ''): string => (string)($empresa[$key] ?? $default);
?>

<section class="cfg-page">
    <div class="cfg-header">
        <div>
            <span class="cfg-kicker">Configurações</span>
            <h1>Dados da empresa</h1>
            <p>Essas informações serão usadas como padrão nos novos contratos do paciente.</p>
        </div>
    </div>

    <?php include __DIR__ . '/_subnav.php'; ?>

    <form method="POST" action="<?= url('/configuracoes/empresa') ?>" class="cfg-card cfg-form">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <div class="cfg-section-title">
            <h2>Identificação</h2>
            <p>Dados jurídicos e comerciais do home care.</p>
        </div>

        <div class="cfg-grid two">
            <label>
                Razão social
                <input type="text" name="razao_social" value="<?= e($value('razao_social', 'Cuidar no Lar')) ?>" required>
            </label>
            <label>
                Nome fantasia
                <input type="text" name="nome_fantasia" value="<?= e($value('nome_fantasia', 'Cuidar no Lar')) ?>">
            </label>
            <label>
                CNPJ
                <input type="text" name="cnpj" value="<?= e($value('cnpj')) ?>" placeholder="00.000.000/0000-00">
            </label>
            <label>
                Inscrição estadual
                <input type="text" name="inscricao_estadual" value="<?= e($value('inscricao_estadual')) ?>">
            </label>
        </div>

        <div class="cfg-section-title">
            <h2>Endereço e contato</h2>
        </div>

        <div class="cfg-grid three">
            <label class="wide-2">
                Endereço
                <input type="text" name="endereco" value="<?= e($value('endereco')) ?>" placeholder="Rua, número, bairro">
            </label>
            <label>
                Cidade
                <input type="text" name="cidade" value="<?= e($value('cidade')) ?>">
            </label>
            <label>
                Estado
                <input type="text" name="estado" value="<?= e($value('estado')) ?>" maxlength="2" placeholder="MG">
            </label>
            <label>
                CEP
                <input type="text" name="cep" value="<?= e($value('cep')) ?>">
            </label>
            <label>
                Telefone
                <input type="text" name="telefone" value="<?= e($value('telefone')) ?>">
            </label>
            <label>
                E-mail
                <input type="email" name="email" value="<?= e($value('email')) ?>">
            </label>
        </div>

        <div class="cfg-section-title">
            <h2>Contrato</h2>
            <p>Quem assina/representa a empresa nos contratos.</p>
        </div>

        <div class="cfg-grid two">
            <label>
                Responsável pelo contrato
                <input type="text" name="responsavel_contrato" value="<?= e($value('responsavel_contrato')) ?>">
            </label>
            <label class="wide">
                Observações padrão do contrato
                <textarea name="observacoes_contrato" rows="4" placeholder="Cláusulas internas ou observações padrão."><?= e($value('observacoes_contrato')) ?></textarea>
            </label>
        </div>

        <div class="cfg-actions">
            <button type="submit" class="btn btn-primary">Salvar dados da empresa</button>
        </div>
    </form>
</section>
