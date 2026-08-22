
<p align="center">
<img src="https://postimg.aliavv.com/newmbp/jluin.png" alt="WeChat SubScribe">
</p>

<h1 align="center"><a href="https://github.com/WPStoreApp/wx-subscribe" target="_blank">微信支付订阅</a></h1>

> 每个梦想，都值得灌溉


[![PHP from Travis config](https://img.shields.io/travis/php-v/symfony/symfony.svg)](https://github.com/WPStoreApp/wx-subscribe)
[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/akismet.svg)](https://wordpress.org/plugins/wx-subscribe/)
[![WordPress plugin rating](https://img.shields.io/wordpress/plugin/r/akismet.svg)](https://wordpress.org/plugins/wx-subscribe/)

## 特性

- **个人可用**
- 年付订阅机制
- 微信支付
- 全文隐藏或部分内容隐藏轻松控制

## 安装

### 通过 Github 安装

1. 点击顶部的「Clone and Download」，选择其中的 「Download Zip」
2. 下载完成后，登陆到 WordPress 后台，进入 WordPress 后台中的「插件」——「安装插件」界面
3. 在「安装插件」界面上传插件，选择刚刚下载好的 Zip 压缩文件
4. 启用插件后，在后台的「微信订阅」——「插件配置」中配置 Payjs 的各项参数，配置完成后保存，即可进行订阅


### 通过 WordPress 后台安装

1. 进入 WordPress 后台的「添加插件页面」
2. 在搜索框内搜索关键词「微信支付付费订阅」
3. 点击搜索出来的插件，安装并启用
4. 配置插件

![](https://postimg.aliavv.com/newmbp/ho715.png)


## 配置

1. 访问 [payjs.cn](https://payjs.cn/ref/MDNXMD)，访问管理后台。
2. 在后台的「会员中心」中可以看到商户号和接口通信密钥，复制商户号和接口通信密钥
3. 将商户号和接口通信密钥复制到 WordPress 后台中的对应配置项目中去，并保存

![](https://postimg.aliavv.com/newmbp/kxxo1.png)

## 使用
### 部分隐藏模式

1. 先写好所有内容
2. 选中你要隐藏的内容
3. 点击编辑栏中「文本」下面的「付费阅读」，会自动在你的内容两侧加入 `[subscribe]` 标签
4. 保存文章并发布即可（不勾选「本文订阅后可读」）

![](https://postimg.aliavv.com/newmbp/yiyag.png)

### 全文隐藏模式

1. 写好全部内容
2. 勾选「发布」框内的「本文订阅后可读」
3. 发布文章

![](https://postimg.aliavv.com/newmbp/5k2aj.png)

## 注意事项

1. 请确保你填写了「商户号」和「接口通信密钥」
2. 设置正确的时区

## 版本变化
#### 1.0.0 (April 29,2018)

- 项目初始化


## 开源协议

本项目基于 GPL v2 协议开源


