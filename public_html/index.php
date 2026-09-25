<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Teleprompter Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="repair-hindi.js"></script>
    <style>
        :root {
            --bg: #0c0d10;
            --panel: #16181d;
            --panel-2: #1e2128;
            --line: #2c313a;
            --text: #f3f5f7;
            --muted: #9aa3af;
            --accent: #3d8bfd;
            --good: #1f9d55;
            --danger: #d64545;
            --warn: #e0a100;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: Inter, "Noto Sans Devanagari", "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
        }
        header h1 { font-size: 16px; margin: 0; letter-spacing: 0.02em; }
        header p { margin: 2px 0 0; color: var(--muted); font-size: 12px; }
        #status { font-size: 13px; font-weight: 700; color: var(--warn); white-space: nowrap; }
        #status.ok { color: var(--good); }
        #status.bad { color: var(--danger); }
        .layout {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(280px, 340px) 1fr;
            gap: 16px;
            padding: 16px;
            min-height: 0;
        }
        .controls, .script-area {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            min-height: 0;
        }
        .controls { overflow: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px; }
        .group { background: var(--panel-2); border-radius: 10px; padding: 12px; }
        .group h2 { margin: 0 0 8px; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
        label { display: block; font-size: 12px; color: var(--muted); margin: 8px 0 4px; }
        input[type="range"], select, input[type="color"] { width: 100%; }
        input[type="color"] { height: 34px; border: 0; background: transparent; padding: 0; }
        .row { display: flex; gap: 8px; }
        .row > * { flex: 1; }
        button, .file-btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 12px;
            font-weight: 700;
            cursor: pointer;
            color: white;
            background: #3a404b;
        }
        button.active, button.primary { background: var(--accent); }
        #playBtn { background: var(--good); width: 100%; }
        #playBtn.playing { background: var(--danger); }
        button.danger { background: var(--danger); width: 100%; }
        .file-btn { display: block; text-align: center; }
        input[type="file"] { display: none; }
        .check { display: flex; align-items: center; gap: 8px; color: var(--text); font-size: 13px; margin-top: 8px; }
        .script-area { display: flex; flex-direction: column; padding: 12px; }
        .script-meta { display: flex; justify-content: space-between; gap: 8px; color: var(--muted); font-size: 12px; margin-bottom: 8px; }
        textarea {
            flex: 1;
            width: 100%;
            min-height: 280px;
            resize: none;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #090a0c;
            color: var(--text);
            padding: 22px;
            font-family: "Noto Sans Devanagari", Inter, sans-serif;
            font-size: 22px;
            line-height: 1.55;
            outline: none;
        }
        textarea.drag { outline: 2px dashed var(--accent); }
        .hint { color: var(--muted); font-size: 12px; margin-top: 8px; }
        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .controls { max-height: none; }
            header { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Teleprompter Studio</h1>
            <p id="sourceLabel">Script syncs to the phone automatically</p>
        </div>
        <div id="status">Connecting…</div>
    </header>

    <main class="layout">
        <aside class="controls">
            <section class="group">
                <h2>Playback</h2>
                <button id="playBtn" type="button">Start</button>
                <label for="speed">Speed <span id="speedVal">0</span></label>
                <input id="speed" type="range" min="0" max="100" value="0">
                <label class="check"><input id="countdownOn" type="checkbox" checked> 3 second countdown</label>
            </section>

            <section class="group">
                <h2>Text</h2>
                <label class="check"><input id="fontAuto" type="checkbox"> Auto-fit text to the phone</label>
                <label for="fontSize">Font size <span id="fontVal">45</span></label>
                <input id="fontSize" type="range" min="18" max="140" value="45">
                <label for="lineHeight">Line spacing <span id="lineVal">1.50</span></label>
                <input id="lineHeight" type="range" min="1.15" max="2.2" step="0.05" value="1.5">
                <label for="margin">Side margin <span id="marginVal">20</span></label>
                <input id="margin" type="range" min="0" max="160" value="20">
                <label for="textAlign">Alignment</label>
                <select id="textAlign">
                    <option value="center">Center</option>
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                </select>
            </section>

            <section class="group">
                <h2>Display</h2>
                <label for="rotation">Phone orientation</label>
                <select id="rotation">
                    <option value="0deg">Portrait</option>
                    <option value="90deg">90° landscape</option>
                    <option value="-90deg">-90° landscape</option>
                    <option value="180deg">Upside down</option>
                </select>
                <label for="cue">Reading line <span id="cueVal">40%</span></label>
                <input id="cue" type="range" min="0.22" max="0.65" step="0.01" value="0.4">
                <div class="row" style="margin-top:8px">
                    <button id="btnMX" type="button">Mirror X</button>
                    <button id="btnMY" type="button">Mirror Y</button>
                </div>
                <div class="row" style="margin-top:8px">
                    <input id="bgColor" type="color" value="#000000" aria-label="Background color">
                    <input id="textColor" type="color" value="#ffffff" aria-label="Text color">
                </div>
            </section>

            <section class="group">
                <h2>Import</h2>
                <label class="file-btn" for="fileInput">Upload PDF or TXT</label>
                <input id="fileInput" type="file" accept=".pdf,.txt,application/pdf,text/plain">
                <p class="hint" id="importHint">Text PDFs are cleaned into readable paragraphs. Scanned image PDFs have no text to extract.</p>
                <button id="clearBtn" class="danger" type="button">Clear script</button>
            </section>
        </aside>

        <section class="script-area">
            <div class="script-meta">
                <span id="stats">0 words</span>
                <span id="positionLabel">0%</span>
                <span id="saveState"></span>
            </div>
            <textarea id="scriptBox" spellcheck="false" placeholder="Paste the script, or drop a PDF / TXT file here."></textarea>
            <p class="hint">Drag or scroll this script with the mouse. Stop holds the phone on that line. Start continues from the same line.</p>
        </section>
    </main>

    <script>
        const API = "/api";
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
        }

        const $ = (id) => document.getElementById(id);
        const scriptBox = $("scriptBox");
        let ready = false;
        let applying = false;
        let isPlaying = false;
        let lastSpeed = 20;
        let scrubbedWhilePaused = false;
        let playRaf = 0;
        let playLast = 0;
        let lastScrollPost = 0;
        let userHolding = false;
        let mirroredX = false;
        let mirroredY = false;
        let source = "text";
        let sourceName = "";
        let timer = null;
        let sending = false;
        let queued = false;

        function setStatus(text, kind) {
            const el = $("status");
            el.textContent = text;
            el.className = kind || "";
        }

        function snapshot() {
            return {
                text: scriptBox.value,
                fontSize: Number($("fontSize").value),
                fontMode: $("fontAuto").checked ? "auto" : "manual",
                speed: Number($("speed").value),
                margin: Number($("margin").value),
                lineHeight: Number($("lineHeight").value),
                textAlign: $("textAlign").value,
                rotation: $("rotation").value,
                cue: Number($("cue").value),
                bgColor: $("bgColor").value,
                textColor: $("textColor").value,
                isMirroredX: mirroredX,
                isMirroredY: mirroredY,
                countdown: 0,
                scroll: Math.round(scrollFraction() * 10000) / 10000,
                source,
                sourceName
            };
        }

        function updateLabels() {
            $("speedVal").textContent = $("speed").value;
            $("fontVal").textContent = $("fontSize").value;
            $("lineVal").textContent = Number($("lineHeight").value).toFixed(2);
            $("marginVal").textContent = $("margin").value;
            $("cueVal").textContent = Math.round(Number($("cue").value) * 100) + "%";
            $("fontSize").disabled = $("fontAuto").checked;
            const words = scriptBox.value.trim() ? scriptBox.value.trim().split(/\s+/).length : 0;
            const minutes = Math.max(1, Math.round(words / 130)) || 0;
            $("stats").textContent = words + " words · about " + (words ? minutes : 0) + " min";
            $("sourceLabel").textContent = sourceName ? (source.toUpperCase() + " · " + sourceName) : "Script syncs to the phone automatically";
        }

        async function flush() {
            if (!ready || applying) return;
            if (sending) { queued = true; return; }
            sending = true;
            $("saveState").textContent = "Saving…";
            const payload = snapshot();
            try {
                const res = await fetch(API + "?action=write", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) throw new Error("save failed");
                setStatus("Live", "ok");
                $("saveState").textContent = "Saved";
            } catch (err) {
                setStatus("Offline", "bad");
                $("saveState").textContent = "Not saved";
            } finally {
                sending = false;
                if (queued) { queued = false; flush(); }
            }
        }

        function scheduleSend(immediate) {
            if (!ready || applying) return;
            clearTimeout(timer);
            timer = setTimeout(flush, immediate ? 0 : 280);
        }

        async function postNow(extra) {
            clearTimeout(timer);
            if (sending) await new Promise((r) => setTimeout(r, 120));
            const payload = Object.assign(snapshot(), extra || {});
            const res = await fetch(API + "?action=write", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            if (!res.ok) throw new Error("save failed");
            setStatus("Live", "ok");
        }

        function scrollFraction() {
            const max = scriptBox.scrollHeight - scriptBox.clientHeight;
            if (max <= 0) return 0;
            return Math.min(1, Math.max(0, scriptBox.scrollTop / max));
        }

        function applyScrollFraction(fraction) {
            const max = scriptBox.scrollHeight - scriptBox.clientHeight;
            scriptBox.scrollTop = Math.max(0, fraction) * Math.max(0, max);
        }

        function setPlaying(playing) {
            isPlaying = playing;
            const btn = $("playBtn");
            btn.textContent = playing ? "Pause" : "Start";
            btn.classList.toggle("playing", playing);
            if (playing) startPlaybackLoop();
            else stopPlaybackLoop();
        }

        function pixelsPerSecond() {
            const speed = Number($("speed").value) || 0;
            const font = Number($("fontSize").value) || 45;
            return speed * 6.25 * (font / 45);
        }

        function stopPlaybackLoop() {
            if (playRaf) cancelAnimationFrame(playRaf);
            playRaf = 0;
        }

        function startPlaybackLoop() {
            if (playRaf) return;
            playLast = performance.now();
            const tick = (now) => {
                if (!isPlaying) {
                    playRaf = 0;
                    return;
                }
                const dt = Math.min(0.05, (now - playLast) / 1000);
                playLast = now;
                const max = scriptBox.scrollHeight - scriptBox.clientHeight;
                if (max > 0) {
                    applying = true;
                    scriptBox.scrollTop = Math.min(max, scriptBox.scrollTop + pixelsPerSecond() * dt);
                    applying = false;
                    updatePositionLabel();
                }
                maybePostScroll(Number($("speed").value), 90);
                playRaf = requestAnimationFrame(tick);
            };
            playRaf = requestAnimationFrame(tick);
        }

        function maybePostScroll(speed, minGap) {
            const now = performance.now();
            if (now - lastScrollPost < minGap) return;
            lastScrollPost = now;
            const scroll = Math.round(scrollFraction() * 10000) / 10000;
            fetch(API + "?action=write", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ scroll, speed })
            }).catch(() => setStatus("Offline", "bad"));
        }

        function updatePositionLabel() {
            const pct = Math.round(scrollFraction() * 100);
            const pos = $("positionLabel");
            if (pos) pos.textContent = pct + "%";
        }

        async function togglePlay() {
            if (isPlaying) {
                lastSpeed = Number($("speed").value) > 0 ? Number($("speed").value) : lastSpeed;
                $("speed").value = 0;
                setPlaying(false);
                updateLabels();
                await postNow({ speed: 0, countdown: 0, scroll: scrollFraction() });
                updatePositionLabel();
                return;
            }

            const speed = lastSpeed > 0 ? lastSpeed : 20;
            const scroll = scrollFraction();
            $("speed").value = speed;
            updateLabels();
            scrubbedWhilePaused = false;

            if ($("countdownOn").checked) {
                for (const n of [3, 2, 1]) {
                    await postNow({ speed: 0, countdown: n, scroll });
                    await new Promise((r) => setTimeout(r, 700));
                }
            }

            await postNow({ speed, countdown: 0, scroll });
            setPlaying(true);
        }

        function normalizePlain(text) {
            return text.replace(/\r\n/g, "\n").replace(/\r/g, "\n").replace(/\u000c/g, "\n\n").replace(/\n{3,}/g, "\n\n").trim();
        }

        function repairPdfText(raw) {
            let text = String(raw);
            text = text.replace(/\u05CC([\u0915-\u0939]\u093C?)/g, "$1\u093F");
            return text.replace(/[\u0530-\u058F\u0590-\u05FF\u1E08\u200B-\u200C\uFEFF]/g, "");
        }

        function normalizePdfText(raw) {
            const cleaned = (typeof repairHindi === "function" ? repairHindi : (s) => s)(normalizePlain(repairPdfText(raw)));
            const lines = cleaned.split("\n").map((line) => line.replace(/[ \t]+/g, " ").trim()).filter((line) => !/^\d{1,3}$/.test(line));
            const paragraphs = [];
            let buf = "";
            const endPunct = /[।.!?…:;"”’)\]]$/;
            lines.forEach((line) => {
                if (!line) {
                    if (buf) paragraphs.push(buf.trim());
                    buf = "";
                    return;
                }
                if (!buf) { buf = line; return; }
                const heading = line.length < 42 && /[:：]$/.test(line);
                if (!endPunct.test(buf) && !heading) {
                    buf = buf.endsWith("-") ? buf.slice(0, -1) + line : buf + " " + line;
                } else {
                    paragraphs.push(buf.trim());
                    buf = line;
                }
            });
            if (buf) paragraphs.push(buf.trim());
            return paragraphs.join("\n\n").trim();
        }

        function joinLine(items) {
            let out = "";
            let lastRight = null;
            items.forEach((item) => {
                const width = item.width > 0 ? item.width : 0;
                if (width > 0 && lastRight !== null) {
                    const gap = item.x - lastRight;
                    if (gap > Math.max(3, width * 0.45) && out && !out.endsWith(" ") && !item.str.startsWith(" ")) out += " ";
                }
                out += item.str;
                lastRight = width > 0 ? item.x + width : null;
            });
            return out.replace(/[ \t]+/g, " ").trim();
        }

        function linesFromItems(items) {
            const rows = [];
            let current = [];
            let currentY = null;
            items.filter((item) => item.str).forEach((item) => {
                const y = item.transform[5];
                const piece = { str: item.str, x: item.transform[4], width: item.width || 0 };
                if (currentY === null || Math.abs(y - currentY) > 4) {
                    if (current.length) rows.push(joinLine(current));
                    current = [piece];
                    currentY = y;
                } else {
                    current.push(piece);
                }
            });
            if (current.length) rows.push(joinLine(current));
            return rows.filter(Boolean);
        }

        async function extractPdf(file) {
            const data = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data }).promise;
            const pages = Math.min(pdf.numPages, 150);
            const chunks = [];
            for (let i = 1; i <= pages; i++) {
                $("importHint").textContent = "Reading PDF page " + i + " of " + pages + "…";
                const page = await pdf.getPage(i);
                const content = await page.getTextContent();
                chunks.push(linesFromItems(content.items).join("\n"));
            }
            if (pdf.numPages > pages) chunks.push("\n\n[Import stopped at 150 pages]");
            return normalizePdfText(chunks.join("\n\n"));
        }

        async function importFile(file) {
            if (!file) return;
            const name = file.name || "script";
            const isPdf = /pdf$/i.test(file.type) || /\.pdf$/i.test(name);
            try {
                let text = "";
                if (isPdf) {
                    text = await extractPdf(file);
                    source = "pdf";
                } else {
                    text = normalizePlain(await file.text());
                    source = "txt";
                }
                if (!text || text.replace(/\s/g, "").length < 5) {
                    $("importHint").textContent = "No text found. This PDF is probably a scan. Export it as text, or paste the script.";
                    source = "text";
                    sourceName = "";
                    return;
                }
                sourceName = name;
                scriptBox.value = text;
                scrubbedWhilePaused = true;
                applyScrollFraction(0);
                updateLabels();
                await postNow({ text, scroll: 0, speed: 0, source, sourceName, countdown: 0 });
                setPlaying(false);
                $("speed").value = 0;
                $("importHint").textContent = "Imported " + name + " and sent it to the phone.";
            } catch (err) {
                $("importHint").textContent = "Could not read that file. Try a text-based PDF or a TXT file.";
            }
        }

        async function hydrate() {
            try {
                const res = await fetch(API + "?action=read&t=" + Date.now(), { cache: "no-store" });
                if (!res.ok) throw new Error("read failed");
                const data = await res.json();
                applying = true;
                scriptBox.value = repairPdfText(data.text || "");
                $("fontSize").value = data.fontSize ?? 45;
                $("fontAuto").checked = data.fontMode === "auto";
                $("speed").value = data.speed ?? 0;
                $("margin").value = data.margin ?? 20;
                $("lineHeight").value = data.lineHeight ?? 1.5;
                $("textAlign").value = data.textAlign || "center";
                $("rotation").value = data.rotation || "0deg";
                $("cue").value = data.cue ?? 0.4;
                $("bgColor").value = data.bgColor || "#000000";
                $("textColor").value = data.textColor || "#ffffff";
                mirroredX = !!data.isMirroredX;
                mirroredY = !!data.isMirroredY;
                $("btnMX").classList.toggle("active", mirroredX);
                $("btnMY").classList.toggle("active", mirroredY);
                source = data.source || "text";
                sourceName = data.sourceName || "";
                lastSpeed = Number(data.speed) > 0 ? Number(data.speed) : 20;
                updateLabels();
                applyScrollFraction(Number(data.scroll) || 0);
                setPlaying(Number(data.speed) > 0);
                updatePositionLabel();
                applying = false;
                ready = true;
                setStatus("Live", "ok");
            } catch (err) {
                ready = true;
                setStatus("Offline", "bad");
                updateLabels();
            }
        }

        ["fontSize", "speed", "margin", "lineHeight", "cue"].forEach((id) => {
            $(id).addEventListener("input", () => {
                updateLabels();
                if (id === "speed") {
                    const value = Number($("speed").value);
                    setPlaying(value > 0);
                    if (value > 0) lastSpeed = value;
                }
                scheduleSend(false);
            });
        });
        ["textAlign", "rotation", "bgColor", "textColor", "fontAuto"].forEach((id) => {
            $(id).addEventListener("change", () => { updateLabels(); scheduleSend(true); });
        });

        scriptBox.addEventListener("input", () => {
            source = "text";
            sourceName = "";
            updateLabels();
            scheduleSend(false);
        });
        scriptBox.addEventListener("pointerdown", () => { userHolding = true; });
        window.addEventListener("pointerup", () => { userHolding = false; });
        scriptBox.addEventListener("scroll", () => {
            if (!ready || applying) return;
            updatePositionLabel();
            if (isPlaying && userHolding) {
                lastSpeed = Number($("speed").value) > 0 ? Number($("speed").value) : lastSpeed;
                $("speed").value = 0;
                setPlaying(false);
                updateLabels();
                postNow({ speed: 0, countdown: 0, scroll: scrollFraction() }).catch(() => setStatus("Offline", "bad"));
                return;
            }
            if (isPlaying) return;
            scrubbedWhilePaused = true;
            maybePostScroll(0, 40);
        });

        $("playBtn").addEventListener("click", () => togglePlay().catch(() => setStatus("Offline", "bad")));
        $("btnMX").addEventListener("click", () => { mirroredX = !mirroredX; $("btnMX").classList.toggle("active", mirroredX); scheduleSend(true); });
        $("btnMY").addEventListener("click", () => { mirroredY = !mirroredY; $("btnMY").classList.toggle("active", mirroredY); scheduleSend(true); });
        $("clearBtn").addEventListener("click", async () => {
            if (!confirm("Clear the script on the teleprompter?")) return;
            scriptBox.value = "";
            source = "text";
            sourceName = "";
            updateLabels();
            await postNow({ text: "", scroll: 0, speed: 0, source, sourceName });
            setPlaying(false);
            $("speed").value = 0;
        });
        $("fileInput").addEventListener("change", (event) => {
            const file = event.target.files && event.target.files[0];
            importFile(file);
            event.target.value = "";
        });

        ["dragenter", "dragover"].forEach((name) => scriptBox.addEventListener(name, (event) => {
            event.preventDefault();
            scriptBox.classList.add("drag");
        }));
        ["dragleave", "drop"].forEach((name) => scriptBox.addEventListener(name, (event) => {
            event.preventDefault();
            scriptBox.classList.remove("drag");
        }));
        scriptBox.addEventListener("drop", (event) => {
            const file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0];
            if (file) importFile(file);
        });

        document.addEventListener("keydown", (event) => {
            if (event.code !== "Space" || event.repeat) return;
            const tag = document.activeElement && document.activeElement.tagName;
            if (tag === "TEXTAREA" || tag === "INPUT" || tag === "SELECT") return;
            event.preventDefault();
            togglePlay().catch(() => setStatus("Offline", "bad"));
        });

        window.TeleprompterStudio = { normalizePdfText, linesFromItems, normalizePlain };
        hydrate();
    </script>
</body>
</html>
