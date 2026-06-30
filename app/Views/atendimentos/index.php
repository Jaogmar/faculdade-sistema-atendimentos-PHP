<?php
$tituloPagina = 'Atendimentos';
$paginaAtiva = 'atendimentos';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Atendimentos</h1>
    <button class="btn btn-success" onclick="novoAtendimento()">Novo</button>
</div>

<div id="alerta"></div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th>Pessoa</th>
                    <th>Tipo</th>
                    <th>Responsável</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaAtendimentos">
                <tr><td colspan="8" class="text-center py-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAtendimento" tabindex="-1">
    <div class="modal-dialog">
        <form id="formAtendimento" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo atendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="pessoa_id">Pessoa *</label>
                    <select class="form-select" id="pessoa_id" name="pessoa_id" required></select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="tipo_atendimento_id">Tipo de atendimento *</label>
                    <select class="form-select" id="tipo_atendimento_id" name="tipo_atendimento_id" required></select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="data_atendimento">Data *</label>
                        <input type="date" class="form-control" id="data_atendimento" name="data_atendimento" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="horario_atendimento">Horário *</label>
                        <input type="time" class="form-control" id="horario_atendimento" name="horario_atendimento" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status inicial</label>
                    <select class="form-select" id="status" name="status">
                        <option value="aberto">aberto</option>
                        <option value="em_andamento">em_andamento</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="descricao">Descrição *</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Registrar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <form id="formStatus" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="statusAtendimentoId" name="id">
                <div class="mb-3">
                    <label class="form-label" for="novoStatus">Status</label>
                    <select class="form-select" id="novoStatus" name="status">
                        <option value="aberto">aberto</option>
                        <option value="em_andamento">em_andamento</option>
                        <option value="concluido">concluido</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="observacao_final">Observação final
                        <small class="text-muted">(obrigatória ao concluir)</small>
                    </label>
                    <textarea class="form-control" id="observacao_final" name="observacao_final" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
let modalAtendimento, modalStatus;

document.addEventListener('DOMContentLoaded', () => {
    modalAtendimento = new bootstrap.Modal(document.getElementById('modalAtendimento'));
    modalStatus = new bootstrap.Modal(document.getElementById('modalStatus'));

    document.getElementById('formAtendimento').addEventListener('submit', async event => {
        event.preventDefault();
        try {
            await AtendeLabApi.post('atendimentos', 'criar', new FormData(event.target));
            modalAtendimento.hide();
            AtendeLabApi.showAlert('alerta', 'Atendimento registrado com sucesso.');
            await carregarAtendimentos();
        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    });

    document.getElementById('formStatus').addEventListener('submit', async event => {
        event.preventDefault();
        try {
            await AtendeLabApi.post('atendimentos', 'alterarStatus', new FormData(event.target));
            modalStatus.hide();
            AtendeLabApi.showAlert('alerta', 'Status atualizado com sucesso.');
            await carregarAtendimentos();
        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    });

    carregarAtendimentos();
});

const STATUS_BADGE = { aberto: 'text-bg-warning', em_andamento: 'text-bg-info', concluido: 'text-bg-success' };

async function carregarAtendimentos() {
    const tbody = document.getElementById('tabelaAtendimentos');
    try {
        const dados = AtendeLabApi.toList(await AtendeLabApi.get('atendimentos', 'listar'));
        if (!dados.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">Nenhum atendimento registrado.</td></tr>';
            return;
        }
        tbody.innerHTML = dados.map(a => `<tr>
            <td>${AtendeLabApi.escape(a.protocolo || a.id)}</td>
            <td>${AtendeLabApi.escape(a.pessoa_nome || '')}</td>
            <td>${AtendeLabApi.escape(a.tipo_nome || '')}</td>
            <td>${AtendeLabApi.escape(a.responsavel_nome || '')}</td>
            <td>${AtendeLabApi.escape(a.data_atendimento || '')}</td>
            <td>${AtendeLabApi.escape(a.horario_atendimento || '')}</td>
            <td><span class="badge ${STATUS_BADGE[a.status] || 'text-bg-secondary'}">${AtendeLabApi.escape(a.status)}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="abrirStatus(${Number(a.id)}, '${AtendeLabApi.escapeAttr(a.status)}')">Status</button>
            </td>
        </tr>`).join('');
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">${AtendeLabApi.escape(error.message)}</td></tr>`;
    }
}

async function novoAtendimento() {
    try {
        const [pessoas, tipos] = await Promise.all([
            AtendeLabApi.get('pessoas', 'listar'),
            AtendeLabApi.get('tipos', 'listar')
        ]);
        preencherSelect('pessoa_id', AtendeLabApi.toList(pessoas).filter(p => p.status === 'ativo'), 'nome');
        preencherSelect('tipo_atendimento_id', AtendeLabApi.toList(tipos).filter(t => t.status === 'ativo'), 'nome');

        document.getElementById('formAtendimento').reset();
        modalAtendimento.show();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}

function preencherSelect(elementId, itens, campoTexto) {
    const select = document.getElementById(elementId);
    if (!itens.length) {
        select.innerHTML = '<option value="">Nenhum registro ativo</option>';
        return;
    }
    select.innerHTML = itens
        .map(i => `<option value="${Number(i.id)}">${AtendeLabApi.escape(i[campoTexto])}</option>`)
        .join('');
}

function abrirStatus(id, statusAtual) {
    document.getElementById('formStatus').reset();
    document.getElementById('statusAtendimentoId').value = id;
    document.getElementById('novoStatus').value = statusAtual || 'aberto';
    modalStatus.show();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
