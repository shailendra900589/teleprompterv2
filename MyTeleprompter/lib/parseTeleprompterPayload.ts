import type {
  FontMode,
  Rotation,
  TeleprompterPayload,
  TeleprompterSettings,
  TextAlign,
} from "@/types/teleprompter";

const ROTATIONS: Rotation[] = ["0deg", "90deg", "-90deg", "180deg"];

function toInt(value: number | string | undefined, fallback: number, min: number, max: number): number {
  if (value === undefined || value === "") return fallback;
  const n = typeof value === "number" ? value : parseInt(String(value), 10);
  if (!Number.isFinite(n)) return fallback;
  return Math.min(max, Math.max(min, n));
}

function toFloat(
  value: number | string | undefined,
  fallback: number,
  min: number,
  max: number
): number {
  if (value === undefined || value === "") return fallback;
  const n = typeof value === "number" ? value : parseFloat(String(value));
  if (!Number.isFinite(n)) return fallback;
  return Math.min(max, Math.max(min, n));
}

function toBool(value: boolean | number | string | undefined, fallback: boolean): boolean {
  if (value === undefined || value === "") return fallback;
  if (value === true || value === 1 || value === "1" || value === "true") return true;
  if (value === false || value === 0 || value === "0" || value === "false") return false;
  return fallback;
}

function toRotation(value: string | undefined, fallback: Rotation): Rotation {
  if (value && ROTATIONS.includes(value as Rotation)) return value as Rotation;
  return fallback;
}

function toFontMode(value: string | undefined, fallback: FontMode): FontMode {
  return value === "auto" || value === "manual" ? value : fallback;
}

function toAlign(value: string | undefined, fallback: TextAlign): TextAlign {
  return value === "left" || value === "center" || value === "right" ? value : fallback;
}

function toScroll(value: number | string | undefined, fallback: number | null): number | null {
  if (value === undefined || value === "") return fallback;
  const n = parseFloat(String(value));
  if (!Number.isFinite(n)) return fallback;
  return Math.round(Math.min(1, Math.max(0, n)) * 10000) / 10000;
}

export function parseTeleprompterPayload(
  data: TeleprompterPayload,
  prev: TeleprompterSettings
): TeleprompterSettings {
  return {
    script: data.text !== undefined ? String(data.text) : prev.script,
    fontSize: toInt(data.fontSize, prev.fontSize, 12, 200),
    fontMode: toFontMode(data.fontMode, prev.fontMode),
    scrollSpeed: toInt(data.speed, prev.scrollSpeed, 0, 100),
    margin: toInt(data.margin, prev.margin, 0, 240),
    rotation: toRotation(data.rotation, prev.rotation),
    colors: {
      bg: data.bgColor ?? prev.colors.bg,
      text: data.textColor ?? prev.colors.text,
    },
    isMirroredX: toBool(data.isMirroredX, prev.isMirroredX),
    isMirroredY: toBool(data.isMirroredY, prev.isMirroredY),
    remoteScroll: toScroll(data.scroll, prev.remoteScroll),
    lineHeight: toFloat(data.lineHeight, prev.lineHeight, 1.1, 2.4),
    textAlign: toAlign(data.textAlign, prev.textAlign),
    cue: toFloat(data.cue, prev.cue, 0.2, 0.7),
    countdown: toInt(data.countdown, prev.countdown, 0, 10),
  };
}

export function settingsEqual(a: TeleprompterSettings, b: TeleprompterSettings): boolean {
  return (
    a.script === b.script &&
    a.fontSize === b.fontSize &&
    a.fontMode === b.fontMode &&
    a.scrollSpeed === b.scrollSpeed &&
    a.margin === b.margin &&
    a.rotation === b.rotation &&
    a.colors.bg === b.colors.bg &&
    a.colors.text === b.colors.text &&
    a.isMirroredX === b.isMirroredX &&
    a.isMirroredY === b.isMirroredY &&
    a.remoteScroll === b.remoteScroll &&
    a.lineHeight === b.lineHeight &&
    a.textAlign === b.textAlign &&
    a.cue === b.cue &&
    a.countdown === b.countdown
  );
}
