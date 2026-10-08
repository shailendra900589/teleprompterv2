# Teleprompter — server deploy (Hostinger)

## What goes on the server

Upload **only** the contents of `public_html/` into your domain’s `public_html` folder:

- `index.php` — studio (browser controller)
- `api.php` — JSON API (hidden URL: `/api`)
- `repair-hindi.js` — PDF Hindi cleanup
- `.htaccess` — clean URLs, cache headers, protect `data.json`
- `app/` — **built phone display** (Expo web export; no npm on phone)
- `data.json` — created automatically on first API hit (or copy from `data.json.example`)

**URLs (no `.php` in the browser):**

| Use | URL |
|-----|-----|
| Studio | `https://teleprompter.nectradigital.com/` |
| API read | `https://teleprompter.nectradigital.com/api?action=read` |
| API write | `POST https://teleprompter.nectradigital.com/api?action=write` |
| Health | `https://teleprompter.nectradigital.com/api?action=ping` |
| Phone display | `https://teleprompter.nectradigital.com/app/` |

Mobile app and `/app/` read the same API URL (see `MyTeleprompter/app.json` → `extra.teleprompterApiUrl`).

Rebuild display app after code changes: `cd MyTeleprompter && npm run build:web` (see `APP-USE-HINDI.md`).

## First-time server setup (SSH)

Replace `YOUR_DOMAIN` with your Hostinger domain path.

```bash
cd ~/domains/teleprompter.nectradigital.com/public_html

# If data.json does not exist yet:
cp data.json.example data.json
chmod 664 data.json
```

Ensure PHP 8+ and `mod_rewrite` are enabled (Hostinger: usually on by default).

## Git workflow (this repo)

### On your PC (after you add GitHub remote)

```bash
cd C:\Users\uuu\OneDrive\Desktop\prompter
git init
git add .
git commit -m "Teleprompter studio + API deploy ready"
git branch -M main
git remote add origin https://github.com/shailendra900589/teleprompterv2.git
git push -u origin main
```

### On Hostinger — first clone + deploy backend

```bash
cd ~
git clone https://github.com/shailendra900589/teleprompterv2.git teleprompter-repo
cd ~/domains/teleprompter.nectradigital.com/public_html

# Copy backend files (keeps existing data.json if already there)
cp -n ~/teleprompter-repo/public_html/data.json.example ./data.json 2>/dev/null || true
rsync -av --exclude='data.json' ~/teleprompter-repo/public_html/ ./
chmod 664 data.json 2>/dev/null || true
```

### On Hostinger — update after `git push`

```bash
cd ~/teleprompter-repo
git pull origin main
rsync -av --exclude='data.json' public_html/ ~/domains/teleprompter.nectradigital.com/public_html/
```

One-liner (after repo is cloned once):

```bash
cd ~/teleprompter-repo && git pull origin main && rsync -av --exclude='data.json' public_html/ ~/domains/teleprompter.nectradigital.com/public_html/
```

## Verify after deploy

```bash
curl -s "https://teleprompter.nectradigital.com/api?action=ping"
curl -sI "https://teleprompter.nectradigital.com/api.php?action=ping" | head -1
```

Expect JSON `{"status":"ok"}` and a **301** redirect from `api.php` to `/api`.

## Notes

- Do **not** commit `public_html/data.json` (it is in `.gitignore`); server keeps live script state.
- Rebuild/reinstall the Expo app after changing `teleprompterApiUrl` in `app.json`.
