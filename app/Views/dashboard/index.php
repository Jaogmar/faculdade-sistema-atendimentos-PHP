<?php
$tituloPagina = 'Dashboard';
$paginaAtiva = 'dashboard';
require __DIR__ . '/../layouts/header.php';
?>

<h1 class="h4 mb-3">Dashboard</h1>

<div id="alerta"></div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm text-center card-indicador">
            <div class="card-body">
                <div class="text-muted">Pessoas</div>
                <div class="display-6" id="totalPessoas">-</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm text-center card-indicador">
            <div class="card-body">
                <div class="text-muted">Tipos de atendimento</div>
                <div class="display-6" id="totalTipos">-</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm text-center card-indicador">
            <div class="card-body">
                <div class="text-muted">Atendimentos</div>
                <div class="display-6" id="totalAtendimentos">-</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">Últimos atendimentos</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th>Pessoa</th>
                    <th>Tipo</th>
                    <th>Responsável</th>
                    <th>Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tabelaRecentes">
                <tr><td colspan="6" class="text-center py-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const STATUS_BADGE = { aberto: 'text-bg-warning', em_andamento: 'text-bg-info', concluido: 'text-bg-success' };

document.addEventListener('DOMContentLoaded', async () => {
    const tbody = document.getElementById('tabelaRecentes');
    try {
        const resumo = await AtendeLabApi.get('dashboard', 'resumo');
        const ind = resumo.indicadores || {};

        document.getElementById('totalPessoas').textContent = ind.total_pessoas ?? 0;
        document.getElementById('totalTipos').textContent = ind.total_tipos ?? 0;
        document.getElementById('totalAtendimentos').textContent = ind.total_atendimentos ?? 0;

        const recentes = resumo.atendimentos_recentes || [];
        if (!recentes.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Nenhum atendimento registrado.</td></tr>';
            return;
        }
        tbody.innerHTML = recentes.map(a => `<tr>
            <td>${AtendeLabApi.escape(a.protocolo || a.id)}</td>
            <td>${AtendeLabApi.escape(a.pessoa_nome || '')}</td>
            <td>${AtendeLabApi.escape(a.tipo_nome || '')}</td>
            <td>${AtendeLabApi.escape(a.responsavel_nome || '')}</td>
            <td>${AtendeLabApi.escape(a.data_atendimento || '')}</td>
            <td><span class="badge ${STATUS_BADGE[a.status] || 'text-bg-secondary'}">${AtendeLabApi.escape(a.status)}</span></td>
        </tr>`).join('');
    } catch (error) {
        ['totalPessoas', 'totalTipos', 'totalAtendimentos'].forEach(id => {
            const el = document.getElementById(id);
            el.textContent = '!';
            el.title = error.message;
        });
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${AtendeLabApi.escape(error.message)}</td></tr>`;
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
