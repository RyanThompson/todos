# Plan — golden Todos to a running SALLT demo

**Status:** Approved v2.1. Demo path is implemented and covered by `GoldenDemoTest`. Recipe JSON is still version 1; categories and tags are extra domains, not a to-many pivot. Supersedes v1.
**App:** this repo is the golden monolith (front end and panel in one Laravel process). It is generated output, not hand-built.
**Starter:** `../SALLT` is the `composer create-project` target that the SDK TUI runs from. Mission and ownership: [`DOGFOOD.md`](DOGFOOD.md).

**Rule:** generators and formats live in the packages. This tree is regenerated from its committed recipe with `streams:onboard --apply`, then checked against itself. Plumbing is never hand-patched here.

---

## Target: the demo script

When every step below works on a fresh machine, the plan is done.

```bash
composer create-project ryanthompson/sallt todos      # Laravel 12 shell + streams/core + streams/sdk
cd todos
php artisan streams:onboard --recipe=todos             # or walk the TUI, or pipe JSON in. All three write the same streams.onboard.json
php artisan streams:onboard --apply                    # emits domains, API edge, Blade + Alpine page, panel, auth
npm install && npm run build
php artisan serve
```

1. **`/`**: one Blade view with Alpine that lists todos, creates them, and toggles `completed`. It does this through `@laravel-streams/api-client` against this app's Streams API. Requests are CSRF-protected same-origin calls with no other auth (bring your own middleware).
2. **`/admin`**: if the recipe says `auth: env-admin`, an SDK-emitted login stub checks `ADMIN_EMAIL` / `ADMIN_PASSWORD`. Those values are mapped from `.env` into a stream shape. With `auth: none` the panel is open, and with `byo` the user brings their own guard.
3. **Panel**: todos, categories and central tags are managed through **modal** add, edit and delete on manage tables. There are no create or edit pages.
4. `php artisan streams:validate` passes, and `composer golden:check` shows a fresh apply equals this repo.

---

## Review findings: what v1 assumed vs. what the code does

| v1 says | Reality (checked in the packages today) | Effect on the plan |
|---|---|---|
| "Manage UI applied" | The Panel PHP extends `App\Livewire\PanelPage`, which does not exist here. `streams/ui` and Livewire aren't required, and no panel provider or route is emitted. The files are emitted but **dead**. | New Phase 4: make the panel runnable before adding auth. |
| Phase 2: emit a `Todos` API resource | `streams-api` already ships generic `EntriesResource` (`/api/streams/{stream}/entries`: list, show, create, put, patch, delete, query). It registers through `API::routeEntries()`. Nothing per-stream is needed. | Phase 2 becomes enable, allowlist and CSRF, not "emit resources". |
| CSRF "just works" | API routes use the `api` middleware group by default, which has no session and no CSRF. `api-client` has no CSRF middleware, and its default `mode` is `cors`. | Phase 2 needs a `web`-group interface plus a client CSRF middleware. |
| Env format is a **filebase** format | Filebase is a *directory of documents*. A single `.env` file belongs in **`FileAdapter`** (the json/csv single-file source), which hard-codes formats in `if` chains. | Phase 5 first makes FileAdapter formats a registry, then adds `env`. |
| — | **Security:** the API has no stream allowlist and no authorization. If the API is enabled, any stream is readable by handle. That includes an env-backed admin stream, along with every other key in that file. `routeCrud()` would also expose create and delete for streams. | Phase 2 adds an allowlist, opt-in per stream. This blocks Phase 5. |
| Recipe is the schema | `streams.onboard.json` has no JSON Schema, and `fromArray()` drops unknown keys silently. There's no typed input path for an agent besides hand-writing the file. The golden repo **doesn't commit its recipe**. | New Phase 0. |
| Generators are generic | `ManageUiEmitter` hard-codes `$handle === 'todos' ? 'title' : 'name'`, so todo-specific logic sits in the SDK. Phase 1's `categories` and `tags` booleans would add more of it. | Recipe v2 uses generic `domains[]`. "Todos" becomes a preset data file. |
| — | `DomainScaffold` files aren't in `streams.lock`, so only UI nodes are hash-tracked. | Phase 0: everything emitted gets locked. |
| — | `RelationshipFieldType` is single-valued (FK). The `multiple` type that should cover many-to-many was removed from core in 2022 (`bbf53cbf6`). | Phase 6 adds a to-many relation (D4). |
| — | `streams-api` already has OpenAPI and Swagger (`resources/openapi`, `swagger/`). | "Postman-style access" is mostly docs later. Kept out of scope. |
| — | `SALLT/composer.json` path-repos `../_packages/streams-sdk` and pins a commit, so create-project fails off this machine. The package name is misspelled `ryantompson/*` in both repos. | Phase 0 (naming) and Phase 7 (publish). |

