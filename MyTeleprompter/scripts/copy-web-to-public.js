/**
 * Copies dist-web into ../public_html/app for Hostinger deploy.
 */
const fs = require("fs");
const path = require("path");

const src = path.join(__dirname, "..", "dist-web");
const dest = path.join(__dirname, "..", "..", "public_html", "app");

function rmDir(dir) {
  if (!fs.existsSync(dir)) return;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, entry.name);
    if (entry.isDirectory()) rmDir(p);
    else fs.unlinkSync(p);
  }
  fs.rmdirSync(dir);
}

function copyDir(from, to) {
  fs.mkdirSync(to, { recursive: true });
  for (const entry of fs.readdirSync(from, { withFileTypes: true })) {
    const fromPath = path.join(from, entry.name);
    const toPath = path.join(to, entry.name);
    if (entry.isDirectory()) copyDir(fromPath, toPath);
    else fs.copyFileSync(fromPath, toPath);
  }
}

if (!fs.existsSync(src)) {
  console.error("Run expo export first (npm run build:web). Missing:", src);
  process.exit(1);
}

rmDir(dest);
copyDir(src, dest);
console.log("Copied teleprompter web app to", dest);
