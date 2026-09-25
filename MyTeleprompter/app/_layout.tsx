import { Stack } from "expo-router";
import { useKeepAwake } from "expo-keep-awake";
import { useEffect } from "react";
import { SafeAreaProvider } from "react-native-safe-area-context";
import * as SystemUI from "expo-system-ui";
import "react-native-reanimated";

function KeepAwakeGuard() {
  useKeepAwake();
  return null;
}

export default function RootLayout() {
  useEffect(() => {
    void SystemUI.setBackgroundColorAsync("#000000");
  }, []);

  return (
    <SafeAreaProvider>
      <KeepAwakeGuard />
      <Stack
        screenOptions={{
          headerShown: false,
          animation: "none",
          contentStyle: { backgroundColor: "#000000" },
        }}
      >
        <Stack.Screen name="index" />
      </Stack>
    </SafeAreaProvider>
  );
}
