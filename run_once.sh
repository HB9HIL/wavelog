sudo apt update
sudo apt install -y libatk1.0-0 libatk-bridge2.0-0 libgtk-3-0 libx11-xcb1 libxcb-dri3-0 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libasound2 libpangocairo-1.0-0 libcups2 libnss3
docker compose -f cypress_testserver.yml up -d
npx cypress run
docker compose -f cypress_testserver.yml down
docker volume rm cypress_db_data
docker volume rm cypress_wavelog_data
docker volume rm root_wavelog-config
docker volume rm root_wavelog-dbdata
docker volume rm root_wavelog-uploads
docker volume rm root_wavelog-userdata
docker rmi cypress_testing-web