FROM node:18.9.0

WORKDIR /home/node

RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y nano curl mariadb-client

RUN curl https://raw.githubusercontent.com/ethersheet-collective/EtherSheet/master/dev_install.sh | bash

WORKDIR /home/node/ethersheet/EtherSheet
RUN npm link /home/node/ethersheet/es_client
RUN npm link /home/node/ethersheet/es_command
RUN npm link /home/node/ethersheet/transactor
RUN npm install \
    && npm install csv@0.2.0 csv-sniffer express@4 body-parser cookie-parser cookie-session formidable compression cluster http-proxy url adm-zip

COPY config.js /home/node/ethersheet/EtherSheet
COPY app.js /home/node/ethersheet/EtherSheet
ADD lib /home/node/ethersheet/EtherSheet/lib/

ADD es_client /home/node/ethersheet/EtherSheet/node_modules/es_client/
RUN mkdir /home/node/ethersheet/EtherSheet/lib/uploads

ENV NODE_ENV=production

CMD [ "node", "app.js" ]