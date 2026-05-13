<?php $at = $finSubnav ?? ''; ?>
<nav class="fin-subnav" aria-label="Módulo financeiro">
    <a class="<?= $at === 'hub' ? 'fin-subnav__link--active' : '' ?>" href="<?= url('/financeiro') ?>">Início</a>
    <a class="<?= $at === 'lancamentos' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/lancamentos') ?>">Lançamentos</a>
    <a class="<?= $at === 'receber' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/contas-receber') ?>">Contas a receber</a>
    <a class="<?= $at === 'pagar' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/contas-pagar') ?>">Contas a pagar</a>
    <a class="<?= $at === 'contratos' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/contratos') ?>">Contratos</a>
    <span class="fin-subnav__sep">|</span>
    <a class="<?= $at === 'rextrato' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/relatorios/extrato') ?>">Extrato paciente</a>
    <a class="<?= $at === 'rfluxo' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/relatorios/fluxo-caixa') ?>">Fluxo de caixa</a>
    <a class="<?= $at === 'rinad' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/relatorios/inadimplencia') ?>">Inadimplência</a>
    <a class="<?= $at === 'rdre' ? 'fin-subnav__link--active' : '' ?>"
        href="<?= url('/financeiro/relatorios/dre') ?>">DRE</a>
</nav>
