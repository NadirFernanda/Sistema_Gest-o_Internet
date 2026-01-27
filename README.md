<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

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
3. **Copie o arquivo de ambiente:**
	```bash
	cp .env.example .env
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
