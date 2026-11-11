# 🧂 Todos (golden)

Public golden app for **SALLT** generation. Regenerated from:

```bash
composer create-project ryanthompson/sallt …
php artisan streams:onboard --recipe=todos --apply
```

Do not hand-maintain domain/UI plumbing here — rebase onto a fresh `--apply` when generators change.

## Walk through

| URL | What |
|---|---|
| `/` | Blade + Alpine. Adds and toggles todos through this app's API |
| `/admin/login` | `admin@todos.test` / `password` (hash in `.env.state`) |
| `/admin` | Panel. Todos manage page uses a modal add action |
| `/api/streams/todos/entries` | Allowlisted Streams API (`todos`, `categories`, `tags`). Other handles are 404 |

```bash
php artisan streams:validate
php artisan test --filter=GoldenDemoTest
composer golden:check
```

## Canon and docs

[ADR-SALLT](https://github.com/RyanThompson/ADR-SALLT) · starter [SALLT](https://github.com/RyanThompson/SALLT) · onboard write-up lives in the streams.dev repo at `docs/packages/sdk/onboard.md`

## License

MIT
