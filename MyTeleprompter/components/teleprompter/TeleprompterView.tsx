import React, { useMemo, useRef, useState } from "react";
import {
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  useWindowDimensions,
  View,
} from "react-native";
import { useSafeAreaInsets } from "react-native-safe-area-context";

import { useAutoScroll } from "@/hooks/useAutoScroll";
import type { SyncStatus } from "@/hooks/useTeleprompterSync";
import { displayParagraphs, fittedMargin, resolveFontSize, resolveLineHeight } from "@/lib/resolveLayout";
import type { TeleprompterSettings } from "@/types/teleprompter";

type Props = {
  settings: TeleprompterSettings;
  status: SyncStatus;
};

export function TeleprompterView({ settings }: Props) {
  const { width: screenWidth, height: screenHeight } = useWindowDimensions();
  const insets = useSafeAreaInsets();
  const scrollViewRef = useRef<ScrollView>(null);
  const [contentHeight, setContentHeight] = useState(0);

  const safeRotation = settings.rotation || "0deg";
  const isLandscape = safeRotation === "90deg" || safeRotation === "-90deg";
  const viewportWidth = isLandscape ? screenHeight : screenWidth;
  const viewportHeight = isLandscape ? screenWidth : screenHeight;

  const margin = fittedMargin(settings.margin, viewportWidth);
  const fontSize = resolveFontSize(
    settings.fontSize,
    settings.fontMode,
    settings.script,
    viewportWidth,
    settings.margin
  );
  const lineHeight = resolveLineHeight(settings.lineHeight, settings.script);
  const paragraphs = useMemo(() => displayParagraphs(settings.script), [settings.script]);
  const scrollSpeed = settings.scrollSpeed;
  const playing = scrollSpeed > 0 && settings.countdown <= 0;

  const cueOffset =
    safeRotation === "0deg"
      ? Math.max(insets.top + 16, viewportHeight * settings.cue)
      : viewportHeight * settings.cue;
  const readingOffset = Math.max(0, cueOffset - (fontSize * lineHeight) / 2);

  const { onScrollY, onScrollBeginDrag, onScrollEndDrag } = useAutoScroll({
    scrollSpeed: playing ? scrollSpeed : 0,
    fontSize,
    contentHeight,
    viewportHeight,
    remoteScroll: settings.remoteScroll,
    script: settings.script,
    scrollRef: scrollViewRef,
  });

  const containerStyle = {
    width: viewportWidth,
    height: viewportHeight,
    overflow: "hidden" as const,
    backgroundColor: settings.colors.bg,
    transform: [
      { rotate: safeRotation },
      { scaleX: settings.isMirroredX ? -1 : 1 },
      { scaleY: settings.isMirroredY ? -1 : 1 },
    ] as const,
    position: "absolute" as const,
    left: isLandscape ? (screenWidth - screenHeight) / 2 : 0,
    top: isLandscape ? (screenHeight - screenWidth) / 2 : 0,
  };

  return (
    <View style={[styles.container, { backgroundColor: settings.colors.bg }]}>
      <StatusBar hidden />

      <View style={containerStyle}>
        <ScrollView
          ref={scrollViewRef}
          style={styles.scroller}
          scrollEnabled={!playing}
          onContentSizeChange={(_w, h) => setContentHeight(h)}
          onScroll={(e) => onScrollY(e.nativeEvent.contentOffset.y)}
          onScrollBeginDrag={onScrollBeginDrag}
          onScrollEndDrag={onScrollEndDrag}
          scrollEventThrottle={16}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingHorizontal: 20 + margin }}
        >
          <View style={{ height: readingOffset }} />

          {paragraphs.map((paragraph, index) => (
              <Text
                key={`${index}-${paragraph.slice(0, 24)}`}
                maxFontSizeMultiplier={1}
                style={[
                  styles.script,
                  {
                    color: settings.colors.text,
                    fontSize,
                    lineHeight: fontSize * lineHeight,
                    textAlign: settings.textAlign,
                    marginBottom: index === paragraphs.length - 1 ? 0 : fontSize * 0.7,
                  },
                ]}
              >
                {paragraph}
              </Text>
          ))}

          <View style={{ height: viewportHeight }} />
        </ScrollView>

        <View pointerEvents="none" style={[styles.cue, { top: cueOffset }]}>
          <View style={styles.cueLine} />
        </View>

        {settings.countdown > 0 ? (
          <View style={styles.countdown} pointerEvents="none">
            <Text style={[styles.countdownText, { color: settings.colors.text }]}>{settings.countdown}</Text>
          </View>
        ) : null}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scroller: {
    flex: 1,
  },
  script: {
    fontWeight: "500",
  },
  cue: {
    position: "absolute",
    left: 0,
    right: 0,
    height: 2,
    zIndex: 5,
    justifyContent: "center",
  },
  cueLine: {
    height: 2,
    backgroundColor: "rgba(255, 77, 90, 0.9)",
  },
  countdown: {
    ...StyleSheet.absoluteFillObject,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "rgba(0,0,0,0.45)",
    zIndex: 6,
  },
  countdownText: {
    fontSize: 160,
    fontWeight: "700",
  },
});
