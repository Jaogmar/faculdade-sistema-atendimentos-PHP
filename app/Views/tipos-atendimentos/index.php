<?php
$tituloPagina = 'Tipos de Atendimento';
$paginaAtiva = 'tipos';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Tipos de Atendimento</h1>
    <button class="btn btn-success" onclick="novoTipo()">Novo</button>
</div>

<div id="alerta"></div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaTipos">
                <tr><td colspan="4" class="text-center py-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalTipo" tabindex="-1">
    <div class="modal-dialog">
        <form id="formTipo" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalTipo">Novo tipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tipoId" name="id">

                <div class="mb-3">
                    <label class="form-label" for="nome">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="descricao">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="ativo">ativo</option>
                        <option value="inativo">inativo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
let modalTipo;

document.addEventListener('DOMContentLoaded', () => {
    modalTipo = new bootstrap.Modal(document.getElementById('modalTipo'));

    document.getElementById('formTipo').addEventListener('submit', async event => {
        event.preventDefault();
        const id = document.getElementById('tipoId').value;
        try {
            await AtendeLabApi.post('tipos', id ? 'atualizar' : 'criar', new FormData(event.target));
            modalTipo.hide();
            AtendeLabApi.showAlert('alerta', id ? 'Tipo atualizado com sucesso.' : 'Tipo cadastrado com sucesso.');
            await carregarTipos();
        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    });

    carregarTipos();
});

async function carregarTipos() {
    const tbody = document.getElementById('tabelaTipos');
    try {
        const dados = AtendeLabApi.toList(await AtendeLabApi.get('tipos', 'listar'));
        if (!dados.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Nenhum tipo cadastrado.</td></tr>';
            return;
        }
        tbody.innerHTML = dados.map(t => `<tr>
            <td>${AtendeLabApi.escape(t.nome)}</td>
            <td>${AtendeLabApi.escape(t.descricao || '')}</td>
            <td><span class="badge ${t.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary'}">${AtendeLabApi.escape(t.status)}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="editarTipo(${Number(t.id)})">Editar</button>
                <button class="btn btn-sm btn-outline-danger" onclick="inativarTipo(${Number(t.id)})">Inativar</button>
            </td>
        </tr>`).join('');
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${AtendeLabApi.escape(error.message)}</td></tr>`;
    }
}

function novoTipo() {
    document.getElementById('formTipo').reset();
    document.getElementById('tipoId').value = '';
    document.getElementById('tituloModalTipo').textContent = 'Novo tipo';
    modalTipo.show();
}

async function editarTipo(id) {
    try {
        const t = AtendeLabApi.toObject(await AtendeLabApi.get('tipos', 'buscar', { id }));
        document.getElementById('tipoId').value = t.id ?? '';
        document.getElementById('nome').value = t.nome ?? '';
        document.getElementById('descricao').value = t.descricao ?? '';
        document.getElementById('status').value = t.status ?? 'ativo';
        document.getElementById('tituloModalTipo').textContent = 'Editar tipo';
        modalTipo.show();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}

async function inativarTipo(id) {
    if (!confirm('Inativar este tipo de atendimento?')) return;
    try {
        await AtendeLabApi.post('tipos', 'inativar', { id });
        AtendeLabApi.showAlert('alerta', 'Tipo inativado com sucesso.');
        await carregarTipos();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
