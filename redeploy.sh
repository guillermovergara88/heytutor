#!/bin/bash

./check_docker.sh &

CHECK_DOCKER_PID=$!

wait $CHECK_DOCKER_PID

docker-compose down --rmi all
docker-compose down -v --remove-orphans

./start.sh