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

## 运行代码

### 启动服务

```shell
docker-compose up -d --remove-orphans --build
```

### 初始化数据库

### 初始化搜索引擎


