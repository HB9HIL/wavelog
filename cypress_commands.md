# Cypress Commands

## start container

```bash
docker compose -f cypress_testserver.yml up -d
```

## open webUI (after start container)

```bash
npx cypress open
```

## kill container

```bash
docker compose -f cypress_testserver.yml down
docker volume rm cypress_testing_db_data
docker volume rm cypress_testing_wavelog_data
docker rmi cypress_testing-web
```

## reset (and run CLI)

```bash
docker compose -f cypress_testserver.yml down
docker volume rm cypress_testing_db_data
docker volume rm cypress_testing_wavelog_data
docker rmi cypress_testing-web
docker compose -f cypress_testserver.yml up -d
npx cypress run
```

## run test once (CLI)

```bash
docker compose -f cypress_testserver.yml up -d
npx cypress run
docker compose -f cypress_testserver.yml down
docker volume rm cypress_testing_db_data
docker volume rm cypress_testing_wavelog_data
docker rmi cypress_testing-web
```