---

## Decisions (settled with Ryan, 2026-11-11)

| # | Question | Decision |
|---|---|---|
| D1 | How do `.env` keys map to entries? | **Mapped**: `ADMIN_EMAIL` becomes `email` on an admin-shaped stream. Use whichever is cheaper: (a) an `env` format on core's file adapter, or (b) a form handler the SDK emits, which reads and writes the keys through the stream's shape. Go with (b) if (a) needs adapter design that isn't defined yet. Qualify this at the start of Phase 5. |
| D2 | `.env` vs `.env.state` | `.env.state` holds working state and is seeded from `.env`. Apply and the panel never rewrite `.env`. |
| D3 | Where login lives | An **SDK login template**. Templates declare which stack they target, so this one's `streams-ui` flavor needs `streams/ui`, and other flavors can come later. Core and UI get no auth code of their own unless the template proves a gap. |
| D4 | Many-to-many for tags | Settled: **one to-many relation type with pivot support**. **Naming is open.** The old `multiple` type (removed in `bbf53cbf6`) stored an array of keys and resolved them with `IN`, with no pivot. It also reads too close to `multiselect`. Proposal: `relationship` with `config.multiple: true`, storing a key array by default, plus an opt-in `config.pivot: <stream>` when pivot data is needed. |
| D5 | Tenancy | It's an SDK template option only. The todos recipe leaves it out (`multi_tenancy: false`). |
| D6 | Generated vs hand-owned files | `#[Generated]` / lock-tracked files are regenerated. The Blade view, Alpine component and login stub are **seeded**: emitted once, then hand-owned (`--force` to re-emit). |

## Discovered and qualified as we go

Ryan's note: how streams are revealed, and which design systems and opt-ins exist, gets worked out **here**, against real failures. Each item is settled at the start of the phase that first needs it, then recorded as a short ADR note in ADR-SALLT and as a recipe or stream-schema key.

| Topic | First needed | Starting point |
|---|---|---|
| **Stream exposure**: how a stream opts in to the API, panel, MCP and client types | Phase 2 | Default closed. A stream is revealed per surface (`expose: { api, ui, mcp }` in the stream JSON, or the recipe allowlist). Pick one home. |
| **Template targets**: how an SDK template declares the stack it emits for (`streams-ui`, plain Blade, headless) | Phase 3 (front end), Phase 5 (login) | A `targets` key on template or kind registration. The recipe chooses the flavor, and apply refuses a flavor whose packages are missing. |
| **Design system** for the seeded Blade/Alpine views and panel theming | Phase 3 | Tailwind tokens. Qualify whether streams-ui theming and the public page share one source. |
| **Env mapping**: core adapter format vs SDK form handler (D1) | Phase 5 | Spike both for an hour, then pick one. |
| **`multiple` pivot shape**: where pivot rows live for file-based streams, and how the API returns and eager-loads them | Phase 6 | Read the removed 2022 implementation first. |

## Phases

### 0. Make the golden loop honest (SDK, SALLT, this repo)

- **Recipe schema:** add `onboard.schema.json` in the SDK (draft-07, same home as `app.schema.json`). The recipe gets a `$schema` key, is validated on read and write, and unknown keys are an error. `streams:schema` prints it.
- **Typed input:** add `streams:onboard --recipe=<preset|path|->`, where `-` reads JSON from stdin. TUI, flags, presets and stdin all produce one validated recipe.
- **Lock everything:** domain scaffold files go into `streams.lock` too, with a `generated` or `seeded` status (D6).
- **Golden check:** add `composer golden:check` here. It creates a temp app from `../SALLT`, copies the **committed** `streams.onboard.json`, runs `--apply`, and diffs every lock-tracked file against this repo. It fails on drift and becomes the CI gate.
- **Hygiene:** fix `ryantompson` → `ryanthompson` in both composer files and the docs. Drop "rename this repo" from the later list, since the split is settled: SALLT is the starter and todos is the output.

