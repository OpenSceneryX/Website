#!/bin/bash

docker rm osx-app; docker rmi $(docker image ls | grep osx/app-dev | awk '{ print $3 }')
