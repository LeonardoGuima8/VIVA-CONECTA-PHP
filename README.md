# VIVA CONECTA (NEXUS Platform)

Plataforma web integrada que conecta pacientes, profissionais e cl�nicas em sa�de, est�tica e bem-estar. Backend em PHP (REST + MVC) consumindo Supabase (PostgreSQL + Auth + Functions) e frontend em HTML/Tailwind UI-KIT mobile-first.

## Arquitetura

- **Backend**: PHP 8.1+, roteador simples, controllers, servi�os especializados, reposit�rios para Supabase REST/PostgREST e Auth.
- **Banco**: Supabase (PostgreSQL) com schema completo (`supabase/sql/schema.sql`), pol�ticas RLS e seeds de refer�ncia.
- **Edge Functions**: Placeholders para `whatsapp_webhook_handler`, `notifications_dispatcher`, `ranking_recalculate`, `availability_sync`.
- **Frontend**: Templates HTML/Tailwind responsivos (`frontend/templates`), cobrindo home, busca, perfis, dashboards, cupons, chat e flows de auth.
- **APIs**: Documentadas em `api/openapi.yaml`.

## Requisitos

- PHP 8.1+ com `ext-curl` (comando `php -m | findstr curl` no Windows).
- Composer.
- Conta Supabase ativa (usar projeto `nqkokcikbfwettwwxqbe`).
- Node/NPM opcionais para Tailwind local (n�o obrigat�rio).

## Configura��o

1. **Vari�veis de ambiente**
   ```bash
   cd backend
   cp .env.example .env
   # j� preenchido com as chaves Supabase fornecidas
   ```
   Ajuste `WHATSAPP_WEBHOOK_SECRET`, `STRIPE_SECRET` e `MERCADO_PAGO_TOKEN`.

2. **Depend�ncias PHP**
   ```bash
   composer install
   ```

3. **Banco de dados**
   ```bash
   supabase db push --file supabase/sql/schema.sql
   supabase db push --file supabase/sql/policies.sql
   supabase db push --file supabase/sql/seeds/seed.sql
   ```
   Ou utilize o console SQL do Supabase para executar os scripts.

4. **Edge Functions** (placeholders prontos)
   ```bash
   supabase functions deploy whatsapp_webhook_handler
   supabase functions deploy notifications_dispatcher
   supabase functions deploy ranking_recalculate
   supabase functions deploy availability_sync
   ```

5. **Servidor de desenvolvimento**
   ```bash
   cd backend
   composer start   # `php -S localhost:8080 -t public`
   ```
   Frontend est�tico: abra arquivos em `frontend/templates` ou sirva via `npx serve frontend/templates`.

## Estrutura de pastas

```
backend/
  public/index.php        # roteador e registro das rotas REST
  src/
    Core/                 # Application, Router, Container, Autoloader
    Controllers/          # Recursos REST (auth, busca, agendamentos, etc.)
    Services/             # Conex�es Supabase (Auth, RPC, Functions)
    Repositories/         # Consultas e opera��es REST/PostgREST
    Http/                 # Request/Response helpers
    Support/              # Utilit�rios (Validator, HttpClient)
  .env.example            # modelo de vari�veis
frontend/templates/       # P�ginas Tailwind (home, dashboards, busca, chat...)
api/openapi.yaml          # documenta��o OpenAPI 3.0
supabase/sql/             # schema, policies, seeds
supabase/functions/       # edge functions (placeholders)
```

## Endpoints principais

- `POST /api/v1/auth/register` � cadastro por perfil (paciente/profissional/cl�nica).
- `POST /api/v1/auth/login` � sess�o por senha (Supabase Auth).
- `GET /api/v1/search` � busca sem�ntica com filtros.
- `GET /api/v1/professionals/:id` � perfil consolidado (view `professionals_view`).
- `POST /api/v1/appointments` � cria��o de agendamento com eventos e notifica��o.
- `POST /api/v1/reviews` � avalia��es validadas ap�s check-in.
- `GET /api/v1/coupons` � cupons ativos e valida��o (`/validate`).
- `POST /api/v1/chat/threads` / `POST /api/v1/chat/messages` � chat interno.
- `GET /api/v1/dashboard/{patient|professional|admin}` � RPCs para KPIs.
- `POST /api/v1/webhooks/whatsapp` � ingest�o de webhooks (delegada � edge function).

Consulte `api/openapi.yaml` para detalhes de par�metros e respostas.

## Frontend (UI-KIT Tailwind)

- **Home (`index.html`)**: hero, verticais, jornada, integra��es.
- **Busca (`search.html`)**: filtros din�micos, resultados carregados via `/api/v1/search`.
- **Perfil (`professional.html`)**: detalhes, agenda, avalia��es, cupons.
- **Dashboards**: `dashboard-patient.html`, `dashboard-professional.html`, `dashboard-admin.html`.
- **Flujos auxiliares**: `login.html`, `register.html`, `chat.html`, `coupons.html`.

As p�ginas j� consomem os endpoints (fetch) e usam placeholders quando a API n�o est� dispon�vel.

## Roadmap (conforme PRD)

1. Autentica��o + UI base ?
2. Busca + Agendamento ?
3. Dashboards + Avalia��es ?
4. Cupons + Gamifica��o ?
5. Deploy + CI/CD (pendente) ? configurar GitHub Actions / pipelines.

## KPIs monitorados

- Tempo m�dio de agendamento < 90s (dashboard paciente).
- Convers�o de busca > 25% (home highlight).
- NPS = 70 (home snapshot).
- Reten��o > 60% (acompanhar via queries Supabase).
- Uptime = 99.5% (dashboard admin).

## Pr�ximos passos sugeridos

- Implementar l�gica real das edge functions (WhatsApp, notifica��es, ranking, disponibilidade).
- Criar testes automatizados (PHPUnit) para servi�os/repos.
- Configurar CI/CD (GitHub Actions) e pipelines de deploy.
- Conectar frontend a um bundler (Vite/Tailwind CLI) se desejar componentes din�micos adicionais.

---

> Base de c�digo gerada e documentada segundo o `PRD_VIVA_CONECTA_FINAL.md`.
