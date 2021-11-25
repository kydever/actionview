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

## 运行前端代码

> 如果不是 OSX 系统，则需要自行修改 front-end.conf 中的 api 代理

```shell
docker build . -t actionview -f front-end.Dockerfile
docker run -p 8080:8080 actionview:latest
```
