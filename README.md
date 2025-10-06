# VIVA CONECTA (NEXUS Platform)

Plataforma web integrada que conecta pacientes, profissionais e clínicas em saúde, estética e bem-estar. Backend em PHP (REST + MVC) consumindo Supabase (PostgreSQL + Auth + Functions) e frontend em HTML/Tailwind UI-KIT mobile-first.

## Arquitetura

- **Backend**: PHP 8.1+, roteador simples, controllers, serviços especializados, repositórios para Supabase REST/PostgREST e Auth.
- **Banco**: Supabase (PostgreSQL) com schema completo (`supabase/sql/schema.sql`), políticas RLS e seeds de referência.
- **Edge Functions**: Placeholders para `whatsapp_webhook_handler`, `notifications_dispatcher`, `ranking_recalculate`, `availability_sync`.
- **Frontend**: Templates HTML/Tailwind responsivos (`frontend/templates`), cobrindo home, busca, perfis, dashboards, cupons, chat e flows de auth.
- **APIs**: Documentadas em `api/openapi.yaml`.

## Requisitos

- PHP 8.1+ com `ext-curl` (comando `php -m | findstr curl` no Windows).
- Composer.
- Conta Supabase ativa (usar projeto `nqkokcikbfwettwwxqbe`).
- Node/NPM opcionais para Tailwind local (não obrigatório).

## Configuração

1. **Variáveis de ambiente**
   ```bash
   cd backend
   cp .env.example .env
   # já preenchido com as chaves Supabase fornecidas
   ```
   Ajuste `WHATSAPP_WEBHOOK_SECRET`, `STRIPE_SECRET` e `MERCADO_PAGO_TOKEN`.

2. **Dependências PHP**
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
   Frontend estático: abra arquivos em `frontend/templates` ou sirva via `npx serve frontend/templates`.

## Estrutura de pastas

```
backend/
  public/index.php        # roteador e registro das rotas REST
  src/
    Core/                 # Application, Router, Container, Autoloader
    Controllers/          # Recursos REST (auth, busca, agendamentos, etc.)
    Services/             # Conexões Supabase (Auth, RPC, Functions)
    Repositories/         # Consultas e operações REST/PostgREST
    Http/                 # Request/Response helpers
    Support/              # Utilitários (Validator, HttpClient)
  .env.example            # modelo de variáveis
frontend/templates/       # Páginas Tailwind (home, dashboards, busca, chat...)
api/openapi.yaml          # documentação OpenAPI 3.0
supabase/sql/             # schema, policies, seeds
supabase/functions/       # edge functions (placeholders)
```

## Endpoints principais

- `POST /api/v1/auth/register` · cadastro por perfil (paciente/profissional/clínica).
- `POST /api/v1/auth/login` · sessão por senha (Supabase Auth).
- `GET /api/v1/search` · busca semântica com filtros.
- `GET /api/v1/professionals/:id` · perfil consolidado (view `professionals_view`).
- `POST /api/v1/appointments` · criação de agendamento com eventos e notificação.
- `POST /api/v1/reviews` · avaliações validadas após check-in.
- `GET /api/v1/coupons` · cupons ativos e validação (`/validate`).
- `POST /api/v1/chat/threads` / `POST /api/v1/chat/messages` · chat interno.
- `GET /api/v1/dashboard/{patient|professional|admin}` · RPCs para KPIs.
- `POST /api/v1/webhooks/whatsapp` · ingestão de webhooks (delegada à edge function).

Consulte `api/openapi.yaml` para detalhes de parâmetros e respostas.

## Frontend (UI-KIT Tailwind)

- **Home (`index.html`)**: hero, verticais, jornada, integrações.
- **Busca (`search.html`)**: filtros dinâmicos, resultados carregados via `/api/v1/search`.
- **Perfil (`professional.html`)**: detalhes, agenda, avaliações, cupons.
- **Dashboards**: `dashboard-patient.html`, `dashboard-professional.html`, `dashboard-admin.html`.
- **Flujos auxiliares**: `login.html`, `register.html`, `chat.html`, `coupons.html`.

As páginas já consomem os endpoints (fetch) e usam placeholders quando a API não está disponível.

## Roadmap (conforme PRD)

1. Autenticação + UI base ?
2. Busca + Agendamento ?
3. Dashboards + Avaliações ?
4. Cupons + Gamificação ?
5. Deploy + CI/CD (pendente) ? configurar GitHub Actions / pipelines.

## KPIs monitorados

- Tempo médio de agendamento < 90s (dashboard paciente).
- Conversão de busca > 25% (home highlight).
- NPS = 70 (home snapshot).
- Retenção > 60% (acompanhar via queries Supabase).
- Uptime = 99.5% (dashboard admin).

## Próximos passos sugeridos

- Implementar lógica real das edge functions (WhatsApp, notificações, ranking, disponibilidade).
- Criar testes automatizados (PHPUnit) para serviços/repos.
- Configurar CI/CD (GitHub Actions) e pipelines de deploy.
- Conectar frontend a um bundler (Vite/Tailwind CLI) se desejar componentes dinâmicos adicionais.

---

> Base de código gerada e documentada segundo o `PRD_VIVA_CONECTA_FINAL.md`.
