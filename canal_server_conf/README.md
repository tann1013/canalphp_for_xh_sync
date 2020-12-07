## 说明

### **此目录是管理canal配置文件**

#### **local**

本地环境，主要用于本地测试

#### **dev**

开发环境，用于内网71服务器开发测试，此dev环境目前并非是dev，而是指的是开发环境，包括dev,test,alpha,beta，但实际测试用的是alpha

订阅信息：

address：192.168.1.71

port：11111

destination：bankcement_dev


docker部署

`docker run -d --restart=always --name canal-server -p 11111:11111 -v /data/wwwroot/bankcement.com/canal-server/conf/:/home/admin/canal-server/conf/ -v /data/wwwroot/bankcement.com/canal-server/logs/:/home/admin/canal-server/logs/ canal/canal-server:v1.1.4`

#### **online**

正式环境，用于正式环境

