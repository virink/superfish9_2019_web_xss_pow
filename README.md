# superfish9 2019 XSS POW

## 题目详情

- **XSS**

## 考点

- XSS
- CSP

## 启动

    docker-compose up -d
    open http://127.0.0.1:8302/

## 说明

由于 **bot** 和 **web** 同处于 **docker** 内网，因此 **bot** 无法通过`127.0.0.1`等本地地址访问 **web**。
如果需要提交 **web** 地址，请替换为 **docker** 内网地址

Eg: `http://127.0.0.1:8302/index.php` -> `http://10.11.77.66/index.php`
即，`127.0.0.1:8302` -> `10.11.77.66`

线上部署请自行解决

## 版权

感谢出题人[@Sfish](https://github.com/superfish9)提供源码及授权使用