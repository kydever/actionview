# 介绍

[![PHPUnit for Hyperf](https://github.com/kydever/actionview/actions/workflows/test.yml/badge.svg)](https://github.com/kydever/actionview/actions/workflows/test.yml)

本项目以 [actionview](https://github.com/lxerxa/actionview.git) 为蓝本，使用 `Hyperf` 框架进行重写。

> 本项目为 Hyperf 框架的 DEMO 项目

## 不同于原项目的部分

- 原项目使用 Laravel 框架，本项目使用 Hyperf 框架
- 原项目使用的是 Mongo，本项目使用的是 MySQL

# 如何运行代码

## 初始化子模块

```shell
git submodule sync --recursive
git submodule update --init --recursive
```

## 运行代码

### 启动服务

```shell
docker-compose up -d --remove-orphans --build
```

### 初始化数据库

```shell
docker exec $(basename $(pwd))_actionview_1 php /opt/www/bin/hyperf.php migrate
```

### 初始化搜索引擎

```shell
docker exec $(basename $(pwd))_actionview_1 php /opt/www/bin/hyperf.php put:mapping -i issue
```

# 如何开发

## MacOS

### 创建 Network

```shell
docker network create default-network
```

### 安装必要的服务

- ElasticSearch

```shell
docker run -d --network default-network --restart always -p 9200:9200 -p 9300:9300 \
-e "discovery.type=single-node" -v elasticsearch-data:/usr/share/elasticsearch/data \
-e ES_JAVA_OPTS="-Xms512m -Xmx512m" --name elasticsearch elasticsearch:5-alpine
```

- Redis

```shell
docker run --name redis -v redis-data:/data --network default-network --restart always -p 6379:6379 -d redis
```

- MySQL

```shell
docker run --name mysql -v mysql-data:/var/lib/mysql -p 3306:3306 --restart always --network default-network -e MYSQL_ROOT_HOST=% -e MYSQL_DATABASE=actionview -e MYSQL_ALLOW_EMPTY_PASSWORD=true -e TZ=Asia/Shanghai -d mysql/mysql-server:5.7
```

### 启动前段代码

- 修改 `front-end.conf`   

将代理地址改为 `proxy_pass  http://host.docker.internal:9501/;`

- 打包代码

```shell
docker build . -f front-end.Dockerfile -t front-end
```

- 启动服务

```shell
docker run -p 10011:8080 -d --name front-end --rm front-end
```

### 启动后端服务

- 复制环境变量

```shell
cp .env.example .env
```

- 修改配置

```dotenv
APP_NAME=actionview

# Mysql
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=actionview
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=

# Redis
REDIS_HOST=127.0.0.1
REDIS_AUTH=(null)
REDIS_PORT=6379
REDIS_DB=0

# FileSystem
FILESYSTEM_DEFAULT_STORAGE="local"
FILESYSTEM_DEFAULT_DOMAIN=""

# ElasticSearch
ELASTIC_SEARCH_HOST="127.0.0.1:9200"
```

- 初始化数据库

```shell
php bin/hyperf.php migrate
```

- 初始化搜索引擎

```shell
php bin/hyperf.php put:mapping -i issue
```
