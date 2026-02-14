#!/usr/bin/env bash
# Collect manifest, asset checksums, git head and recent access log lines related to /clientes
# Usage: sudo bash scripts/check_parity.sh [optional-base-url]

set -euo pipefail
HOST_ARG=${1:-}
OUTFILE="parity_$(hostname -s).txt"
: > "$OUTFILE"

echo "=== Host: $(hostname -s) ($(date -u)) ===" | tee -a "$OUTFILE"

echo "\n--- public/build/manifest.json ---" | tee -a "$OUTFILE"
if [ -f public/build/manifest.json ]; then
  cat public/build/manifest.json | tee -a "$OUTFILE"
else
  echo "(no public/build/manifest.json)" | tee -a "$OUTFILE"
fi

echo "\n--- sha256sum of public/build/assets/* ---" | tee -a "$OUTFILE"
if ls public/build/assets/* >/dev/null 2>&1; then
  sha256sum public/build/assets/* 2>/dev/null | sort | tee -a "$OUTFILE"
else
  echo "(no assets directory or no files)" | tee -a "$OUTFILE"
fi

echo "\n--- git HEAD (short) ---" | tee -a "$OUTFILE"
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  git rev-parse --short HEAD 2>/dev/null | tee -a "$OUTFILE"
else
  echo "(not a git repo)" | tee -a "$OUTFILE"
fi

echo "\n--- file mtimes for public/build/assets/* ---" | tee -a "$OUTFILE"
if ls public/build/assets/* >/dev/null 2>&1; then
  stat -c "%n %y" public/build/assets/* 2>/dev/null | tee -a "$OUTFILE"
fi

# Nginx access log lines referencing /clientes (last 200)
echo "\n--- nginx access log: last 200 lines with /clientes (requires read access) ---" | tee -a "$OUTFILE"
if [ -f /var/log/nginx/access.log ]; then
  sudo grep "/clientes" /var/log/nginx/access.log 2>/dev/null | tail -n 200 | tee -a "$OUTFILE"
else
  echo "(no /var/log/nginx/access.log on this host)" | tee -a "$OUTFILE"
fi

# If provided, curl the host/clientes to capture response headers (helps identify backend headers)
if [ -n "$HOST_ARG" ]; then
  echo "\n--- curl -I $HOST_ARG/clientes (response headers) ---" | tee -a "$OUTFILE"
  curl -s -D - "$HOST_ARG/clientes" -o /dev/null | sed -n '1,200p' | tee -a "$OUTFILE"
fi

# Also attempt a localhost request to see what this nginx returns
echo "\n--- curl -I http://127.0.0.1/clientes (local request headers) ---" | tee -a "$OUTFILE"
curl -s -D - http://127.0.0.1/clientes -o /dev/null | sed -n '1,200p' | tee -a "$OUTFILE"

echo "\nOutput written to $OUTFILE"

exit 0
