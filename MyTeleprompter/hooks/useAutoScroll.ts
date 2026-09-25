import { useEffect, useRef } from "react";
import type { ScrollView } from "react-native";


type Options = {
  scrollSpeed: number;
  fontSize: number;
  contentHeight: number;
  viewportHeight: number;
  remoteScroll: number | null;
  script: string;
  scrollRef: React.RefObject<ScrollView | null>;
};

export function useAutoScroll({
  scrollSpeed,
  fontSize,
  contentHeight,
  viewportHeight,
  remoteScroll,
  script,
  scrollRef,
}: Options) {
  const currentScrollY = useRef(0);
  const latest = useRef({ scrollSpeed, fontSize, contentHeight, viewportHeight, remoteScroll });
  latest.current = { scrollSpeed, fontSize, contentHeight, viewportHeight, remoteScroll };

  const scriptRef = useRef(script);
  const pendingReseed = useRef(true);
  const userDragging = useRef(false);
  const targetY = useRef(0);

  if (scriptRef.current !== script) {
    scriptRef.current = script;
    pendingReseed.current = true;
  }

  const maxScrollOf = (height: number, viewport: number) => Math.max(0, height - viewport);

  useEffect(() => {
    if (contentHeight <= 0 || remoteScroll == null) return;
    const max = maxScrollOf(contentHeight, viewportHeight);
    const nextTarget = remoteScroll * max;
    targetY.current = nextTarget;
    if (!pendingReseed.current) return;
    currentScrollY.current = nextTarget;
    scrollRef.current?.scrollTo({ y: nextTarget, animated: false });
    pendingReseed.current = false;
  }, [script, contentHeight, viewportHeight, remoteScroll, scrollRef]);

  useEffect(() => {
    let raf = 0;
    let last = performance.now();

    const loop = (now: number) => {
      const dt = Math.min(0.05, (now - last) / 1000);
      last = now;
      const { contentHeight: height, viewportHeight: viewport, remoteScroll: remote } = latest.current;
      const max = maxScrollOf(height, viewport);

      if (!userDragging.current && max > 0 && remote != null && !pendingReseed.current) {
        targetY.current = remote * max;
        const delta = targetY.current - currentScrollY.current;
        if (Math.abs(delta) > 0.3) {
          const gain = 1 - Math.exp(-16 * dt);
          currentScrollY.current += delta * gain;
          if (Math.abs(targetY.current - currentScrollY.current) < 0.4) {
            currentScrollY.current = targetY.current;
          }
          scrollRef.current?.scrollTo({ y: currentScrollY.current, animated: false });
        }
      }

      raf = requestAnimationFrame(loop);
    };

    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, [scrollRef]);

  return {
    onScrollY: (y: number) => {
      if (userDragging.current) currentScrollY.current = y;
    },
    onScrollBeginDrag: () => {
      userDragging.current = true;
    },
    onScrollEndDrag: () => {
      userDragging.current = false;
      targetY.current = currentScrollY.current;
    },
  };
}
