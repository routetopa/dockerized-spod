FROM node:8.9.0

WORKDIR /home/node/etherpad-lite

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y nano curl abiword
    
ADD 1.7.0 ./

RUN ./bin/installDeps.sh
RUN npm install ep_page_view@0.5.24 ep_comments_page@0.0.35 ep_document_import_hook@0.1.2 ep_font_family@0.2.7 ep_font_size@0.1.11 ep_mammoth_custom@1.2.8

COPY settings.json .
COPY APIKEY.txt .
COPY pad.css src/static/css/pad.css
COPY timeslider.css src/static/css/timeslider.css

RUN chown -R node:node /home/node/etherpad-lite
USER node
ENV NODE_ENV=production
EXPOSE 9001
CMD ["node", "node_modules/ep_etherpad-lite/node/server.js"]