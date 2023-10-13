#!/bin/bash

cd "$(dirname "$0")"
docker build --no-cache -f Dockerfile.base -t osx/app-base .
