# Teleprompter app — phone par kaise chalayein

Aapke PC par `npm` / Expo se problem ho rahi thi, isliye **app already build karke** server par deploy ke liye taiyar hai.

## Sabse aasaan tareeka (recommended)

1. Hostinger File Manager se **`public_html`** folder ki files upload karein (purani guide `DEPLOY.md` dekhein).
2. Phone ke browser mein kholein:

   **https://teleprompter.nectradigital.com/app/**

3. Studio control ke liye (PDF upload, start/stop):

   **https://teleprompter.nectradigital.com/**

4. Phone par **Add to Home screen** / **Install app** karein — teleprompter full-screen jaisa chalega (APK ki zaroorat nahi).

## Login bhool gaye? (APK ke liye)

Main aapke purane password ya account **use nahi kar sakta** — Expo APK sirf **aapke** account se cloud par banti hai. Do raaste:

### A) Purana account wapas lo (recommended)

1. Browser: [https://expo.dev/forgot-password](https://expo.dev/forgot-password)
2. Wahi email daalo jisse pehle Expo banaya tha.
3. Naya password set karke PC par:

```powershell
cd C:\Users\uuu\OneDrive\Desktop\prompter\MyTeleprompter
npx eas login
npm run build:apk
```

### B) Naya free Expo account (purana recover na ho)

1. [https://expo.dev/signup](https://expo.dev/signup) — naya account (free).
2. Same commands:

```powershell
cd C:\Users\uuu\OneDrive\Desktop\prompter\MyTeleprompter
npx eas login
npx eas init
npm run build:apk
```

`eas init` naye account se project link karega (pehli baar puchhe to confirm karo).

### Password yaad hai, sirf terminal login nahi karna?

1. Login karke: [Access tokens](https://expo.dev/settings/access-tokens) → naya token.
2. PowerShell (token paste karo):

```powershell
cd C:\Users\uuu\OneDrive\Desktop\prompter\MyTeleprompter
$env:EXPO_TOKEN="apna-token-yahan"
npm run build:apk
```

Build khatam hone par [expo.dev](https://expo.dev) → project → **Download APK**.

**Bina kisi Expo login ke:** upar wala **`/app/`** web display use karo — yeh pehle se build ho chuka hai.

## Latest Android APK (EAS preview)

- **Local file (PC):** `releases/Nectra-Teleprompter-preview.apk` (~82 MB)
- **Expo dashboard:** [Build logs & download](https://expo.dev/accounts/rahulknow598/projects/nectra-teleprompter/builds/4d90260e-b385-4a1d-81a7-28ab257af9fc)
- **Direct link:** https://expo.dev/artifacts/eas/nbXcVDsNZTlHLIZS9wP6B-s_JSA2-XAPOogyofPItnA.apk (link ~2 hafte tak valid)

Phone par: APK copy karke install karein (“Unknown sources” allow karna pad sakta hai).

## Web app dubara build karna

```powershell
cd C:\Users\uuu\OneDrive\Desktop\prompter\MyTeleprompter
npm install
npm run build:web
```

Phir `public_html/app` folder dubara upload karein.

## Local test (developer)

```powershell
npm run start
```

**Mat chalayein:** `npm audit fix --force` — isse Expo toot jata hai.
