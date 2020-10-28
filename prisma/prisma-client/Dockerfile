FROM node:10.20.1-alpine3.9

WORKDIR /app

COPY package*.json ./

# To handle 'not get uid/gid' error in alpine linux set unsafe-perm true
RUN apk update && apk upgrade \
    && npm config set unsafe-perm true \
    && yarn install

COPY . .

EXPOSE 4000

CMD ["yarn", "start"]
