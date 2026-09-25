import { useEffect, useState } from "react";
import { AppState, type AppStateStatus } from "react-native";

import {
  FETCH_TIMEOUT_MS,
  OFFLINE_AFTER_MISSES,
  POLL_INTERVAL_MS,
  TELEPROMPTER_API_URL,
} from "@/constants/config";
import { parseTeleprompterPayload, settingsEqual } from "@/lib/parseTeleprompterPayload";
import {
  DEFAULT_SETTINGS,
  type TeleprompterPayload,
  type TeleprompterSettings,
} from "@/types/teleprompter";

export type SyncStatus = "connecting" | "live" | "offline";

export function useTeleprompterSync() {
  const [settings, setSettings] = useState<TeleprompterSettings>(DEFAULT_SETTINGS);
  const [status, setStatus] = useState<SyncStatus>("connecting");
  const [lastUpdated, setLastUpdated] = useState<number | null>(null);

  useEffect(() => {
    let interval: ReturnType<typeof setInterval> | null = null;
    let cancelled = false;
    let misses = 0;

    const fetchData = async () => {
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), FETCH_TIMEOUT_MS);
      try {
        const uniqueUrl = `${TELEPROMPTER_API_URL}&t=${Date.now()}`;
        const response = await fetch(uniqueUrl, {
          headers: { "Cache-Control": "no-cache", Pragma: "no-cache" },
          cache: "no-store",
          signal: controller.signal,
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = (await response.json()) as TeleprompterPayload;
        if (cancelled) return;

        misses = 0;
        setSettings((prev) => {
          const next = parseTeleprompterPayload(data, prev);
          return settingsEqual(prev, next) ? prev : next;
        });
        setStatus("live");
        setLastUpdated(Date.now());
      } catch {
        if (cancelled) return;
        misses += 1;
        if (misses >= OFFLINE_AFTER_MISSES) setStatus("offline");
      } finally {
        clearTimeout(timer);
      }
    };

    const startPolling = () => {
      if (interval) return;
      void fetchData();
      interval = setInterval(fetchData, POLL_INTERVAL_MS);
    };

    const stopPolling = () => {
      if (interval) {
        clearInterval(interval);
        interval = null;
      }
    };

    const onAppState = (next: AppStateStatus) => {
      if (next === "active") startPolling();
      else stopPolling();
    };

    startPolling();
    const sub = AppState.addEventListener("change", onAppState);

    return () => {
      cancelled = true;
      stopPolling();
      sub.remove();
    };
  }, []);

  return { settings, status, lastUpdated };
}
