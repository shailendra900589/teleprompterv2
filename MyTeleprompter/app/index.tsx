import React from "react";

import { TeleprompterView } from "@/components/teleprompter/TeleprompterView";
import { useTeleprompterSync } from "@/hooks/useTeleprompterSync";

export default function TeleprompterScreen() {
  const { settings, status } = useTeleprompterSync();
  return <TeleprompterView settings={settings} status={status} />;
}
