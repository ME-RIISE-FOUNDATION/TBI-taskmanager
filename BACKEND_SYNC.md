# Cross-Device Sync Backend

## The problem this fixes

Previously the whole app stored its data in the browser's `localStorage`. That is
per-browser and per-device, so a task marked **Completed** on one person's laptop
was never visible to anyone logging in elsewhere — there was no shared store to
sync through.

## How it works now

A small PHP API (`/api/data_api.php`) is the single shared source of truth.

```
Browser (any device)                     Server
┌────────────────────┐   bootstrap GET   ┌─────────────────────────┐
│ page load          │ ────────────────▶ │ data_api.php            │
│  await TBI.ready()  │ ◀──────────────── │   → Store (Sheets/JSON)  │
│  render from cache │   all entities    └─────────────────────────┘
│                    │
│ mark task complete │   append/update    ┌─────────────────────────┐
│  DB.updateById(...)│ ────────────────▶ │ same shared Store        │
└────────────────────┘   (fetch keepalive)└─────────────────────────┘
```

- **On every page load** the front-end pulls the full dataset from the server
  into `localStorage` (now just a cache), then renders. So every login sees the
  latest data.
- **On every change** (`DB.append/updateById/deleteById/_set`) the front-end
  writes the local cache *and* pushes the change to the server with
  `fetch(..., { keepalive: true })`, so the write completes even if the page
  reloads immediately after.

No page logic was rewritten — the `DB` layer in `assets/js/app.js` was made
server-aware, and each page now `await TBI.ready()` before rendering.

## Two storage drivers (automatic)

`api/Store.php` picks a driver at runtime:

| Condition | Driver | Notes |
|-----------|--------|-------|
| `SPREADSHEET_ID` **and** Google credentials are set | **Google Sheets** | Durable, survives redeploys. One tab per entity. |
| otherwise | **JSON files** in `data/*.json` | Zero-config. Good for a single persistent PHP host. |

> ⚠️ **On Railway/Render the container filesystem is ephemeral.** The JSON-file
> driver works while the container is alive but resets on every redeploy. For
> durable storage on those hosts you **must** configure Google Sheets (below),
> or attach a persistent volume mounted at `/var/www/html/data`.

## Enabling Google Sheets (durable, recommended for cloud)

1. Create a Google Cloud project, enable the **Google Sheets API**, create a
   **Service Account**, and download its JSON key.
2. Create a Google Sheet and **share it (Editor)** with the service account's
   `client_email`.
3. Provide the config to the server via environment variables:
   - `SPREADSHEET_ID` = the id from the sheet URL.
   - `GOOGLE_CREDENTIALS_BASE64` = `base64 -w 0 credentials.json`
     (or place the file at `config/credentials.json`).
   - `SETUP_KEY` = a secret of your choice (defaults to `TBI_SETUP_2024`).
4. Visit once: `https://your-app/setup/setup_sheets.php?key=YOUR_SETUP_KEY`
   — this creates the tabs and seeds them from `data/*.json`.
5. Set `SETUP_DONE=1` afterwards to disable the setup endpoint.

Deployment scaffolding (`Dockerfile`, `docker/start.sh`, `railway.json`,
`render.yaml`) is included; see `DEPLOY.md`.

## Local development

- Open the files over **http** (e.g. `php -S localhost:8000` from the project
  root) to exercise the real sync path against the JSON-file store.
- Opening via `file://` falls back to the old pure-`localStorage` mode (handy for
  quick UI work, but it does **not** sync — that's expected).

## Security notes / follow-ups

- `data/` and `config/` are blocked from direct web download in `.htaccess`
  (the API reads them server-side). Verify your host honours `.htaccess`
  (`AllowOverride All`), which the included `Dockerfile` sets.
- Login is still validated **client-side** against the pulled `users` list, and
  passwords are stored in plaintext — unchanged from before. This was kept to
  avoid changing the auth flow in this fix. Recommended next step: move login to
  a server endpoint and store bcrypt hashes (`password_hash`/`password_verify`).
- The store uses last-write-wins per record. For ~6 users this is fine; heavy
  concurrent editing of the same record could drop one edit.
