# Pla de Mar
Web application for students at primary school.

## System requirements
The application is made with Symfony 5, so it requires a host with php 7.2, and Apache 2 or Nginx as web server.

I've prepared a Docker container with system requirements. You can build and run the container using docker-compose:

    docker-compose up -d
    
Use the `--build` option if you make changes in the Dockerfile after the first build.

## Symfony 5
The '/app' folder contains the main application written in Symfony.
