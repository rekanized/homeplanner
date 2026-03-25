#!/bin/sh

# Exit on error
set -e

USERNAME="rekanized"
TAG="latest"

echo "🚀 Building Homeplanner images for $USERNAME..."

# Build the application image
echo "📦 Building App image..."
docker build -t $USERNAME/homeplanner-app:$TAG .

# Build the Nginx image
echo "📦 Building Nginx image..."
docker build -t $USERNAME/homeplanner-nginx:$TAG -f Dockerfile.nginx .

echo "🔑 Please ensure you are logged into Docker Hub (run 'docker login')."

# Push the images
echo "📤 Pushing App image..."
docker push $USERNAME/homeplanner-app:$TAG

echo "📤 Pushing Nginx image..."
docker push $USERNAME/homeplanner-nginx:$TAG

echo "✅ Successfully published to Docker Hub!"
echo "You can now deploy using: docker compose up -d"
