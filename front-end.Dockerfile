FROM node:latest as builder

WORKDIR /usr/src/build

COPY front-end /usr/src/build
RUN npm install && npm run build
COPY front-end.conf /usr/src/build/app.conf
COPY storage/front-end /usr/src/build/front-end

FROM nginx:alpine

COPY --from=builder /usr/src/build/dist /usr/src/app/actionview/assets
COPY --from=builder /usr/src/build/front-end/index.html /usr/src/app/index.html
COPY --from=builder /usr/src/build/front-end/scripts /usr/src/app/actionview/scripts
COPY --from=builder /usr/src/build/dist/common.js /usr/src/app/actionview/scripts/common.js
COPY --from=builder /usr/src/build/dist/app*.js /usr/src/app/actionview/scripts/app.js
COPY --from=builder /usr/src/build/front-end/images /usr/src/app/actionview/images
COPY --from=builder /usr/src/build/app.conf /etc/nginx/conf.d/

ENTRYPOINT ["nginx", "-g", "daemon off;"]