**Done:** this repo commits `streams.onboard.json`, and `golden:check` passes on today's (domain-only) emit.

### 1. Recipe v2 (SDK only)

The version bumps to `2`, and v1 files migrate on read.

| Key | Values | Notes |
|---|---|---|
| `frontend` | `none` \| `blade-alpine` | Replaces the bool and `public_client` from v1 |
| `panels` | int | Unchanged |
| `auth` | `none` \| `env-admin` \| `byo` | Only meaningful when `panels > 0`. `env-admin` selects the login template for the panel's stack (D3) |
| `api` | `{ enabled, middleware: web\|api, streams: [...] }` | The `streams` allowlist is what the API exposes |
| `domains` | `[{ name, fields?, relations?, taggable? }]` | Replaces `sample_domain`. Generic: no todo knowledge in emitters |
| `multi_tenancy` | bool | A template option only. False in the todos preset (D5) |
| `discover_streams_api`, `streams_api_base_url`, `surface` | Unchanged | |

- **Presets as data:** add `resources/recipes/todos.json` (Todos, Categories and Tags domains with `category_id` and `tags`). `--kitchen-sink` is a preset too. Later, user and package presets register through the same registry as kinds.
- Fix the `ManageUiEmitter` title field: it uses `stream.config.title_column`, else the first required string field.
- `plannedSteps()` marks each new step `pending-generator` until its emitter lands.

**Done:** `--recipe=todos --json --no-write` prints a schema-valid v2 recipe. Tests cover presets, migration and rejection of unknown keys.

### 2. API edge (streams-api, api-client, SDK)

- **Qualify stream exposure first** (see Discovered and qualified). Then, in **api**, an unexposed stream returns 404, checked before endpoint resolution. The emitted API uses `routeEntries()` only and never `routeCrud()`.
- **api:** the interface middleware can be set to the `web` group for first-party browser calls, so session and CSRF apply.
- **api-client:** add `CsrfMiddleware`. It reads `meta[name=csrf-token]` or the `XSRF-TOKEN` cookie and sets the header on non-GET requests. The client defaults to `mode: 'same-origin'` when `baseURL` is relative.
- **SDK step `api-edge`:** composer-require `streams/api`, write a provider line `API::routeEntries()` with the allowlist from the recipe, and set `STREAMS_API_ENABLED=true` / `STREAMS_API_MIDDLEWARE=web` in `.env.example`.

**Done:** a feature test here does `GET`/`POST`/`PATCH /api/streams/todos/entries`, and an unlisted stream returns 404. A client unit test asserts the CSRF header.

### 3. Public Blade + Alpine (SDK, api-client)

- **SDK step `frontend-blade-alpine`:** emits the route `/`, a controller that returns a Blade view, `resources/js/todos.js` (an Alpine component), and a Vite entry that imports the client with `CsrfMiddleware`. The Blade view and JS are **seeded** (D6).
- The page lists, creates and toggles `completed`. It has no business rules and no other framework.
- **Dev loop:** the npm dependency is `file:../_packages/api-client`, with `npm run build` run there first (exports point to `dist/`). The published version replaces it in Phase 7.

**Done:** a Playwright smoke test (load `/`, add a todo, toggle it, reload, see it persisted) plus a feature test for the page shell.

### 4. Panel actually runs (streams-ui, SDK)

- **SDK step `panel`:** composer-require `streams/ui` (which pulls in Livewire), emit a panel provider (path `/admin`, navigation from `ui.pages`), and emit or ship the `PanelPage` base. The base should live in streams-ui rather than `App\Livewire`, so generated code doesn't depend on an app class that doesn't exist.
- **Manage emitter:** add row **edit** and **delete** modal actions next to **add**. The edit form is derived from stream fields, not a hard-coded title field.
- **ui:** confirm that `HasActions` row actions mount modals with a bound entry. Patch it if they don't.

**Done:** `/admin/todos` lists the todos and adds, edits and deletes them through modals. A Livewire feature test covers each action.

### 5. Admin login from `.env` (SDK, plus core only if D1 picks the adapter)

