# 博学谷

### 注意事项

1.启用rewrite

```bash
#去掉分号
#LoadModule rewrite_module modules/mod_rewrite.so
```

2.启用.htaccess

在虚拟主机配置项中

```bash
AllowOverride None

#修改为
AllowOverride All
```

3.linux 或 mac 环境下

```bash
mkdir runtime && chmod 777 runtime
```

4、配置根目录

将网站根目录设置到`api/public`，默认索引设置为index.php

```bash
# 根目录
DocumentRoot "yourpath/api/public"
```

5、默认索引

```bash
<IfModule dir_module>
    DirectoryIndex index.php index.html
</IfModule>
```

6、数据库支持

修改php配置文件`php.ini`

```bash
#去掉分号
;extension=php_mysql.dll
;extension=php_pdo_mysql.dll
```

7、开启宽字符集支持

```bash
#去掉分号
;extension=php_mbstring.dll
```



