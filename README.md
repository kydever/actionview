# 介绍

本项目以 [actionview](https://github.com/lxerxa/actionview.git) 为蓝本，使用 `Hyperf` 框架进行重写。

> 本项目为 Hyperf 框架的 DEMO 项目

## 不同于原项目的部分

- 原项目使用 Laravel 框架，本项目使用 Hyperf 框架
- 原项目使用的是 Mongo，本项目使用的是 MySQL

# 如何贡献代码

## 初始化子模块

```shell
git submodule sync --recursive
git submodule update --init --recursive
```

### 修改 react 版本

> 暂时前端代码存在版本冲突，所以修改 react-dom 和 react 版本到 15.5.4

## 运行代码

### 创建网络

```shell
docker network create default-network
```
### 运行前端代码

> 如果想让前端代码访问宿主机，OSX 系统将 front-end.conf 中的接口地址改成 host.docker.internal，其他系统按实际情况配置

```shell
docker build . -t actionview_front_end -f front-end.Dockerfile
docker run -p 8081:8080 --restart always --network default-network --name actionview_front_end -d actionview_front_end:latest
```

## 运行后端脚本

自己准备好对应的 .env.actionview 配置

```shell
docker build . -t actionview
docker run --name actionview --restart always --network default-network -v .env.actionview:/opt/www/.env -d actionview:latest
```
