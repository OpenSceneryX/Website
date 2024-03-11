#!/bin/bash

cd "$(dirname "$0")"
cd ..
docker build -f docker/Dockerfile.dev -t osx/app-dev
