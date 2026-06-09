# AtendeLab
Sistema de Controle de Atendimentos Acadêmicos desenvolvido na disciplina de Fábrica de
Software.

## Tecnologias utilizadas
- PHP 8.x
- MySQL
- phpMyAdmin
- HTML
- CSS
- Bootstrap
- Git e GitHub

## Funcionalidades previstas
- Página pública
- Login
- Dashboard
- Cadastro de pessoas atendidas
- Cadastro de tipos de atendimento
- Registro de atendimentos
- Relatórios

## Como executar localmente
1. Clonar o repositório.
2. Colocar a pasta no htdocs do XAMPP.
3. Iniciar Apache e MySQL.
4. Criar o banco atendelab.
5. Importar o script database/atendelab.sql.
6. Acessar http://localhost/atendelab/public/

## Acesso ao sistema (login)
- A aplicação abre direto na tela de login (`?controller=auth&action=login`).
- Usuário de teste padrão:
  - E-mail: `admin@atendelab.com`
  - Senha: `123456`
- As senhas são armazenadas com `password_hash()` e validadas com `password_verify()`.
- Páginas internas (dashboard e CRUDs) exigem sessão ativa; sem login o acesso é
  redirecionado para a tela de login. Use o botão **Sair** para encerrar a sessão.