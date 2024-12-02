docker compose -f cypress_testserver.yml up -d
npx cypress run
docker compose -f cypress_testserver.yml down
docker volume rm cypress-tests_db_data
docker volume rm cypress-tests_wavelog_data
docker rmi cypress-tests-web
