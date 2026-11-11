# Dogfood mission — golden Todos (monolith)

This app is the **public demonstration** of SALLT: one Laravel process, front and panel, generated as far as possible from `streams:onboard` (recipe = schema; TUI and `--json` are the same options). Hand edits that generators can emit get rebased onto a fresh `--apply`.

Canon: [ADR-SALLT](https://github.com/RyanThompson/ADR-SALLT). Starter shell: [SALLT](https://github.com/RyanThompson/SALLT) (`composer create-project` → SDK TUI).

## What “done” looks like here

1. **Front** — one Blade page + Alpine. `@laravel-streams/api-client` posts to **this app’s** `streams/api` (CSRF; otherwise open internal API — bring your own middleware).
2. **Panel** — Streams UI, **modal** workflows (not extra pages) for todos, categories, and **central tags**.
3. **Login** — optional onboard choice: `ADMIN_EMAIL` / `ADMIN_PASSWORD` (hash). Storage is a stream whose source is the **file** adapter + an **env** (key=value) format over `.env.state` (seeded from `.env`). It is never exposed through the API. Or skip (bring your own auth).
4. **Later SDK options** (not this slice): personal access tokens, Postman-shaped docs, MCP. Same recipe keys, applied after the first build.

## Already emitted (kitchen-sink `--apply`)

| Step | Where |
|---|---|
| Domain `Todos` (model, repository, lang, `streams/todos.json`) | this app |
| Manage UI tree + Panel PHP (page, table, modal add) | this app, but **not runnable yet** (no `streams/ui`, no panel provider, `App\Livewire\PanelPage` missing) |
| `streams/api`, Alpine client page, tenancy, env-stream auth | **not generators yet** |

`streams/ui` and `streams/api` are still Composer requirements the app must pull; UI PHP was emitted ahead of the package being required.

## Where the work belongs

| Surface | Owns | Does not own |
|---|---|---|
| **streams-core** | Entries, source adapters: **file** adapter formats (json/csv + **env key=value**), filebase formats, field types, `streams` user provider | UI, HTTP API, TUI |
| **streams-ui** | Panel builders: tables, forms, **modal** actions, panel auth hooks | Domain rules, public Blade |
| **streams-api** | Generic entries REST edge (`/api/streams/{stream}/entries`), **stream allowlist**, `web`-group (CSRF) interfaces | Panel, Alpine markup |
| **streams-sdk** | `streams:onboard` recipe + `--apply` emitters (domain, manage UI, **next: api resource, alpine page, env-admin option**) | Business todos logic |
| **@laravel-streams/api-client** | Browser client used by the Blade/Alpine page, including CSRF middleware | Laravel routes |
| **streams.dev** | Docs + MCP that match the commands and schema the TUI writes | App-specific todos copy |
| **This repo** | Golden output of those generators; dogfood only | Forking package behavior |

## Onboarding tree (recipe keys)

`streams:onboard` already asks: frontend? panel? how many? multi-tenant? discover an existing API or emit one? sample Todos?

Add when we implement them (same JSON, TUI or `--json`):

- `auth`: `none` \| `env-admin` \| `byo`
- `public_client`: blade+alpine → own API (default when `frontend` && `api`)
- `tag_domain` / `categories`: central tags + categories (modal CRUD)
- Later: `tokens`, `mcp`

Default / kitchen-sink: frontend + 1 panel + API + sample Todos + env-admin + tags/categories. Enter-through-defaults stays the minimal recipe (no UI).

## Build order

See [`PLAN.md`](PLAN.md). That file is the only ordered list.

Work **from this app’s failures**: run `--apply`, fix the package that should have emitted the missing file, regenerate here, and run `composer golden:check`.
