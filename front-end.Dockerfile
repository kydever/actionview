FROM node:latest as builder

WORKDIR /usr/src/build

RUN npm install -g cnpm --registry=https://registry.npm.taobao.org && cnpm install

COPY front-end /usr/src/build
COPY front-end.conf /usr/src/build/app.conf
RUN npm install && npm run build

FROM nginx:alpine

COPY --from=builder /usr/src/build/dist /usr/src/app/dist
COPY --from=builder /usr/src/build/app.conf /etc/nginx/conf.d/

ENTRYPOINT ["nginx", "-g", "daemon off;"]
