#!/bin/bash

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Attempting to install Docker..."
    
    # Install Docker using apt-get
    sudo apt-get update
    sudo apt-get install -y docker.io
    
    # Start Docker
    if sudo service --status-all | grep -Fq "docker"; then
        sudo service docker start
    elif sudo systemctl --quiet is-active docker; then
        sudo systemctl start docker
    else
        echo "Docker installation failed. Please install and start Docker manually."
        exit 1
    fi
fi

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo "Docker is not running. Starting Docker..."
    
    if sudo service --status-all | grep -Fq "docker"; then
        sudo service docker start
    elif sudo systemctl --quiet is-active docker; then
        sudo systemctl start docker
    else
        echo "Failed to start Docker. Please start Docker manually."
        exit 1
    fi
fi
