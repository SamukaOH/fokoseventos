# 🚀 Deploy do Fokos Eventos na Railway

## 1. Suba o código para o GitHub
Crie um repositório (ex.: `fokos-eventos`) e envie esta pasta inteira.

## 2. Crie o projeto na Railway
1. https://railway.com → **New Project** → **Deploy from GitHub repo** → selecione o repositório
2. A Railway detecta o `Dockerfile` automaticamente e builda o app

## 3. Adicione o MySQL
1. No projeto → **+ Create** → **Database** → **MySQL**
2. A Railway cria as variáveis `MYSQLHOST/MYSQLPORT/MYSQLUSER/MYSQLPASSWORD/MYSQLDATABASE`
3. No serviço do **app** → aba **Variables** → **Add Variable Reference** e referencie
   as 5 variáveis do MySQL (para o app enxergá-las)

## 4. Variáveis do app (aba Variables do serviço)
| Nome        | Valor |
|-------------|-------|
| `SETUP_KEY` | uma chave secreta qualquer (ex.: `fokos-2026-instalar`) |
| `APP_URL`   | *(opcional)* a URL pública — se omitir, usa `RAILWAY_PUBLIC_DOMAIN` automaticamente |

## 5. Gere o domínio público
Serviço do app → **Settings** → **Networking** → **Generate Domain**

## 6. Instale o banco (uma única vez)
Acesse: `https://SEU-DOMINIO.up.railway.app/setup.php?key=SUA_SETUP_KEY`
→ cria as 18 tabelas + dados demo + seu usuário admin.

## 7. Pronto!
Login: `smena.mena@icloud.com` / sua senha.
**Depois de instalar**: remova o `setup.php` do repositório (ou troque a `SETUP_KEY`).

## Rodando localmente (XAMPP) — continua igual
Sem variáveis de ambiente, o app usa os padrões: `http://localhost/fokos`, MySQL root local.
