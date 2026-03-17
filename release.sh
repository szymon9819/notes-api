#!/bin/sh

set -eu

tag="${1:-}"

if [ -z "$tag" ]; then
    echo "Usage: ./release.sh v0.0.1"
    exit 1
fi

if ! printf '%s\n' "$tag" | grep -Eq '^v[0-9]+\.[0-9]+\.[0-9]+$'; then
    echo "TAG must match vMAJOR.MINOR.PATCH"
    exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
    echo "Working tree must be clean before creating a release tag."
    exit 1
fi

if [ "$(git branch --show-current)" != "master" ]; then
    echo "Check out master before creating a release tag."
    exit 1
fi

if git rev-parse "$tag" >/dev/null 2>&1; then
    echo "Tag $tag already exists locally."
    exit 1
fi

git pull --ff-only origin master
git tag -a "$tag" -m "Release $tag"
git push origin "$tag"