- **Qualify D1:** compare an `env` format on core's `FileAdapter` with an SDK-emitted form handler mapped to the stream shape, and pick one.
  - *Adapter route (core):* move `FileAdapter` formats into `streams.core.sources.file.formats` (json and csv unchanged), add `env` with a `prefix` mapping, and fix the `config(' ', 'id')` typo in `writeData()`.
  - *Form-handler route (SDK):* an `admin` stream shape (`email`, `password:hash`) plus an emitted handler that reads and writes `ADMIN_*` keys in `.env.state` (D2).
- **SDK login template `auth/env-admin`**, with the `streams-ui` target first. It emits the login page or component, a guard check against the mapped values, panel middleware, `.env.example` keys and a `streams:admin:password` hash command. Apply refuses it if `streams/ui` is missing.
- The admin stream is **never exposed** (default closed, from Phase 2).

**Done:** the env mapping round-trips under test. Feature tests here: a wrong password is rejected, the right one opens `/admin`, and `GET /api/streams/admin/entries` returns 404.

### 6. Categories and central tags (core, SDK)

- **core:** add to-many relations per D4 (name per Ryan's call). Store a key array by default, with an opt-in pivot stream, and add API eager loading in `AppliesEagerLoading`.
- **SDK:** domain scaffolding loops over `domains[]`. `relations` emits to-one or to-many relation fields (D4 naming), and `taggable: true` adds a `tags` field pointing at the Tags domain. Tags owns tag rows, and other domains only hold keys.
- **Panel:** Categories and Tags get the same manage chain (modals). The Todos edit modal gets a category select and a tags multiselect.
- **Front end:** shows category and tags read-only via `?with[]=category&with[]=tags`.

**Done:** `--recipe=todos --apply` emits three domains, and you can create a tag and a category in the panel and attach both to a todo through modals.

### 7. Docs, MCP and publish (streams.dev, SALLT)

- **streams.dev docs:** one walkthrough page (create-project → onboard → apply → this app), an `sdk/onboard.md` reference generated from `onboard.schema.json`, and corrections to `api/authentication.md` covering CSRF and the allowlist.
- **streams.dev MCP** (`app/Mcp`): `GetSchema` serves `onboard.schema.json`, and a new `GetRecipePreset` tool lets agents start from a preset and author a valid recipe without calling the TUI.
- **Publish:** tag `streams/core`, `ui`, `api` and `sdk` on Packagist (or a VCS repository block in SALLT). Remove the path repo and commit pin from `SALLT/composer.json`, publish `@laravel-streams/api-client`, and re-point both dependencies here. The plan isn't done until create-project works off this machine.

---

## Explicitly later (recipe flags, not steps)

- Personal access tokens, a Postman collection (generated from the existing OpenAPI), and an MCP server toggle.
- Multi-panel and real tenant resolvers (the recipe already records them, and their emitters stay pending).
- Path 2 (JS-only create flow).

## Ownership by phase

| Phase | core | ui | api | sdk | api-client | streams.dev | SALLT | this repo |
|---|---|---|---|---|---|---|---|---|
| 0 | | | | ● | | | ● | ● |
| 1 | | | | ● | | | | ● |
| 2 | | | ● | ● | ● | | | ● |
| 3 | | | | ● | ● | | | ● |
| 4 | | ● | | ● | | | | ● |
| 5 | ○ | ○ | | ● | | | | ● |
| 6 | ● | | ● | ● | | | | ● |
| 7 | | | | | ● | ● | ● | ● |

○ = only if a qualification step shows that package is needed.

## How each phase runs

1. Settle any open "Discovered and qualified" item for this phase, and record it.
2. Write a failing test **here** that describes the demo behavior.
3. Implement it in the owning package, with that package's own test.
4. Run `streams:onboard --apply`, then `streams:validate`, then `composer golden:check`.
5. If something fails, fix the package, never app code. Commit the regenerated tree as the golden snapshot.
6. Mark the phase in this file (status line plus the last commands run).

## Order and critical path

0 → 1 → 2 → 3 is the **first clickable demo** (public page only).
4 → 5 gives the panel with login. Phase 5 depends on Phase 2's exposure rule.
6 can run in parallel with 4 and 5 once 1 lands. 7's docs track each phase, and the publish step closes the plan.
