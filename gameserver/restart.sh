#!/bin/bash
docker-compose -f docker-compose.develop.yml down --remove-orphans
docker-compose -f docker-compose.develop.yml up -d
