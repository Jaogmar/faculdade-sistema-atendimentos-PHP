<?php
$tituloPagina = 'Pessoas';
$paginaAtiva = 'pessoas';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pessoas</h1>
    <button class="btn btn-success" onclick="novaPessoa()">Novo</button>
</div>

<div id="alerta"></div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Documento</th>
                    <th>E-mail</th>
                    <th>Curso</th>
                    <th>Período</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaPessoas">
                <tr><td colspan="7" class="text-center py-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalPessoa" tabindex="-1">
    <div class="modal-dialog">
        <form id="formPessoa" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalPessoa">Nova pessoa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pessoaId" name="id">

                <div class="mb-3">
                    <label class="form-label" for="nome">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="documento">Documento *</label>
                        <input type="text" class="form-control" id="documento" name="documento" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">E-mail *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ativo">ativo</option>
                            <option value="inativo">inativo</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="curso">Curso</label>
                        <input type="text" class="form-control" id="curso" name="curso">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="periodo">Período</label>
                        <input type="text" class="form-control" id="periodo" name="periodo">
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="2"></textarea>
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
let modalPessoa;

document.addEventListener('DOMContentLoaded', () => {
    modalPessoa = new bootstrap.Modal(document.getElementById('modalPessoa'));

    document.getElementById('formPessoa').addEventListener('submit', async event => {
        event.preventDefault();
        const form = event.target;
        const id = document.getElementById('pessoaId').value;
        try {
            await AtendeLabApi.post('pessoas', id ? 'atualizar' : 'criar', new FormData(form));
            modalPessoa.hide();
            AtendeLabApi.showAlert('alerta', id ? 'Pessoa atualizada com sucesso.' : 'Pessoa cadastrada com sucesso.');
            await carregarPessoas();
        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    });

    carregarPessoas();
});

async function carregarPessoas() {
    const tbody = document.getElementById('tabelaPessoas');
    try {
        const dados = AtendeLabApi.toList(await AtendeLabApi.get('pessoas', 'listar'));
        if (!dados.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Nenhuma pessoa cadastrada.</td></tr>';
            return;
        }
        tbody.innerHTML = dados.map(p => `<tr>
            <td>${AtendeLabApi.escape(p.nome)}</td>
            <td>${AtendeLabApi.escape(p.documento)}</td>
            <td>${AtendeLabApi.escape(p.email)}</td>
            <td>${AtendeLabApi.escape(p.curso || '')}</td>
            <td>${AtendeLabApi.escape(p.periodo || '')}</td>
            <td><span class="badge ${p.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary'}">${AtendeLabApi.escape(p.status)}</span></td>
            <td class="text-end text-nowrap">
                <button class="btn btn-sm btn-outline-primary" onclick="editarPessoa(${Number(p.id)})">Editar</button>
                <button class="btn btn-sm btn-outline-danger ms-1" onclick="inativarPessoa(${Number(p.id)})">Inativar</button>
            </td>
        </tr>`).join('');
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${AtendeLabApi.escape(error.message)}</td></tr>`;
    }
}

function novaPessoa() {
    document.getElementById('formPessoa').reset();
    document.getElementById('pessoaId').value = '';
    document.getElementById('tituloModalPessoa').textContent = 'Nova pessoa';
    modalPessoa.show();
}

async function editarPessoa(id) {
    try {
        const p = AtendeLabApi.toObject(await AtendeLabApi.get('pessoas', 'buscarPorId', { id }));
        document.getElementById('pessoaId').value = p.id ?? '';
        document.getElementById('nome').value = p.nome ?? '';
        document.getElementById('documento').value = p.documento ?? '';
        document.getElementById('email').value = p.email ?? '';
        document.getElementById('telefone').value = p.telefone ?? '';
        document.getElementById('curso').value = p.curso ?? '';
        document.getElementById('periodo').value = p.periodo ?? '';
        document.getElementById('observacoes').value = p.observacoes ?? '';
        document.getElementById('status').value = p.status ?? 'ativo';
        document.getElementById('tituloModalPessoa').textContent = 'Editar pessoa';
        modalPessoa.show();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}

async function inativarPessoa(id) {
    if (!confirm('Inativar esta pessoa? O histórico será preservado.')) return;
    try {
        await AtendeLabApi.post('pessoas', 'inativar', { id });
        AtendeLabApi.showAlert('alerta', 'Pessoa inativada com sucesso.');
        await carregarPessoas();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
