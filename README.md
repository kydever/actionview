# 介绍

本项目以 [actionview](https://github.com/lxerxa/actionview.git) 为蓝本，使用 `Hyperf` 框架进行重写。

> 本项目为 Hyperf 框架的 DEMO 项目

# 如何贡献代码

## 初始化子模块

```shell
git submodule sync --recursive
git submodule update --init --recursive
```

## 运行前端代码

> 如果不是 OSX 系统，则需要自行修改 front-end.conf 中的 api 代理

```shell
docker build . -t actionview -f front-end.Dockerfile
```
