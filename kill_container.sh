#!/bin/bash

docker compose -f cypress_testserver.yml down
docker volume rm wavelog_db_data
docker volume rm wavelog_wavelog_data
docker rmi wavelog-web