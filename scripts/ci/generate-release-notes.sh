#!/usr/bin/env bash
set -euo pipefail

# Genera notas de release desde commits desde el último tag (Plan CI/CD Fase 3).
# Uso: scripts/ci/generate-release-notes.sh [output.md]

OUTPUT="${1:-RELEASE_NOTES.md}"
PREV_TAG="$(git describe --tags --abbrev=0 2>/dev/null || echo '')"

{
  echo "# Release notes"
  echo
  if [[ -n "${PREV_TAG}" ]]; then
    echo "_Changes since \`${PREV_TAG}\`_"
    echo
    git log "${PREV_TAG}..HEAD" --pretty=format:'- %s (%h)' --no-merges
  else
    echo "_Initial release (no previous tag)_"
    echo
    git log -n 50 --pretty=format:'- %s (%h)' --no-merges
  fi
  echo
} > "${OUTPUT}"

echo "Release notes written to ${OUTPUT}"
