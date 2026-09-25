export type Rotation = "0deg" | "90deg" | "-90deg" | "180deg";
export type FontMode = "auto" | "manual";
export type TextAlign = "left" | "center" | "right";

export type TeleprompterPayload = {
  text?: string;
  fontSize?: number | string;
  fontMode?: string;
  speed?: number | string;
  margin?: number | string;
  scroll?: number | string;
  rotation?: string;
  bgColor?: string;
  textColor?: string;
  isMirroredX?: boolean | number | string;
  isMirroredY?: boolean | number | string;
  lineHeight?: number | string;
  textAlign?: string;
  cue?: number | string;
  countdown?: number | string;
  updatedAt?: number | string;
};

export type TeleprompterSettings = {
  script: string;
  fontSize: number;
  fontMode: FontMode;
  scrollSpeed: number;
  margin: number;
  rotation: Rotation;
  colors: { bg: string; text: string };
  isMirroredX: boolean;
  isMirroredY: boolean;
  remoteScroll: number | null;
  lineHeight: number;
  textAlign: TextAlign;
  cue: number;
  countdown: number;
};

export const DEFAULT_SETTINGS: TeleprompterSettings = {
  script: "",
  fontSize: 45,
  fontMode: "manual",
  scrollSpeed: 0,
  margin: 20,
  rotation: "0deg",
  colors: { bg: "#000000", text: "#ffffff" },
  isMirroredX: false,
  isMirroredY: false,
  remoteScroll: null,
  lineHeight: 1.5,
  textAlign: "center",
  cue: 0.4,
  countdown: 0,
};
