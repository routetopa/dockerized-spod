#!/bin/bash
# git clone -c core.autocrlf=false https://github.com/ckan/ckan.git


(
    cd ./ckan
    git clone -c core.autocrlf=false https://github.com/keitaroinc/docker-ckan ckan
    # move ckan content into current directory
    mv ./ckan/compose/2.9/postgresql ./
    mv ./ckan/compose/2.9/solr8 ./
    rm -rf ./ckan
)


cp ./ckan/.ckan-env.example ./ckan/.ckan-env
cp ./.env.example ./.env

source ./.env
cp ./vhost.d/host-url ./vhost.d/$HOST_URL

docker-compose up -d --build
docker restart oauth > /dev/null

echo "-----"
docker exec etherpad bash -c "sed -i 's/0.0.0.0/$HOST_URL/g' settings.json"
docker exec oauth bash -c "sh init.sh $EMAIL $PASSWORD $HOST_URL"

# echo "Oxwall is ready"


