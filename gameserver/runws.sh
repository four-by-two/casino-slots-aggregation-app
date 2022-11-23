#!/usr/bin/env bash

set -e

function cleanup() {
popd
}

trap cleanup EXIT

pushd "$( dirname "${BASH_SOURCE[0]}" )"

echo "Starting Websocket server" >&2
( php ws_server.php ) & # runs on :8000
ws=$!


wait