# Fokos Eventos v7 — pronto para Railway ☁️

> Deploy na nuvem: veja **RAILWAY.md**. Local (XAMPP) continua funcionando sem configuração.

# v5 — ERP System

> **v5 — Design System "Fokos Deck"**: novo DESENHO de layout em dark moderno —
> sidebar flutuante destacada das bordas (painel arredondado com ícones em tiles),
> topbar transparente fundida ao conteúdo com ações em cápsula, dashboard recomposto
> com KPI-herói amarelo em destaque, cards com etiquetas flutuantes, kanban de raias
> tracejadas com fichas elevadas e dock flutuante no mobile. Tipografia Sora + Inter.
> Login preservado. Mesma lógica, mesmas funcionalidades — outro desenho.

> **v2 — Design System "Fokos Glass"**: interface totalmente repaginada com glassmorphism
> (estilo Apple), 100% responsiva (PC, tablet e mobile), com todos os componentes visuais
> unificados em um único design system (`public/assets/css/app.css`). Todas as
> funcionalidades da v1 foram preservadas.
>
> **Mudanças da v2:**
> - Novo design system global: cards, tabelas, badges, botões, modais (bottom-sheet no mobile), toasts, kanban, calendário e grid de letreiros padronizados entre todas as telas
> - `mobile.css` consolidado dentro do `app.css` (arquivo mantido apenas por compatibilidade)
> - Correção: hash das senhas de demonstração no `database.sql` (antes inválido)
> - Correção: coluna `criado_em` ambígua em `/api/financeiro/dashboard`
> - Correção de segurança: `/api/motoristas` não expõe mais hash de senha e tokens
> - Área do motorista e tela de login com o mesmo visual glass
> - Safe-areas do iOS, inputs sem zoom no iPhone e suporte a `prefers-reduced-motion`

Sistema ERP operacional completo para a **Fokos Eventos**, empresa especializada em letreiros iluminados, estruturas para eventos e logística operacional.

---

## Stack

- **Backend:** PHP 8+ (arquitetura MVC)
- **Banco de dados:** MySQL 8+ / MariaDB 10.4+
- **Frontend:** HTML5, CSS3, JavaScript puro, AJAX/Fetch
- **Compatível com:** XAMPP, Laragon, WAMP, qualquer ambiente PHP+MySQL

---

## Instalação no XAMPP

### 1. Copiar arquivos

Extraia ou clone o projeto na pasta `htdocs` do XAMPP:

```
C:\xampp\htdocs\fokos\
```

Ou no Linux/Mac:
```
/opt/lampp/htdocs/fokos/
```

### 2. Configurar o banco de dados

1. Abra o **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Crie um banco chamado `fokos_eventos`
3. Importe o arquivo SQL: `fokos/database.sql`

### 3. Configurar a aplicação

Edite o arquivo `app/config/config.php`:

```php
define('DB_HOST',   'localhost');
define('DB_NAME',   'fokos_eventos');
define('DB_USER',   'root');       // seu usuário MySQL
define('DB_PASS',   '');           // sua senha MySQL
define('APP_URL',   'http://localhost/fokos');
```

### 4. Configurar uploads (opcional)

Crie a pasta de uploads com permissão de escrita:
```
/public/uploads/
```

No Windows ela já deve funcionar. No Linux/Mac:
```bash
chmod -R 775 public/uploads/
```

### 5. Acessar o sistema

Abra no navegador:
```
http://localhost/fokos
```

---

## Credenciais padrão

| Tipo      | E-mail                  | Senha    |
|-----------|-------------------------|----------|
| Admin     | admin@fokos.com         | password |
| Motorista | motorista@fokos.com     | password |

> ⚠️ **Troque as senhas após o primeiro acesso!**

---

## Módulos

### Admin
| Módulo       | URL                          | Descrição                              |
|-------------|------------------------------|----------------------------------------|
| Dashboard   | `/dashboard`                 | Visão geral, gráficos, KPIs            |
| Demandas    | `/demandas`                  | Gerenciamento de eventos (kanban)      |
| Estoque     | `/estoque`                   | Produtos, movimentações, histórico     |
| Financeiro  | `/financeiro`                | Receitas, despesas, fluxo de caixa     |
| Motoristas  | `/motoristas`                | Equipe de entrega, disponibilidade     |

### Motorista (área mobile)
| Módulo     | URL          | Descrição                            |
|-----------|--------------|--------------------------------------|
| Área do motorista | `/motorista` | Interface simplificada e touch-friendly |

---

## Estrutura de pastas

```
fokos/
├── app/
│   ├── config/          # Configurações e banco de dados
│   ├── controllers/     # Lógica de cada módulo
│   ├── helpers/         # Funções utilitárias (auth, CSRF, etc.)
│   ├── middlewares/     # Router e middlewares
│   └── views/           # Templates PHP por módulo
│       ├── auth/
│       ├── components/  # sidebar, topbar
│       ├── dashboard/
│       ├── demandas/
│       ├── estoque/
│       ├── financeiro/
│       ├── motorista/   # Área exclusiva do motorista
│       └── motoristas/  # Gestão de motoristas (admin)
├── public/
│   └── assets/
│       ├── css/         # Design system completo
│       ├── js/          # JavaScript modular
│       └── uploads/     # Uploads de fotos
├── database.sql         # Schema + dados de demonstração
├── index.php            # Front controller
└── .htaccess            # Reescrita de URLs
```

---

## Segurança

- Proteção CSRF em todos os formulários
- Prepared statements (prevenção SQL Injection)
- Escape de saída (prevenção XSS)
- Hash bcrypt nas senhas (custo 12)
- Rate limiting no login (5 tentativas / 15 min)
- Controle de sessão seguro
- Logs de atividade

---

## Requisitos mínimos

- PHP 8.0+
- MySQL 8.0+ / MariaDB 10.4+
- Extensões PHP: `pdo`, `pdo_mysql`, `mbstring`, `gd`, `fileinfo`
- Módulo Apache: `mod_rewrite` habilitado

---

## Solução de problemas

**Página em branco ou erro 500**
- Verifique se `mod_rewrite` está ativo no Apache
- Confira as credenciais em `app/config/config.php`
- Verifique os logs do Apache em `xampp/apache/logs/error.log`

**Uploads não funcionam**
- Crie a pasta `public/uploads/` manualmente
- No Linux, ajuste permissões: `chmod 775 public/uploads/`

**Erro "Banco não encontrado"**
- Confirme que importou `database.sql` no phpMyAdmin
- Verifique se o nome do banco é exatamente `fokos_eventos`

---

## Desenvolvido para

**Fokos Eventos** — Letreiros iluminados, estruturas e logística operacional.

Sistema desenvolvido com PHP puro (sem framework), arquitetura MVC, sem dependências de Composer. Instalação simples e direta no XAMPP.
