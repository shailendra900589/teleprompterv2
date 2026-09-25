const DEVANAGARI = /[\u0900-\u097F]/;
const BASE_WIDTH = 390;

function clamp(n: number, min: number, max: number): number {
  return Math.min(max, Math.max(min, n));
}

export function fittedMargin(margin: number, viewportWidth: number): number {
  return Math.max(0, Math.min(margin, Math.round(viewportWidth * 0.16)));
}

/** Fit the longest readable line to the screen. Paragraphs wrap instead of shrinking to unreadability. */
export function autoFitFontSize(script: string, viewportWidth: number, margin: number): number {
  const pad = 20 + fittedMargin(margin, viewportWidth);
  const available = Math.max(80, viewportWidth - pad * 2);
  const lines = script
    .split("\n")
    .map((line) => line.trim())
    .filter((line) => line.length > 0)
    .slice(0, 80);

  const hasDeva = DEVANAGARI.test(script);
  if (lines.length === 0) return clamp(Math.round(available / 14), 28, 72);

  const longest = lines.reduce((max, line) => Math.max(max, Array.from(line).length), 1);
  const factor = hasDeva ? 0.58 : 0.5;
  const target = Math.min(Math.max(longest, 8), hasDeva ? 28 : 36);
  return clamp(Math.round(available / (target * factor)), 20, hasDeva ? 84 : 96);
}

export function resolveFontSize(
  fontSize: number,
  fontMode: "auto" | "manual",
  script: string,
  viewportWidth: number,
  margin: number
): number {
  if (fontMode === "auto") return autoFitFontSize(script, viewportWidth, margin);

  const widthScale = clamp(viewportWidth / BASE_WIDTH, 0.82, 1.45);
  const cap = Math.max(22, viewportWidth / 7);
  return clamp(Math.round(fontSize * widthScale), 18, cap);
}

export function resolveLineHeight(lineHeight: number, script: string): number {
  if (DEVANAGARI.test(script)) return Math.max(lineHeight, 1.48);
  return lineHeight;
}

export function repairPdfText(script: string): string {
  return script
    .replace(/\u05CC([\u0915-\u0939]\u093C?)/g, "$1\u093F")
    .replace(/[\u0530-\u058F\u0590-\u05FF\u1E08\u200B-\u200C\uFEFF]/g, "");
}

export function displayParagraphs(script: string): string[] {
  const cleaned = repairPdfText(script)
    .replace(/\u000c/g, "\n\n")
    .replace(/\r\n/g, "\n")
    .replace(/\n{3,}/g, "\n\n")
    .trim();
  if (!cleaned) return [];
  return cleaned.split(/\n{2,}/).map((block) => block.trim()).filter(Boolean);
}
