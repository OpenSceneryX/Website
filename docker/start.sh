#!/bin/bash

MSYS_NO_PATHCONV=1 UID_GID="$(id -u):$(id -g)" docker compose up
