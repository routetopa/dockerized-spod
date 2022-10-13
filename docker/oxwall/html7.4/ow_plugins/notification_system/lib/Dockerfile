FROM node:lts-slim

WORKDIR /home/node

RUN apt-get update \
    && apt-get upgrade -y
    
COPY index.js ./
COPY config.js ./
COPY package.json ./

RUN npm install

RUN chown -R node:node /home/node
USER node
ENV NODE_ENV=production
EXPOSE 3000
CMD ["node", "index.js"]