<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" a*Copie o arquivo de ambiente:**
	```bash
	cp .env.example .envlt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


## Sobre o Projeto

Este projeto é um sistema de gestão desenvolvido em Laravel Blade, com autenticação de sessão nativa do Laravel.


### Funcionalidades
- Login e logout com sessão Laravel
- Proteção de rotas administrativas (/dashboard, /clientes, /planos, /alertas)
- Layout com partials (header e sidebar)
- Gerenciamento de clientes, planos e alertas
- Cadastro e gestão de equipamentos instalados para cada cliente

## Fluxo de Cadastro e Gestão de Equipamentos

1. **Acesse o Dashboard**
	- Clique em "Clientes" para acessar a lista de clientes.

2. **Lista de Clientes**
	- Veja todos os clientes cadastrados.
	- Para cada cliente, clique em "Ver Ficha" para acessar a ficha individual.

3. **Ficha do Cliente**
	- Exibe os dados do cliente selecionado.
	- Mostra a seção "Equipamentos Instalados" com a lista de equipamentos já cadastrados para o cliente.
	- Clique em "Adicionar Equipamento" para cadastrar um novo equipamento para este cliente.

4. **Cadastro de Equipamento**
	- Preencha o formulário com o nome do equipamento, morada (endereço) e ponto de referência.
	- O equipamento será vinculado automaticamente ao cliente selecionado.
	- Após o cadastro, você será redirecionado de volta à ficha do cliente, onde poderá ver o novo equipamento listado.

## Instalação e Execução

1. **Clone o repositório:**
	```bash
	git clone <repo-url>
	cd PROJECTO
	```
2. **Instale as dependências:**
	```bash
	composer install
	```
3. *
	```
4. **Gere a chave da aplicação:**
	```bash
	php artisan key:generate
	```
5. **Configure o banco de dados** no arquivo `.env` conforme seu ambiente.
6. **Execute as migrações:**
	```bash
	php artisan migrate
	```
7. **Crie um usuário admin:**
	Você pode usar tinker ou um seeder para criar um usuário manualmente:
	```bash
	php artisan tinker
	>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@email.com', 'password' => bcrypt('senha123')]);
	```
8. **Inicie o servidor:**
	```bash
	php artisan serve
	```
9. **Acesse no navegador:**
	[http://localhost:8000](http://localhost:8000)

## Autenticação

- Acesse `/login` para entrar no sistema.
- Após login, você será redirecionado para `/dashboard`.
- Todas as páginas administrativas exigem autenticação.
- Para sair, use o botão de logout (POST para `/logout`).

## Estrutura de Layout

- O layout base está em `resources/views/layouts/app.blade.php`.
- Partials: `resources/views/layouts/partials/header.blade.php` e `sidebar.blade.php`.
- As páginas principais estendem o layout base.

## Dúvidas

Consulte a [documentação do Laravel](https://laravel.com/docs) para mais detalhes.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Fluxos Detalhados do Sistema

### 1. Login e Acesso
- Acesse `/login` e entre com seu e-mail e senha cadastrados.
- Após login, você será redirecionado ao `/dashboard`.
- Todas as áreas administrativas exigem autenticação.

### 2. Dashboard
- Tela inicial após login, com atalhos para Clientes, Planos, Alertas e Estoque de Equipamentos.
- Menu "Relatórios" permite acessar o relatório de cobranças.
- Botão "Sair" faz logout seguro.

### 3. Gestão de Clientes
- Clique em "Clientes" no dashboard.
- Veja a lista de clientes cadastrados, com BI, nome, contato e ações.
- Para cadastrar um novo cliente, preencha o formulário e clique em "Cadastrar Cliente".
- Para editar, clique em "Editar" ao lado do cliente, altere os dados e salve.
- Para excluir, clique em "Excluir" e confirme.
- Para ver detalhes, clique em "Ver Ficha".

### 4. Gestão de Planos
- Clique em "Planos" no dashboard.
- Veja a lista de planos, com cliente, nome, descrição, preço, ciclo, ativação, vencimento e status.
- Para cadastrar um novo plano, preencha o formulário e clique em "Cadastrar Plano".
- Para editar, clique em "Editar" ao lado do plano, altere os dados e salve.
- Para excluir, clique em "Remover" e confirme.
- Status dos planos é exibido com badge colorido.

### 5. Relatório de Cobranças
- Acesse pelo menu "Relatórios > Cobrança".
- Veja todas as cobranças, com filtros avançados (cliente, descrição, status, valor, datas).
- Exporte o relatório para Excel.
- Status das cobranças é exibido com badge colorido.

### 6. Estoque de Equipamentos
- Clique em "Estoque de Equipamentos" no dashboard.
- Veja a lista de equipamentos em estoque, com nome, descrição, modelo, número de série e quantidade.
- Para cadastrar novo equipamento, clique em "Cadastrar Novo Equipamento" e preencha o formulário.
- Exporte o estoque para Excel.

### 7. Alertas
- Clique em "Alertas" no dashboard.
- Veja os alertas de vencimento de planos próximos.
- Filtros para quantidade de dias.

### 8. Navegação e Layout
- Todas as telas usam layout moderno, responsivo e consistente.
- Botões principais são amarelos, ações de exclusão em destaque.
- Tabelas com visual limpo, badges de status e feedback visual para ações.

### 9. Logout
- Use o botão "Sair" no dashboard para encerrar a sessão com segurança.

---
Esses fluxos garantem uma experiência intuitiva e moderna para a gestão de clientes, planos, cobranças, estoque e alertas.
