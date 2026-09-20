#!/bin/sh
set -eu

USERNAME="${USERNAME:-rekanized}"
TAG="${1:-latest}"

# Build both artifacts successfully before publishing either one.
docker build --pull -t "$USERNAME/homeplanner-app:$TAG" .
docker build --pull -t "$USERNAME/homeplanner-nginx:$TAG" -f Dockerfile.nginx .
docker push "$USERNAME/homeplanner-app:$TAG"
docker push "$USERNAME/homeplanner-nginx:$TAG"

printf 'Published both images with tag %s.\n' "$TAG"
