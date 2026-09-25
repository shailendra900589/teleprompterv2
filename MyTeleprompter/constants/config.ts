import Constants from "expo-constants";

const extra = Constants.expoConfig?.extra as
  | { teleprompterApiUrl?: string }
  | undefined;

/** Override via app.json → expo.extra.teleprompterApiUrl for staging / custom hosts */
export const TELEPROMPTER_API_URL =
  extra?.teleprompterApiUrl ??
  "https://teleprompter.nectradigital.com/api?action=read";

export const POLL_INTERVAL_MS = 160;
export const FETCH_TIMEOUT_MS = 4000;
export const OFFLINE_AFTER_MISSES = 3;
/** Pixels per second at font size 45 for speed value 1. Matches the previous 40ms timer. */
export const SPEED_PX_PER_SEC_AT_REF_FONT = 6.25;
export const REF_FONT_SIZE = 45;
export const REMOTE_SCROLL_SYNC_THRESHOLD = 8;
