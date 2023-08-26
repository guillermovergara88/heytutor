#!/bin/bash

./check_docker.sh &

CHECK_DOCKER_PID=$!

wait $CHECK_DOCKER_PID

# In case of scaling the app to 2 instances
# docker-compose up -d --build --scale app=2

docker-compose build --no-cache
docker-compose up -d