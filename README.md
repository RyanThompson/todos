# 🧂 Todos (golden)

Public golden app for **SALLT** generation. Regenerated from:

```bash
composer create-project ryanthompson/sallt …
php artisan streams:onboard --kitchen-sink --apply
```

Do not hand-maintain domain/UI plumbing here — rebase onto a fresh `--apply` when generators change.

## Current emit

| Step | Status |
|---|---|
| Domain (`Todos` Entry, repository, lang, `streams/todos.json`) | applied |
| Manage UI (page / table / modal add + Panel PHP) | applied |
| `streams/api`, Alpine, tenancy | pending generators |

## Validate

```bash
php artisan streams:validate
```

## Canon

[ADR-SALLT](https://github.com/RyanThompson/ADR-SALLT) · starter [SALLT](https://github.com/RyanThompson/SALLT)

## License

MIT
