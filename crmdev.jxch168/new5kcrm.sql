-- MySQL dump 10.13  Distrib 5.6.25, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: 5kcrm
-- ------------------------------------------------------
-- Server version	5.6.25-0ubuntu0.15.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `new5kcrm_action_log`
--

DROP TABLE IF EXISTS `new5kcrm_action_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_action_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `action_name` varchar(100) NOT NULL,
  `param_name` varchar(100) DEFAULT NULL,
  `action_id` int(10) NOT NULL,
  `content` varchar(500) NOT NULL,
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='操作日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_action_log`
--

LOCK TABLES `new5kcrm_action_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_action_log` DISABLE KEYS */;
INSERT INTO `new5kcrm_action_log` VALUES (1,1,'leads','add',NULL,1,'管理员admin在2015-09-15 18:06:46添加了id为1的线索。',1442311606),(2,1,'customer','add',NULL,1,'管理员admin在2015-09-15 18:08:19添加了id为1的客户。',1442311699),(3,1,'user','add',NULL,2,'管理员admin在2015-09-16 15:43:36添加了id为2的员工。',1442389416),(4,1,'product','add',NULL,1,'管理员admin在2015-10-08 12:45:51添加了id为1的产品。',1444279551),(5,1,'product','add',NULL,2,'管理员admin在2015-10-08 12:49:31添加了id为2的产品。',1444279771),(6,1,'product','add',NULL,3,'管理员admin在2015-10-08 12:50:19添加了id为3的产品。',1444279819),(7,1,'product','add',NULL,4,'管理员admin在2015-10-08 12:50:57添加了id为4的产品。',1444279857),(8,1,'product','add',NULL,5,'管理员admin在2015-10-08 12:51:37添加了id为5的产品。',1444279897),(9,1,'product','add',NULL,6,'管理员admin在2015-10-08 12:52:09添加了id为6的产品。',1444279929),(10,1,'customer','edit',NULL,1,'管理员admin在2015-10-08 12:56:07修改了id为1的客户。',1444280167),(11,1,'business','add',NULL,1,'管理员admin在2015-10-08 12:59:12添加了id为1的商机。',1444280352),(12,1,'finance','add','t=receivables',1,'管理员admin在2015-10-08 13:03:26添加了id为1的财务。',1444280606),(13,1,'finance','add','t=payables',1,'管理员admin在2015-10-08 13:07:45添加了id为1的财务。',1444280865),(14,1,'customer','add',NULL,2,'管理员admin在2015-10-14 09:45:39添加了id为2的客户。',1444787139),(15,1,'customer','edit',NULL,2,'管理员admin在2015-10-14 09:47:57修改了id为2的客户。',1444787277),(16,1,'product','edit',NULL,1,'管理员admin在2015-10-16 10:11:57修改了id为1的产品。',1444961517),(17,1,'product','edit',NULL,3,'管理员admin在2015-10-16 10:12:10修改了id为3的产品。',1444961530),(18,1,'product','edit',NULL,4,'管理员admin在2015-10-16 10:12:22修改了id为4的产品。',1444961542),(19,1,'product','edit',NULL,5,'管理员admin在2015-10-16 10:13:49修改了id为5的产品。',1444961629),(20,1,'product','edit',NULL,6,'管理员admin在2015-10-16 10:13:58修改了id为6的产品。',1444961638);
/*!40000 ALTER TABLE `new5kcrm_action_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_announcement`
--

DROP TABLE IF EXISTS `new5kcrm_announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_announcement` (
  `announcement_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `order_id` int(10) NOT NULL COMMENT '排序',
  `role_id` int(10) NOT NULL COMMENT '发表人岗位',
  `title` varchar(200) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `create_time` int(10) NOT NULL COMMENT '发表时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `color` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL COMMENT '通知部门id',
  `status` int(1) NOT NULL COMMENT '是否发布1发布2停用',
  `isshow` int(1) NOT NULL DEFAULT '0' COMMENT '是否公开1是0否',
  PRIMARY KEY (`announcement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='存放知识文章信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_announcement`
--

LOCK TABLES `new5kcrm_announcement` WRITE;
/*!40000 ALTER TABLE `new5kcrm_announcement` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_announcement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_business`
--

DROP TABLE IF EXISTS `new5kcrm_business`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_business` (
  `business_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '商机id',
  `name` varchar(255) NOT NULL DEFAULT '',
  `origin` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(200) NOT NULL,
  `estimate_price` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(10) NOT NULL COMMENT '客户id',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者岗位',
  `owner_role_id` int(10) NOT NULL COMMENT '所有者岗位',
  `gain_rate` int(3) NOT NULL COMMENT '赢单率(百分比)',
  `total_amount` int(10) NOT NULL COMMENT '产品总数',
  `subtotal_val` float(9,2) NOT NULL COMMENT '小计和',
  `discount_price` float(9,2) NOT NULL COMMENT '其他费用',
  `sales_price` float(9,2) NOT NULL COMMENT '成交价',
  `due_date` int(10) NOT NULL COMMENT '预计成交日期',
  `create_time` int(10) NOT NULL COMMENT '商机创建时间',
  `update_time` int(10) NOT NULL COMMENT '修改时间',
  `update_role_id` int(10) NOT NULL,
  `status_id` int(10) NOT NULL COMMENT '商机状态id',
  `total_price` float(10,2) NOT NULL COMMENT '商机金额',
  `nextstep` varchar(100) NOT NULL COMMENT '下一步',
  `nextstep_time` int(10) NOT NULL,
  `is_deleted` int(1) NOT NULL COMMENT '是否删除',
  `delete_role_id` int(10) NOT NULL,
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  `contacts_id` int(10) NOT NULL COMMENT '商机联系人',
  `contract_address` varchar(500) NOT NULL,
  PRIMARY KEY (`business_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='本表存放商机相关信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_business`
--

LOCK TABLES `new5kcrm_business` WRITE;
/*!40000 ALTER TABLE `new5kcrm_business` DISABLE KEYS */;
INSERT INTO `new5kcrm_business` VALUES (1,'可能会购买月满盈','','',100000,1,1,1,60,1,0.00,0.00,0.00,0,1444280352,1444280352,1,3,10.00,'',0,0,0,0,1,'上海市\n市辖区\n黄浦区\n');
/*!40000 ALTER TABLE `new5kcrm_business` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_business_data`
--

DROP TABLE IF EXISTS `new5kcrm_business_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_business_data` (
  `business_id` int(10) NOT NULL COMMENT '主键',
  `description` text NOT NULL COMMENT '备注',
  PRIMARY KEY (`business_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商机数据表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_business_data`
--

LOCK TABLES `new5kcrm_business_data` WRITE;
/*!40000 ALTER TABLE `new5kcrm_business_data` DISABLE KEYS */;
INSERT INTO `new5kcrm_business_data` VALUES (1,'');
/*!40000 ALTER TABLE `new5kcrm_business_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_business_status`
--

DROP TABLE IF EXISTS `new5kcrm_business_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_business_status` (
  `status_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '商机状态',
  `name` varchar(20) DEFAULT NULL COMMENT '商机状态名',
  `order_id` int(10) DEFAULT NULL COMMENT '顺序号',
  `is_end` int(1) NOT NULL,
  `description` varchar(200) DEFAULT NULL COMMENT '商机状态描述',
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_2` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COMMENT='本表存放商机状态信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_business_status`
--

LOCK TABLES `new5kcrm_business_status` WRITE;
/*!40000 ALTER TABLE `new5kcrm_business_status` DISABLE KEYS */;
INSERT INTO `new5kcrm_business_status` VALUES (1,'深度沟通',3,0,'已经约见，并且报价'),(2,'初步沟通',2,0,'已进行初步沟通网站建设事宜，可能约见'),(3,'意向客户',1,0,'通过沟通近期有做网站的需求'),(5,'签订合同',5,0,'签订合同'),(6,'设计制作',6,0,'制作中'),(7,'制作完成',7,0,'制作完成待收款'),(99,'项目失败',99,1,'项目失败'),(100,'项目成功',100,1,'项目成功');
/*!40000 ALTER TABLE `new5kcrm_business_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_business_status_flow`
--

DROP TABLE IF EXISTS `new5kcrm_business_status_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_business_status_flow` (
  `flow_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '状态流id',
  `name` varchar(50) NOT NULL COMMENT '状态流名字',
  `data` text NOT NULL COMMENT '状态流数据',
  `in_use` int(1) NOT NULL COMMENT '是否在用',
  `description` varchar(200) NOT NULL COMMENT '状态流描述',
  PRIMARY KEY (`flow_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_business_status_flow`
--

LOCK TABLES `new5kcrm_business_status_flow` WRITE;
/*!40000 ALTER TABLE `new5kcrm_business_status_flow` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_business_status_flow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_comment`
--

DROP TABLE IF EXISTS `new5kcrm_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_comment` (
  `comment_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `content` varchar(1000) NOT NULL COMMENT '评论内容',
  `creator_role_id` int(10) NOT NULL COMMENT '评论人',
  `to_role_id` int(10) NOT NULL COMMENT '被评论人',
  `module` varchar(50) NOT NULL COMMENT '模块',
  `module_id` int(10) NOT NULL COMMENT '模块id',
  `create_time` int(10) NOT NULL COMMENT '添加时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_comment`
--

LOCK TABLES `new5kcrm_comment` WRITE;
/*!40000 ALTER TABLE `new5kcrm_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_config`
--

DROP TABLE IF EXISTS `new5kcrm_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_config`
--

LOCK TABLES `new5kcrm_config` WRITE;
/*!40000 ALTER TABLE `new5kcrm_config` DISABLE KEYS */;
INSERT INTO `new5kcrm_config` VALUES (1,'defaultinfo','a:8:{s:4:\"name\";s:12:\"华陌通CRM\";s:11:\"description\";s:36:\"华陌通的客户关系管理系统\";s:5:\"state\";s:9:\"上海市\";s:4:\"city\";s:9:\"市辖区\";s:15:\"allow_file_type\";s:40:\"pdf,doc,jpg,png,gif,txt,doc,xls,zip,docx\";s:19:\"contract_alert_time\";i:30;s:10:\"task_model\";s:1:\"2\";s:4:\"logo\";N;}',''),(2,'customer_outdays','30','客户设置放入客户吃天数'),(3,'customer_limit_condition','day','客户池领取条件限制 day：今日 week： 本周 month：本月'),(4,'customer_limit_counts','10','客户池领取次数限制'),(5,'leads_outdays','30','线索超出天数放入客户池'),(6,'contract_custom','','');
/*!40000 ALTER TABLE `new5kcrm_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_contacts`
--

DROP TABLE IF EXISTS `new5kcrm_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_contacts` (
  `contacts_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '联系人id',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者岗位id',
  `name` varchar(50) NOT NULL COMMENT '联系人姓名',
  `post` varchar(20) NOT NULL COMMENT '客户联系人岗位',
  `department` varchar(20) NOT NULL COMMENT '客户联系人部门',
  `sex` int(1) NOT NULL COMMENT '联系人性别',
  `saltname` varchar(20) NOT NULL COMMENT '称呼',
  `telephone` varchar(20) NOT NULL COMMENT '联系人电话',
  `email` varchar(50) NOT NULL COMMENT '联系人邮箱',
  `qq` varchar(20) NOT NULL COMMENT 'qq',
  `address` varchar(50) NOT NULL COMMENT '联系地址',
  `zip_code` varchar(20) NOT NULL COMMENT '邮编',
  `description` varchar(100) NOT NULL COMMENT '联系人信息备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '信息更新时间',
  `is_deleted` int(1) NOT NULL COMMENT '是否被删除',
  `delete_role_id` int(10) NOT NULL,
  `delete_time` int(10) NOT NULL,
  PRIMARY KEY (`contacts_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='本表存放客户联系人对应关系信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_contacts`
--

LOCK TABLES `new5kcrm_contacts` WRITE;
/*!40000 ALTER TABLE `new5kcrm_contacts` DISABLE KEYS */;
INSERT INTO `new5kcrm_contacts` VALUES (1,1,'李明明','老板','',0,'先生','13800138000','','','','','',1442311699,1442311699,0,0,0);
/*!40000 ALTER TABLE `new5kcrm_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_contract`
--

DROP TABLE IF EXISTS `new5kcrm_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_contract` (
  `contract_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `number` varchar(50) NOT NULL COMMENT '编号',
  `business_id` int(10) NOT NULL COMMENT '商机',
  `price` decimal(10,2) NOT NULL COMMENT '总价',
  `due_time` int(10) NOT NULL COMMENT '签约日期',
  `owner_role_id` int(10) NOT NULL COMMENT '负责人',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者',
  `content` text NOT NULL COMMENT '合同内容',
  `description` varchar(500) NOT NULL COMMENT '描述',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `start_date` int(10) NOT NULL COMMENT '生效时间',
  `end_date` int(10) NOT NULL COMMENT '到期时间',
  `status` varchar(20) NOT NULL COMMENT '合同状态',
  `is_deleted` int(1) NOT NULL COMMENT '是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`contract_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='合同表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_contract`
--

LOCK TABLES `new5kcrm_contract` WRITE;
/*!40000 ALTER TABLE `new5kcrm_contract` DISABLE KEYS */;
INSERT INTO `new5kcrm_contract` VALUES (1,'5k_crm201510084831',1,100000.00,1444280447,1,1,'<div class=\"note\">\r\n	月满盈 预期年化收益率6.5%，出借期限1个月。例：投资20万元到期收益为1083元。\r\n</div>\r\n<div id=\"xunlei_com_thunder_helper_plugin_d462f475-c18e-46be-bd10-327458d045bd\">\r\n</div>','月满盈 预期年化收益率6.5%，出借期限1个月。例：投资20万元到期收益为1083元。',1444280486,1444280486,1444320000,1446998400,'已创建',0,0,0);
/*!40000 ALTER TABLE `new5kcrm_contract` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_control`
--

DROP TABLE IF EXISTS `new5kcrm_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_control` (
  `control_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '操作id',
  `module_id` int(10) NOT NULL COMMENT '模块id',
  `name` varchar(20) NOT NULL COMMENT '操作名',
  `m` varchar(20) NOT NULL COMMENT '对应Action',
  `a` varchar(20) NOT NULL COMMENT '行为',
  `parameter` varchar(50) NOT NULL COMMENT '参数',
  `description` varchar(200) NOT NULL COMMENT '操作描述',
  PRIMARY KEY (`control_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COMMENT='本表存放操作信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_control`
--

LOCK TABLES `new5kcrm_control` WRITE;
/*!40000 ALTER TABLE `new5kcrm_control` DISABLE KEYS */;
INSERT INTO `new5kcrm_control` VALUES (1,1,'crm面板操作','index','index','','CRM系统面板'),(2,7,'修改个人信息','User','edit','','是的法士大夫地方'),(4,7,'添加用户','User','add','',''),(78,7,'删除员工','User','delete','',''),(6,7,'添加部门','User','department_add','',''),(7,7,'修改部门','User','department_edit','',''),(8,7,'删除部门','User','department_delete','',''),(9,7,'添加岗位','User','role_add','',''),(10,7,'修改岗位','User','role_edit','',''),(11,7,'删除岗位','User','role_delete','',''),(12,2,'添加商机','Business','add','',''),(34,2,'完整商机信息','Business','view','',''),(13,2,'修改商机','Business','edit','',''),(14,2,'删除商机','Business','delete','',''),(15,2,'添加商机日志','Business','addLogging','',''),(16,2,'修改商机日志','Business','eidtLogging','',''),(17,2,'删除商机日志','Business','deleteLogging','',''),(18,1,'用户登录','User','login','',''),(19,1,'用户注册','User','register','',''),(20,1,'退出','User','logout','',''),(21,7,'查看部门信息','User','department','',''),(22,1,'找回密码','User','lostPW','',''),(23,1,'重置密码','User','lostpw_reset','',''),(24,7,'查看员工信息','User','index','',''),(25,7,'查看岗位信息','User','role','',''),(26,7,'岗位分配','User','user_role_relation','',''),(27,7,'员工资料修改','User','editUsers','',''),(28,1,'查看我的日志','User','mylog','',''),(60,6,'岗位授权','Permission','authorize','',''),(30,7,'个人日志详情','User','mylog_view','',''),(31,7,'删除个人日志','User','mylog_delete','',''),(32,2,'查看商机信息','Business','index','',''),(33,2,'查看商机日志','Business','logging','',''),(35,3,'产品列表','product','index','',''),(36,3,'添加产品','Product','add','',''),(37,3,'修改产品信息','product','edit','',''),(38,3,'删除产品','Product','delete','',''),(39,3,'查看产品分类信息','Product','category','',''),(40,3,'添加产品分类','Product','category_add','',''),(41,3,'删除产品分类','Product','deleteCategory','',''),(42,3,'修改产品分类','Product','editcategory','',''),(43,3,'产品销量统计','Product','count','',''),(44,5,'查看客户信息','Customer','customerView','',''),(45,5,'添加客户','Customer','add','',''),(46,5,'修改客户信息','Customer','edit','',''),(47,5,'删除客户','Customer','delete','',''),(48,5,'添加客户联系人','Contacts','add','',''),(49,5,'查看客户联系人','Contacts','view','',''),(50,5,'删除客户联系人','Contacts','delete','',''),(51,5,'修改客户联系人','Contacts','edit','',''),(52,6,'查看操作模块','Permission','module','',''),(53,6,'修改操作模块','Permission','module_edit','',''),(54,6,'添加操作模块信息','Permission','module_add','',''),(55,6,'删除操作模块','Permission','module_delete','',''),(56,6,'查看操作信息','Permission','index','',''),(57,6,'修改操作','Permission','control_edit','',''),(58,6,'删除模块','Permission','control_delete','',''),(59,6,'添加操作','Permission','control_add','',''),(61,9,'smtp设置','Config','smtpConfig','',''),(62,9,'删除状态','Config','deleteStatus','',''),(63,9,'修改状态','Config','editStatus','',''),(64,9,'添加状态','Config','addStatus','',''),(65,9,'查看状态','Config','statusList','',''),(66,9,'查看状态流','Config','flowList','',''),(67,9,'添加状态流','Config','addStatusflow','',''),(68,9,'删除状态流','Config','deleteStatusFlow','',''),(69,9,'修改状态流信息','Config','editStatusFlow','','');
/*!40000 ALTER TABLE `new5kcrm_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_control_module`
--

DROP TABLE IF EXISTS `new5kcrm_control_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_control_module` (
  `module_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '操作模块id',
  `name` varchar(20) NOT NULL COMMENT '操作模块名',
  `description` varchar(50) NOT NULL COMMENT '操作模块描述',
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='存放操作模块信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_control_module`
--

LOCK TABLES `new5kcrm_control_module` WRITE;
/*!40000 ALTER TABLE `new5kcrm_control_module` DISABLE KEYS */;
INSERT INTO `new5kcrm_control_module` VALUES (2,'商机模块','关于一切商机操作的模块'),(3,'产品模块','关于产品操作的模块'),(5,'客户模块','客户的管理'),(6,'权限模块','用户的权限管理'),(7,'员工管理模块','是的范德萨发的说法'),(9,'系统设置','');
/*!40000 ALTER TABLE `new5kcrm_control_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer`
--

DROP TABLE IF EXISTS `new5kcrm_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer` (
  `customer_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '客户id',
  `owner_role_id` int(10) NOT NULL COMMENT '所有者岗位',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `contacts_id` int(10) NOT NULL DEFAULT '0' COMMENT '首要联系人',
  `name` varchar(333) NOT NULL DEFAULT '',
  `origin` varchar(150) NOT NULL DEFAULT '',
  `address` varchar(100) NOT NULL COMMENT '客户联系地址',
  `zip_code` varchar(20) NOT NULL COMMENT '邮编',
  `industry` varchar(150) NOT NULL DEFAULT '',
  `annual_revenue` varchar(20) NOT NULL COMMENT '年营业额',
  `ownership` varchar(150) NOT NULL DEFAULT '',
  `rating` varchar(150) NOT NULL DEFAULT '',
  `create_time` int(10) NOT NULL COMMENT '建立时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `is_deleted` int(1) NOT NULL COMMENT '是否删除',
  `is_locked` int(1) NOT NULL COMMENT '是否锁定',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='本表存放客户的相关信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer`
--

LOCK TABLES `new5kcrm_customer` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer` DISABLE KEYS */;
INSERT INTO `new5kcrm_customer` VALUES (1,1,1,1,'李明明','电话营销','上海市\n市辖区\n黄浦区\n','','','','','',1442311699,1444280252,0,0,0,0),(2,0,1,0,'test','网络营销','\n\n\n','','','','','',1444787139,1444804572,0,0,0,0);
/*!40000 ALTER TABLE `new5kcrm_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_attribute`
--

DROP TABLE IF EXISTS `new5kcrm_customer_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_attribute` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '分组信息主键id',
  `group_id` int(10) NOT NULL COMMENT '客户属性组id',
  `name` int(10) NOT NULL COMMENT '属性组名称',
  `description` varchar(100) NOT NULL COMMENT '属性描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户属性信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_attribute`
--

LOCK TABLES `new5kcrm_customer_attribute` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_customer_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_attribute_group`
--

DROP TABLE IF EXISTS `new5kcrm_customer_attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_attribute_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '客户属性组id',
  `name` varchar(20) NOT NULL COMMENT '属性组名称',
  `description` varchar(100) DEFAULT NULL COMMENT '属性组描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表存放客户属性组信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_attribute_group`
--

LOCK TABLES `new5kcrm_customer_attribute_group` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_attribute_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_customer_attribute_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_attribute_relation`
--

DROP TABLE IF EXISTS `new5kcrm_customer_attribute_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_attribute_relation` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '客户属性关系id',
  `customer_id` int(10) NOT NULL COMMENT '客户id',
  `attribute_id` int(10) NOT NULL COMMENT '客户对应属性id',
  `description` varchar(100) DEFAULT NULL COMMENT '客户属性关系备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表存放客户和属性的关系';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_attribute_relation`
--

LOCK TABLES `new5kcrm_customer_attribute_relation` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_attribute_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_customer_attribute_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_cares`
--

DROP TABLE IF EXISTS `new5kcrm_customer_cares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_cares` (
  `care_id` int(10) NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL,
  `care_time` int(10) NOT NULL,
  `contacts_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `owner_role_id` int(10) NOT NULL,
  `type` varchar(20) NOT NULL,
  `content` varchar(1000) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  `creator_role_id` int(10) NOT NULL,
  PRIMARY KEY (`care_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='客户关怀信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_cares`
--

LOCK TABLES `new5kcrm_customer_cares` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_cares` DISABLE KEYS */;
INSERT INTO `new5kcrm_customer_cares` VALUES (1,'今天他感冒了，过两天再问问他',1444280340,1,1,1,'phone','今天他感冒了，过两天再问问他','',1444280392,1444280392,1);
/*!40000 ALTER TABLE `new5kcrm_customer_cares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_data`
--

DROP TABLE IF EXISTS `new5kcrm_customer_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_data` (
  `customer_id` int(10) unsigned NOT NULL COMMENT '客户id',
  `no_of_employees` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL COMMENT '备注',
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户附表信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_data`
--

LOCK TABLES `new5kcrm_customer_data` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_data` DISABLE KEYS */;
INSERT INTO `new5kcrm_customer_data` VALUES (1,'',''),(2,'','手机号：13681861415\r\n预约时间：2015-10-14 10:30\r\n预约门店：华陌通金钟大厦营业部\r\n                 徐汇区肇嘉浜路680号 金钟大厦 601室\r\n');
/*!40000 ALTER TABLE `new5kcrm_customer_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_focus`
--

DROP TABLE IF EXISTS `new5kcrm_customer_focus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_focus` (
  `focus_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `focus_time` int(10) NOT NULL COMMENT '关注时间',
  PRIMARY KEY (`focus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_focus`
--

LOCK TABLES `new5kcrm_customer_focus` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_focus` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_customer_focus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_record`
--

DROP TABLE IF EXISTS `new5kcrm_customer_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL COMMENT '客户',
  `user_id` int(10) NOT NULL COMMENT '用户',
  `start_time` int(10) NOT NULL COMMENT '时间',
  `type` int(10) NOT NULL COMMENT '1：领取 2：分配',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=ascii COMMENT='客户记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_record`
--

LOCK TABLES `new5kcrm_customer_record` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_record` DISABLE KEYS */;
INSERT INTO `new5kcrm_customer_record` VALUES (1,1,1,1444280177,1),(2,2,1,1444804572,1);
/*!40000 ALTER TABLE `new5kcrm_customer_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_customer_share`
--

DROP TABLE IF EXISTS `new5kcrm_customer_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_customer_share` (
  `share_id` int(10) NOT NULL AUTO_INCREMENT,
  `share_role_id` int(10) NOT NULL COMMENT '分享人ID',
  `by_sharing_id` varchar(150) NOT NULL COMMENT '被分享人ID',
  `customer_id` int(10) NOT NULL COMMENT '客户ID',
  `share_time` int(10) NOT NULL COMMENT '分享时间',
  PRIMARY KEY (`share_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_customer_share`
--

LOCK TABLES `new5kcrm_customer_share` WRITE;
/*!40000 ALTER TABLE `new5kcrm_customer_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_customer_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_email_template`
--

DROP TABLE IF EXISTS `new5kcrm_email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_email_template` (
  `template_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `subject` varchar(200) NOT NULL COMMENT '主题',
  `title` varchar(100) NOT NULL,
  `content` varchar(500) NOT NULL COMMENT '内容',
  `order_id` int(4) NOT NULL COMMENT '顺序id',
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='短信模板';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_email_template`
--

LOCK TABLES `new5kcrm_email_template` WRITE;
/*!40000 ALTER TABLE `new5kcrm_email_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_event`
--

DROP TABLE IF EXISTS `new5kcrm_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_event` (
  `event_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动id',
  `owner_role_id` int(10) NOT NULL COMMENT '所有人岗位',
  `subject` varchar(50) NOT NULL COMMENT '主题',
  `start_date` int(10) NOT NULL COMMENT '开始时间',
  `end_date` int(10) NOT NULL COMMENT '结束时间',
  `venue` varchar(100) NOT NULL COMMENT '活动地点',
  `contacts_id` int(10) NOT NULL COMMENT '联系人id',
  `customer_id` int(10) NOT NULL COMMENT '客户id',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `create_date` int(10) NOT NULL COMMENT '创建时间',
  `update_date` int(10) NOT NULL COMMENT '修改时间',
  `business_id` int(10) NOT NULL COMMENT '商机id',
  `leads_id` int(10) NOT NULL COMMENT '线索id',
  `send_email` int(1) NOT NULL COMMENT '发送通知邮件1不发送0',
  `recurring` int(1) NOT NULL COMMENT '重复1 不重复0',
  `description` text NOT NULL COMMENT '描述',
  `isclose` int(1) NOT NULL COMMENT '是否关闭1开启0关闭',
  `is_deleted` int(1) NOT NULL COMMENT '是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_event`
--

LOCK TABLES `new5kcrm_event` WRITE;
/*!40000 ALTER TABLE `new5kcrm_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_fields`
--

DROP TABLE IF EXISTS `new5kcrm_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_fields` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `model` varchar(20) NOT NULL COMMENT '对应模块小写，如business',
  `is_main` int(1) NOT NULL COMMENT '是否是主表字段1是0否',
  `field` varchar(50) NOT NULL COMMENT '数据库字段名',
  `name` varchar(100) NOT NULL COMMENT '显示标识',
  `form_type` varchar(20) NOT NULL COMMENT '数据类型 text 单行文本 textarea 多行文本 editor 编辑器 box 选项 datetime 日期 number 数字 user员工email邮箱phone手机号mobile电话phone',
  `default_value` varchar(100) NOT NULL COMMENT '默认值',
  `color` varchar(20) NOT NULL COMMENT '颜色',
  `max_length` int(4) NOT NULL COMMENT '字段长度',
  `is_unique` int(1) NOT NULL COMMENT '是否唯一1是0否',
  `is_null` int(1) NOT NULL COMMENT '是否允许为空',
  `is_validate` int(1) NOT NULL COMMENT '是否验证',
  `in_index` int(1) NOT NULL COMMENT '是否列表页显示1是0否',
  `in_add` int(1) NOT NULL DEFAULT '1' COMMENT '是否添加时显示1是0否',
  `input_tips` varchar(500) NOT NULL COMMENT '输入提示',
  `setting` text NOT NULL COMMENT '设置',
  `order_id` int(5) NOT NULL COMMENT '同一模块内的顺序id',
  `operating` int(1) NOT NULL COMMENT '0改删、1改、2无、3删',
  PRIMARY KEY (`field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='字段表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_fields`
--

LOCK TABLES `new5kcrm_fields` WRITE;
/*!40000 ALTER TABLE `new5kcrm_fields` DISABLE KEYS */;
INSERT INTO `new5kcrm_fields` VALUES (1,'',1,'owner_role_id','负责人','user','','',10,0,0,0,1,1,'','',0,2),(2,'',1,'creator_role_id','创建人','user','','',10,0,0,0,1,1,'','',0,2),(3,'',1,'delete_role_id','删除人','user','','',10,0,0,0,1,1,'','',0,2),(4,'',1,'is_deleted','是否删除','deleted','','',1,0,0,0,1,1,'','',0,2),(5,'',1,'create_time','创建时间','datetime','','',10,0,0,0,1,1,'','',0,2),(6,'',1,'update_time','更新时间','datetime','','',10,0,0,0,1,1,'','',0,2),(7,'',1,'delete_time','删除时间','datetime','','',10,0,0,0,1,1,'','',0,2),(8,'customer',1,'name','客户姓名','text','','5521FF',333,1,1,1,1,1,'','',0,1),(9,'customer',1,'origin','客户信息来源','box','','333333',150,0,0,0,0,1,'','array(\'type\'=>\'select\',\'data\'=>array(1=>\'电话营销\',2=>\'网络营销\'))',6,1),(10,'customer',1,'address','客户联系地址','address','','',500,0,0,0,1,1,'','',18,0),(11,'customer',1,'zip_code','邮编','text','','',150,0,0,0,0,1,'','',12,0),(12,'customer',1,'industry','客户行业','box','','050505',150,0,0,0,1,1,'','array(\'type\'=>\'radio\',\'data\'=>array(1=>\'教育/培训\',2=>\'电子商务\',3=>\'对外贸易\'))',5,1),(13,'customer',1,'annual_revenue','年营业额','box','','',150,0,0,0,0,1,'','array(\'type\'=>\'select\',\'data\'=>array(1=>\'1-10万\',2=>\'10-20万\',3=>\'20-50万\'))',14,1),(14,'customer',1,'ownership','公司性质','box','','000000',150,0,0,0,0,1,'','array(\'type\'=>\'radio\',\'data\'=>array(1=>\'合资\',2=>\'国企\',3=>\'民营\'))',7,0),(15,'customer',1,'rating','评分','box','','A3A3A3',150,0,0,1,1,1,'','array(\'type\'=>\'radio\',\'data\'=>array(1=>\'一星\',2=>\'二星\',3=>\'三星\'))',15,0),(16,'business',1,'origin','商机来源','box','','1BA69C',0,0,0,1,1,1,'','array(\'type\'=>\'select\',\'data\'=>array(1=>\'电话营销\',2=>\'网络营销\'))',11,1),(17,'business',1,'type','商机类型','box','','',0,0,0,0,0,1,'','array(\'type\'=>\'select\', \'data\'=>array(1=>\'新业务\',2=>\'现有业务\'))',9,0),(18,'business',1,'gain_rate','赢单率','number','','',0,0,0,0,0,1,'%','',12,0),(19,'business',1,'estimate_price','预计价格','floatnumber','','333333',0,0,1,1,0,1,'单位：元','',13,0),(20,'product',1,'category_id','产品类别','p_box','','',0,0,0,0,0,1,'','',2,2),(21,'business',1,'status_id','状态','b_box','','',0,0,0,0,0,1,'','',10,2),(22,'product',1,'name',' 产品名称','text','','021012',200,1,1,0,1,1,'','',0,1),(51,'product',1,'year_rate','年化收益%','text','0.0','FC0D0D',5,0,1,0,1,1,'年化收益率%','',0,0),(52,'product',1,'str_month','出借期限【月】','box','','333333',4,0,1,0,1,1,'按月选择','array(\'type\'=>\'select\',\'data\'=>array(1=>\'1\',2=>\'3\',3=>\'6\',4=>\'12\'))',0,0),(27,'product',1,'link','详情链接','text','http://','6E6E6E',200,0,0,0,0,1,'','',5,0),(28,'business',1,'name','商机名','text','','090D08',0,1,1,1,1,1,'','',2,1),(29,'business',1,'nextstep','下次联系内容','text','','',0,0,0,0,1,1,'','',15,2),(30,'business',1,'nextstep_time','下次联系时间','datetime','','',0,0,0,1,1,1,'','',14,2),(31,'business',1,'customer_id','客户','customer','','',0,0,0,1,1,1,'','',0,2),(32,'business',1,'contacts_id','联系人','contacts','','',0,0,0,0,0,1,'','',3,2),(33,'business',1,'contract_address','合同签订地址','address','','333333',0,0,0,1,0,1,'','',4,0),(34,'leads',1,'nextstep_time','下次联系时间','datetime','','',0,0,0,0,1,1,'','',8,2),(35,'leads',1,'nextstep','下次联系内容','text','','',0,0,0,0,1,1,'','',9,2),(36,'leads',1,'contacts_name','联系人姓名','text','','333333',0,0,1,1,1,1,'','',1,1),(37,'leads',1,'saltname','尊称','box','','333333',0,0,0,0,1,1,'','array(\'type\'=>\'select\',\'data\'=>array(1=>\'女士\',2=>\'先生\'))',2,1),(38,'leads',1,'mobile','手机','mobile','','333333',0,0,0,1,1,1,'','',3,1),(39,'leads',1,'email','邮箱','email','','',0,0,0,1,0,1,'','',6,1),(40,'leads',1,'position','职位','text','','',0,0,0,0,0,1,'','',5,1),(41,'leads',1,'address','地址','address','','333333',0,0,0,0,0,1,'','',7,0),(42,'customer',0,'no_of_employees','员工数','box','','0A0A0A',150,0,0,0,0,1,'','array(\'type\'=>\'select\',\'data\'=>array(1=>\'5--20人\',2=>\'20-50人\',3=>\'50人以上\'))',13,1),(43,'customer',0,'description','备注','textarea','','',0,0,0,0,0,1,'','',19,1),(44,'leads',0,'description','备注','textarea','','',0,0,0,0,0,1,'','',10,1),(45,'product',0,'description','备注','textarea','','',0,0,0,0,0,1,'','',19,1),(46,'business',0,'description','备注','textarea','','',0,0,0,0,0,1,'','',19,1),(47,'leads',1,'name','客户姓名','text','','05330E',0,0,1,0,0,1,'','',0,1),(48,'leads',1,'source','来源','box','','333333',0,0,1,0,0,1,'','array(\'type\'=>\'select\',\'data\'=>array(1=>\'网络营销\',2=>\'公开媒体\',3=>\'合作伙伴\',4=>\'员工介绍\',5=>\'广告\',6=>\'推销电话\',7=>\'其他\'))',4,1),(49,'business',1,'total_price','商机金额','floatnumber','','333333',0,0,1,0,0,1,'','',0,1);
/*!40000 ALTER TABLE `new5kcrm_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_file`
--

DROP TABLE IF EXISTS `new5kcrm_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_file` (
  `file_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '附件主键',
  `name` varchar(50) NOT NULL COMMENT '附件名',
  `role_id` int(10) NOT NULL COMMENT '创建者岗位',
  `size` int(10) NOT NULL COMMENT '文件大小字节',
  `create_date` int(10) NOT NULL COMMENT '创建时间',
  `file_path` varchar(200) NOT NULL COMMENT '文件路径',
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='附件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_file`
--

LOCK TABLES `new5kcrm_file` WRITE;
/*!40000 ALTER TABLE `new5kcrm_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_finance`
--

DROP TABLE IF EXISTS `new5kcrm_finance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_finance` (
  `finance_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '财务id',
  `name` varchar(50) NOT NULL COMMENT '财务活动名',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `check_role_id` int(10) NOT NULL COMMENT '审核人id',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `check_time` int(10) NOT NULL COMMENT '审核时间',
  `check_result` int(1) NOT NULL COMMENT '0未审核，1审核通过，-1审核未通过，2等到处理',
  `money` float(10,2) NOT NULL COMMENT '实际金额',
  `plan_money` float(10,2) NOT NULL COMMENT '应收（付）金额',
  `income_or_expenses` int(1) NOT NULL COMMENT '收入1还是支出-1',
  `description` varchar(100) NOT NULL COMMENT '财务活动描述',
  PRIMARY KEY (`finance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财务表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_finance`
--

LOCK TABLES `new5kcrm_finance` WRITE;
/*!40000 ALTER TABLE `new5kcrm_finance` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_finance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_knowledge`
--

DROP TABLE IF EXISTS `new5kcrm_knowledge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_knowledge` (
  `knowledge_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `category_id` int(10) NOT NULL COMMENT '文章类别',
  `role_id` int(10) NOT NULL COMMENT '发表人岗位',
  `title` varchar(200) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `create_time` int(10) NOT NULL COMMENT '发表时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `hits` int(10) NOT NULL COMMENT '点击次数',
  `color` varchar(50) NOT NULL,
  PRIMARY KEY (`knowledge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='存放知识文章信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_knowledge`
--

LOCK TABLES `new5kcrm_knowledge` WRITE;
/*!40000 ALTER TABLE `new5kcrm_knowledge` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_knowledge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_knowledge_category`
--

DROP TABLE IF EXISTS `new5kcrm_knowledge_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_knowledge_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章类别id',
  `parent_id` int(11) NOT NULL COMMENT '父类别id',
  `name` varchar(30) NOT NULL COMMENT '类别名称',
  `description` varchar(100) NOT NULL COMMENT '备注',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='知识文章分类信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_knowledge_category`
--

LOCK TABLES `new5kcrm_knowledge_category` WRITE;
/*!40000 ALTER TABLE `new5kcrm_knowledge_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_knowledge_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_leads`
--

DROP TABLE IF EXISTS `new5kcrm_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_leads` (
  `leads_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '线索主键',
  `owner_role_id` int(10) NOT NULL COMMENT '拥有者岗位',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者岗位',
  `name` varchar(255) NOT NULL,
  `position` varchar(20) NOT NULL COMMENT '职位',
  `contacts_name` varchar(255) NOT NULL,
  `saltname` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL COMMENT '电子邮箱',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '修改时间',
  `is_deleted` int(1) NOT NULL COMMENT '是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人的岗位id',
  `delete_time` int(10) NOT NULL,
  `is_transformed` int(1) NOT NULL COMMENT '是否转换',
  `transform_role_id` int(10) NOT NULL COMMENT '转换者',
  `contacts_id` int(10) NOT NULL COMMENT '转换成联系人',
  `customer_id` int(10) NOT NULL COMMENT '转换成的客户',
  `business_id` int(10) NOT NULL COMMENT '转换成的商机',
  `nextstep` varchar(50) NOT NULL COMMENT '下一次联系',
  `nextstep_time` int(10) NOT NULL COMMENT '联系时间',
  `have_time` int(10) NOT NULL COMMENT '最后一次领取时间',
  `address` varchar(500) NOT NULL,
  `source` varchar(500) NOT NULL COMMENT '线索来源',
  PRIMARY KEY (`leads_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='线索表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_leads`
--

LOCK TABLES `new5kcrm_leads` WRITE;
/*!40000 ALTER TABLE `new5kcrm_leads` DISABLE KEYS */;
INSERT INTO `new5kcrm_leads` VALUES (1,1,1,'李明明','老板','李明明','先生','13800138000','',1442311606,1442311699,0,0,0,1,1,1,1,0,'',0,1442311606,'上海市\n市辖区\n黄浦区','网络营销');
/*!40000 ALTER TABLE `new5kcrm_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_leads_data`
--

DROP TABLE IF EXISTS `new5kcrm_leads_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_leads_data` (
  `leads_id` int(10) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL COMMENT '备注',
  `tqq` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`leads_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_leads_data`
--

LOCK TABLES `new5kcrm_leads_data` WRITE;
/*!40000 ALTER TABLE `new5kcrm_leads_data` DISABLE KEYS */;
INSERT INTO `new5kcrm_leads_data` VALUES (1,'','');
/*!40000 ALTER TABLE `new5kcrm_leads_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_leads_record`
--

DROP TABLE IF EXISTS `new5kcrm_leads_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_leads_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `leads_id` int(10) NOT NULL,
  `owner_role_id` int(10) NOT NULL,
  `start_time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_leads_record`
--

LOCK TABLES `new5kcrm_leads_record` WRITE;
/*!40000 ALTER TABLE `new5kcrm_leads_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_leads_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_log`
--

DROP TABLE IF EXISTS `new5kcrm_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_log` (
  `log_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `role_id` int(11) NOT NULL COMMENT '创建者岗位',
  `category_id` int(10) NOT NULL,
  `create_date` int(10) NOT NULL COMMENT '创建时间',
  `update_date` int(10) NOT NULL COMMENT '更新时间',
  `subject` varchar(200) NOT NULL COMMENT '主题',
  `content` text NOT NULL COMMENT '内容',
  `comment_id` int(10) NOT NULL COMMENT '评论id',
  `about_roles` varchar(200) NOT NULL COMMENT '新增相关人',
  `about_roles_name` varchar(500) NOT NULL COMMENT '新增相关人姓名',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_log`
--

LOCK TABLES `new5kcrm_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_log` DISABLE KEYS */;
INSERT INTO `new5kcrm_log` VALUES (1,1,1,1442389588,1442389588,'工号','0501001',0,'',''),(2,1,1,1444280252,1444280252,'今天和他打了电话','今天和他打了电话，问了问他最近投资意向',0,'','');
/*!40000 ALTER TABLE `new5kcrm_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_log_category`
--

DROP TABLE IF EXISTS `new5kcrm_log_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_log_category` (
  `category_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `name` varchar(200) NOT NULL COMMENT '分类名',
  `order_id` int(10) NOT NULL COMMENT '顺序id',
  `description` varchar(500) NOT NULL COMMENT '描述',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='日志类型表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_log_category`
--

LOCK TABLES `new5kcrm_log_category` WRITE;
/*!40000 ALTER TABLE `new5kcrm_log_category` DISABLE KEYS */;
INSERT INTO `new5kcrm_log_category` VALUES (2,'月报',3,'每月工作总结'),(3,'周报',2,'每周工作总结'),(4,'日报',1,'每日工作总结');
/*!40000 ALTER TABLE `new5kcrm_log_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_login_history`
--

DROP TABLE IF EXISTS `new5kcrm_login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_login_history` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `login_time` int(11) NOT NULL COMMENT '登录时间',
  `login_ip` varchar(50) NOT NULL COMMENT '登录ip',
  `login_status` char(1) NOT NULL COMMENT '登录 1成功   2 失败',
  PRIMARY KEY (`login_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='用户登录历史表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_login_history`
--

LOCK TABLES `new5kcrm_login_history` WRITE;
/*!40000 ALTER TABLE `new5kcrm_login_history` DISABLE KEYS */;
INSERT INTO `new5kcrm_login_history` VALUES (12,1,1441691952,'192.168.27.17','1'),(13,1,1441696222,'192.168.27.17','1'),(14,1,1441701009,'192.168.27.17','1'),(15,1,1441956161,'140.206.126.42','1'),(16,1,1441956168,'140.206.126.42','1'),(17,1,1441957685,'140.206.126.42','1'),(18,1,1442046967,'180.166.18.114','1'),(19,1,1442102207,'118.187.21.113','1'),(20,1,1442199605,'140.206.126.42','1'),(21,1,1442214959,'140.206.126.42','1'),(22,1,1442299961,'140.206.126.42','1'),(23,1,1442387736,'140.206.126.42','1'),(24,1,1443000317,'124.74.111.94','1'),(25,1,1444274757,'124.74.111.94','1'),(26,1,1444700530,'124.74.111.94','1'),(27,1,1444786124,'140.206.126.42','1'),(28,1,1444804536,'124.74.111.94','1'),(29,1,1444957442,'124.74.111.94','1');
/*!40000 ALTER TABLE `new5kcrm_login_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_message`
--

DROP TABLE IF EXISTS `new5kcrm_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_message` (
  `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to_role_id` int(11) unsigned NOT NULL,
  `from_role_id` int(11) unsigned NOT NULL,
  `content` text NOT NULL,
  `read_time` int(11) unsigned NOT NULL,
  `send_time` int(11) unsigned NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `to_role_id` (`to_role_id`,`from_role_id`,`read_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_message`
--

LOCK TABLES `new5kcrm_message` WRITE;
/*!40000 ALTER TABLE `new5kcrm_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_navigation`
--

DROP TABLE IF EXISTS `new5kcrm_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_navigation` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `url` varchar(200) NOT NULL,
  `postion` varchar(10) NOT NULL COMMENT '位置',
  `listorder` int(11) NOT NULL COMMENT '排序',
  `module` varchar(20) NOT NULL COMMENT '模块',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='导航菜单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_navigation`
--

LOCK TABLES `new5kcrm_navigation` WRITE;
/*!40000 ALTER TABLE `new5kcrm_navigation` DISABLE KEYS */;
INSERT INTO `new5kcrm_navigation` VALUES (1,'线索','/index.php?m=leads','top',0,'Leads'),(2,'客户','/index.php?m=customer','top',1,'Customer'),(3,'商机','/index.php?m=business','more',0,'Business'),(4,'理财产品','/index.php?m=product','top',2,'Product'),(5,'任务','/index.php?m=task','top',3,'Task'),(6,'日程','/index.php?m=event','more',5,'Event'),(7,'合同','/index.php?m=contract','top',4,'Contract'),(8,'财务','/index.php?m=finance','top',5,'Finance'),(9,'日志','/index.php?m=log','more',1,''),(10,'知识','/index.php?m=knowledge','more',2,'Knowledge'),(11,'营销','/index.php?m=setting&a=sendsms','more',3,''),(12,'站内信','/index.php?m=message','more',4,''),(13,'我的面板','/','user',0,''),(14,'个人资料','/index.php?m=user&a=edit','user',1,''),(15,'组织架构','/index.php?m=user','user',2,''),(16,'权限分配','/index.php?m=user&a=role','user',3,'Role'),(17,'公告管理','/index.php?m=announcement','user',4,'announcement'),(18,'操作日志','/index.php?m=action_log','user',5,''),(19,'系统设置','/index.php?m=setting','user',6,'Setting');
/*!40000 ALTER TABLE `new5kcrm_navigation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_note`
--

DROP TABLE IF EXISTS `new5kcrm_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_note` (
  `note_id` int(10) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL,
  `content` varchar(1000) NOT NULL COMMENT '内容',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='便笺表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_note`
--

LOCK TABLES `new5kcrm_note` WRITE;
/*!40000 ALTER TABLE `new5kcrm_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_payables`
--

DROP TABLE IF EXISTS `new5kcrm_payables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_payables` (
  `payables_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应付款id',
  `customer_id` int(10) NOT NULL COMMENT '客户id',
  `contract_id` int(10) DEFAULT NULL COMMENT '合同id',
  `name` varchar(500) NOT NULL COMMENT '应付款名',
  `price` decimal(10,2) NOT NULL COMMENT '应付金额',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `owner_role_id` int(10) NOT NULL,
  `description` text NOT NULL COMMENT '描述',
  `pay_time` int(10) NOT NULL COMMENT '付款时间',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `status` int(2) NOT NULL COMMENT '状态0未收1部分收2已收',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `is_deleted` int(1) NOT NULL DEFAULT '0' COMMENT ' 是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`payables_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='应付款表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_payables`
--

LOCK TABLES `new5kcrm_payables` WRITE;
/*!40000 ALTER TABLE `new5kcrm_payables` DISABLE KEYS */;
INSERT INTO `new5kcrm_payables` VALUES (1,1,1,'月满盈付息',100000.00,1,1,'',1446998400,1444280865,0,1444280865,0,0,0);
/*!40000 ALTER TABLE `new5kcrm_payables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_paymentorder`
--

DROP TABLE IF EXISTS `new5kcrm_paymentorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_paymentorder` (
  `paymentorder_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '付款单id',
  `name` varchar(500) NOT NULL COMMENT '付款单主题',
  `money` decimal(10,2) NOT NULL COMMENT '付款金额',
  `payables_id` int(10) NOT NULL COMMENT '应付款id',
  `description` text NOT NULL COMMENT '描述',
  `pay_time` int(10) NOT NULL COMMENT '付款时间',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `owner_role_id` int(10) NOT NULL COMMENT '负责人',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL COMMENT '审核时间',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `is_deleted` int(1) NOT NULL DEFAULT '0' COMMENT ' 是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`paymentorder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='付款单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_paymentorder`
--

LOCK TABLES `new5kcrm_paymentorder` WRITE;
/*!40000 ALTER TABLE `new5kcrm_paymentorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_paymentorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_permission`
--

DROP TABLE IF EXISTS `new5kcrm_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_permission` (
  `permission_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '权限id',
  `role_id` int(10) NOT NULL COMMENT '岗位id',
  `position_id` int(10) NOT NULL COMMENT '岗位组id',
  `url` varchar(50) NOT NULL COMMENT '对应模块操作',
  `description` varchar(200) NOT NULL COMMENT '权限备注',
  PRIMARY KEY (`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表用来存放权限信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_permission`
--

LOCK TABLES `new5kcrm_permission` WRITE;
/*!40000 ALTER TABLE `new5kcrm_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_position`
--

DROP TABLE IF EXISTS `new5kcrm_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_position` (
  `position_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '岗位id',
  `parent_id` int(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `department_id` int(10) NOT NULL,
  `description` varchar(200) NOT NULL COMMENT '描述',
  PRIMARY KEY (`position_id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='岗位表控制权限';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_position`
--

LOCK TABLES `new5kcrm_position` WRITE;
/*!40000 ALTER TABLE `new5kcrm_position` DISABLE KEYS */;
INSERT INTO `new5kcrm_position` VALUES (1,0,'总经理',1,''),(2,1,'总经理',4,''),(3,2,'业务总监',4,''),(4,1,'总经理',10,''),(5,1,'副总经理',1,''),(6,4,'总监',10,''),(7,4,'团队经理',10,''),(8,4,'财务',10,''),(9,4,'行政',10,''),(10,4,'洁卫',10,''),(11,4,'业务员',10,''),(12,2,'营销部经理',4,''),(13,2,'团队经理',4,''),(14,2,'人事专员',4,''),(15,2,'前台',4,''),(16,2,'理财师',4,''),(17,1,'门店经理',9,''),(18,17,'门店副经理',9,''),(19,17,'团队经理',9,''),(20,17,'前台',9,''),(21,17,'理财师',9,''),(22,1,'团队负责人',8,''),(23,22,'总监',8,''),(24,22,'团队经理',8,''),(25,22,'理财师',8,''),(26,1,'营业厅经理',7,''),(27,26,'团队经理',7,''),(28,26,'前台',7,''),(29,26,'保洁',7,''),(30,26,'理财师',7,''),(31,1,'门店经理',6,''),(32,31,'总监',6,''),(33,31,'团队经理',6,''),(34,31,'前台',6,''),(35,31,'理财师',6,''),(36,1,'门店经理',5,''),(37,36,'总监',5,''),(38,36,'团队经理',5,''),(39,36,'前台',5,''),(40,36,'保洁员',5,''),(41,36,'理财师',5,'');
/*!40000 ALTER TABLE `new5kcrm_position` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_praise`
--

DROP TABLE IF EXISTS `new5kcrm_praise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_praise` (
  `praise_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL COMMENT '日志id',
  `role_id` int(10) NOT NULL COMMENT '赞的人role_id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_praise`
--

LOCK TABLES `new5kcrm_praise` WRITE;
/*!40000 ALTER TABLE `new5kcrm_praise` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_praise` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product`
--

DROP TABLE IF EXISTS `new5kcrm_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product` (
  `product_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '产品id',
  `category_id` int(11) NOT NULL COMMENT '产品类别的id',
  `name` varchar(200) NOT NULL DEFAULT '',
  `creator_role_id` int(10) NOT NULL COMMENT '产品信息添加者',
  `link` varchar(200) NOT NULL DEFAULT '',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '修改时间',
  `year_rate` varchar(5) NOT NULL DEFAULT '0.0',
  `str_month` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product`
--

LOCK TABLES `new5kcrm_product` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product` DISABLE KEYS */;
INSERT INTO `new5kcrm_product` VALUES (1,1,'月满盈',1,'http://',1444279551,1444961517,'6.5','1'),(2,1,'季满盈',1,'http://',1444279771,1444279771,'9.0','3'),(3,1,'双季盈',1,'http://',1444279819,1444961530,'10.0','6'),(4,1,'双季通',1,'http://',1444279857,1444961542,'9.6','6'),(5,1,'年富盈',1,'http://',1444279897,1444961629,'13.0','12'),(6,1,'年富通',1,'http://',1444279929,1444961638,'12.0','12');
/*!40000 ALTER TABLE `new5kcrm_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product_attribute`
--

DROP TABLE IF EXISTS `new5kcrm_product_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product_attribute` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '产品属性id',
  `group_id` int(10) NOT NULL COMMENT '产品id',
  `name` varchar(20) NOT NULL COMMENT '属性名',
  `description` varchar(50) DEFAULT NULL COMMENT '产品属性备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表存放产品属性信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product_attribute`
--

LOCK TABLES `new5kcrm_product_attribute` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_product_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product_attribute_group`
--

DROP TABLE IF EXISTS `new5kcrm_product_attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product_attribute_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '产品属性组id',
  `name` varchar(20) NOT NULL COMMENT '产品属性组名称',
  `description` varchar(100) DEFAULT NULL COMMENT '产品属性组备注',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表存放产品属性组相关信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product_attribute_group`
--

LOCK TABLES `new5kcrm_product_attribute_group` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product_attribute_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_product_attribute_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product_attribute_relation`
--

DROP TABLE IF EXISTS `new5kcrm_product_attribute_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product_attribute_relation` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '产品属性关系id',
  `product_id` int(10) NOT NULL COMMENT '产品id',
  `attribute_id` int(10) NOT NULL COMMENT '产品属性id',
  `description` varchar(100) DEFAULT NULL COMMENT '产品属性关系描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表存放产品属性关系的相关信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product_attribute_relation`
--

LOCK TABLES `new5kcrm_product_attribute_relation` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product_attribute_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_product_attribute_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product_category`
--

DROP TABLE IF EXISTS `new5kcrm_product_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '产品类别id',
  `parent_id` int(11) NOT NULL COMMENT '父类别id',
  `name` varchar(30) NOT NULL COMMENT '类别名称',
  `description` varchar(100) NOT NULL COMMENT '备注',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product_category`
--

LOCK TABLES `new5kcrm_product_category` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product_category` DISABLE KEYS */;
INSERT INTO `new5kcrm_product_category` VALUES (1,0,'默认','');
/*!40000 ALTER TABLE `new5kcrm_product_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product_data`
--

DROP TABLE IF EXISTS `new5kcrm_product_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product_data` (
  `product_id` int(10) NOT NULL COMMENT '主键',
  `description` text NOT NULL COMMENT '备注',
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品信息附表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product_data`
--

LOCK TABLES `new5kcrm_product_data` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product_data` DISABLE KEYS */;
INSERT INTO `new5kcrm_product_data` VALUES (1,'月满盈 预期年化收益率6.5%，出借期限1个月。例：投资20万元到期收益为1083元。'),(2,'季满盈 预期年化收益率9 %，出借期限3个月。例：投资20万元到期收益为4500元。'),(3,'双季盈 预期年化收益率10 %，出借期限6个月。例：投资20万元到期收益为10000元。'),(4,' 双季通 预期年化收益率9.6%，出借期限6个月，（收益按季提前返还）例：投资20万元到期收益为9600元。分两期，每季首返还4800元。 '),(5,'年富盈 预期年化收益率13 %，出借期限12个月。例：投资20万元到期收益为26000元。'),(6,'年富通 预期年化收益率12%，出借期限12个月，（收益按季提前返还）例：投资20万元到期收益为24000元。分四期，每季首返还6000元。');
/*!40000 ALTER TABLE `new5kcrm_product_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_product_images`
--

DROP TABLE IF EXISTS `new5kcrm_product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_product_images` (
  `images_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL COMMENT '关联产品id',
  `is_main` int(1) NOT NULL COMMENT '0：副图  1：主图',
  `name` varchar(500) NOT NULL COMMENT '源文件名',
  `save_name` varchar(500) NOT NULL COMMENT '保存至服务器的文件名',
  `size` varchar(500) NOT NULL COMMENT 'KB',
  `path` varchar(500) NOT NULL COMMENT '路径',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `listorder` int(10) NOT NULL COMMENT '排序',
  PRIMARY KEY (`images_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='产品图库';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_product_images`
--

LOCK TABLES `new5kcrm_product_images` WRITE;
/*!40000 ALTER TABLE `new5kcrm_product_images` DISABLE KEYS */;
INSERT INTO `new5kcrm_product_images` VALUES (1,2,1,'201502121311042900.jpg','5615f5db32a51.jpg','14.43','./Uploads/201510/08/5615f5db32a51.jpg',1444279771,1),(2,1,1,'月满盈.jpg','56205ced851ea.jpg','14.20','./Uploads/201510/16/56205ced851ea.jpg',1444961517,2),(3,3,1,'双季盈.jpg','56205cfadf7dd.jpg','14.03','./Uploads/201510/16/56205cfadf7dd.jpg',1444961530,3),(4,4,1,'双季通.jpg','56205d068c386.jpg','14.13','./Uploads/201510/16/56205d068c386.jpg',1444961542,4),(5,5,1,'年富盈.jpg','56205d5d71fd0.jpg','13.97','./Uploads/201510/16/56205d5d71fd0.jpg',1444961629,5),(6,6,1,'年富通.jpg','56205d66befb3.jpg','14.06','./Uploads/201510/16/56205d66befb3.jpg',1444961638,6);
/*!40000 ALTER TABLE `new5kcrm_product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_contract`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_contract` (
  `id` int(10) NOT NULL,
  `business_id` int(10) NOT NULL COMMENT '商机id',
  `contract_id` int(10) NOT NULL COMMENT '合同id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商机合同关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_contract`
--

LOCK TABLES `new5kcrm_r_business_contract` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_contract` DISABLE KEYS */;
INSERT INTO `new5kcrm_r_business_contract` VALUES (0,1,1);
/*!40000 ALTER TABLE `new5kcrm_r_business_contract` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_customer`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_customer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `business_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_customer`
--

LOCK TABLES `new5kcrm_r_business_customer` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_business_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_event`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_event` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `business_id` int(10) NOT NULL,
  `event_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_event`
--

LOCK TABLES `new5kcrm_r_business_event` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_business_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_file`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `business_id` int(10) NOT NULL,
  `file_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_file`
--

LOCK TABLES `new5kcrm_r_business_file` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_business_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `business_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商机和日志id对应关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_log`
--

LOCK TABLES `new5kcrm_r_business_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_business_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_product`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `business_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `sales_price` float(10,2) NOT NULL COMMENT '成交价',
  `estimate_price` float(10,2) NOT NULL COMMENT '报价',
  `amount` int(10) NOT NULL COMMENT '产品交易数量',
  `discount_rate` int(3) NOT NULL COMMENT '折扣率',
  `tax_rate` int(3) NOT NULL COMMENT '税率',
  `unit_price` float(9,2) NOT NULL COMMENT '单价',
  `subtotal` float(9,2) NOT NULL COMMENT '小计',
  `description` varchar(200) NOT NULL COMMENT '产品交易备注',
  `subtotal_val` float(9,2) NOT NULL COMMENT '小计和',
  `discount_price` float(9,2) NOT NULL COMMENT '其他费用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_product`
--

LOCK TABLES `new5kcrm_r_business_product` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_product` DISABLE KEYS */;
INSERT INTO `new5kcrm_r_business_product` VALUES (1,1,1,0.00,0.00,1,0,0,0.00,0.00,'',0.00,0.00);
/*!40000 ALTER TABLE `new5kcrm_r_business_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_status`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_status` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '关系主键',
  `business_id` int(10) NOT NULL COMMENT '商机id',
  `gain_rate` int(3) NOT NULL,
  `status_id` int(10) NOT NULL COMMENT '状态id',
  `description` text NOT NULL COMMENT '阶段描述',
  `due_date` int(10) NOT NULL COMMENT '预计成交日期',
  `owner_role_id` int(10) NOT NULL COMMENT '负责人id',
  `update_time` int(10) NOT NULL COMMENT '推进时间',
  `update_role_id` int(10) NOT NULL COMMENT '推进人',
  `total_price` float(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商机状态阶段表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_status`
--

LOCK TABLES `new5kcrm_r_business_status` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_business_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_business_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_business_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_business_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `business_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_business_task`
--

LOCK TABLES `new5kcrm_r_business_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_business_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_business_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contacts_customer`
--

DROP TABLE IF EXISTS `new5kcrm_r_contacts_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contacts_customer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contacts_customer`
--

LOCK TABLES `new5kcrm_r_contacts_customer` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_customer` DISABLE KEYS */;
INSERT INTO `new5kcrm_r_contacts_customer` VALUES (1,1,1);
/*!40000 ALTER TABLE `new5kcrm_r_contacts_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contacts_event`
--

DROP TABLE IF EXISTS `new5kcrm_r_contacts_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contacts_event` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) NOT NULL,
  `event_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contacts_event`
--

LOCK TABLES `new5kcrm_r_contacts_event` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contacts_file`
--

DROP TABLE IF EXISTS `new5kcrm_r_contacts_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contacts_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) NOT NULL,
  `file_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contacts_file`
--

LOCK TABLES `new5kcrm_r_contacts_file` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contacts_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_contacts_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contacts_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contacts_log`
--

LOCK TABLES `new5kcrm_r_contacts_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contacts_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_contacts_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contacts_task` (
  `id` int(10) NOT NULL,
  `contacts_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='联系人任务关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contacts_task`
--

LOCK TABLES `new5kcrm_r_contacts_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_contacts_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contract_file`
--

DROP TABLE IF EXISTS `new5kcrm_r_contract_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contract_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contract_id` int(10) NOT NULL COMMENT '合同id',
  `file_id` int(10) NOT NULL COMMENT '文件id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='合同文件关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contract_file`
--

LOCK TABLES `new5kcrm_r_contract_file` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contract_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_contract_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_contract_product`
--

DROP TABLE IF EXISTS `new5kcrm_r_contract_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_contract_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contract_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `sales_price` float(10,2) NOT NULL,
  `estimate_price` float(10,2) NOT NULL,
  `amount` int(10) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_contract_product`
--

LOCK TABLES `new5kcrm_r_contract_product` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_contract_product` DISABLE KEYS */;
INSERT INTO `new5kcrm_r_contract_product` VALUES (1,1,1,100000.00,100000.00,1,'');
/*!40000 ALTER TABLE `new5kcrm_r_contract_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_customer_event`
--

DROP TABLE IF EXISTS `new5kcrm_r_customer_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_customer_event` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `event_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_customer_event`
--

LOCK TABLES `new5kcrm_r_customer_event` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_customer_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_customer_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_customer_file`
--

DROP TABLE IF EXISTS `new5kcrm_r_customer_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_customer_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `file_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_customer_file`
--

LOCK TABLES `new5kcrm_r_customer_file` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_customer_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_customer_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_customer_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_customer_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_customer_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_customer_log`
--

LOCK TABLES `new5kcrm_r_customer_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_customer_log` DISABLE KEYS */;
INSERT INTO `new5kcrm_r_customer_log` VALUES (1,1,2);
/*!40000 ALTER TABLE `new5kcrm_r_customer_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_customer_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_customer_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_customer_task` (
  `id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户任务关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_customer_task`
--

LOCK TABLES `new5kcrm_r_customer_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_customer_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_customer_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_event_file`
--

DROP TABLE IF EXISTS `new5kcrm_r_event_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_event_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) NOT NULL,
  `file_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_event_file`
--

LOCK TABLES `new5kcrm_r_event_file` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_event_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_event_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_event_leads`
--

DROP TABLE IF EXISTS `new5kcrm_r_event_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_event_leads` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) NOT NULL,
  `leads_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_event_leads`
--

LOCK TABLES `new5kcrm_r_event_leads` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_event_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_event_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_event_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_event_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) DEFAULT NULL,
  `log_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动和日志id对应表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_event_log`
--

LOCK TABLES `new5kcrm_r_event_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_event_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_event_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_event_product`
--

DROP TABLE IF EXISTS `new5kcrm_r_event_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_event_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) DEFAULT NULL,
  `product_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_event_product`
--

LOCK TABLES `new5kcrm_r_event_product` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_event_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_event_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_file_finance`
--

DROP TABLE IF EXISTS `new5kcrm_r_file_finance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_file_finance` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `finance_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_file_finance`
--

LOCK TABLES `new5kcrm_r_file_finance` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_file_finance` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_file_finance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_file_leads`
--

DROP TABLE IF EXISTS `new5kcrm_r_file_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_file_leads` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `leads_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文件和日志对应关系';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_file_leads`
--

LOCK TABLES `new5kcrm_r_file_leads` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_file_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_file_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_file_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_file_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_file_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文件和日志对应关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_file_log`
--

LOCK TABLES `new5kcrm_r_file_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_file_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_file_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_file_product`
--

DROP TABLE IF EXISTS `new5kcrm_r_file_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_file_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_file_product`
--

LOCK TABLES `new5kcrm_r_file_product` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_file_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_file_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_file_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_file_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_file_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_file_task`
--

LOCK TABLES `new5kcrm_r_file_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_file_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_file_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_file_user`
--

DROP TABLE IF EXISTS `new5kcrm_r_file_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_file_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `file_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='员工对应文件资料表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_file_user`
--

LOCK TABLES `new5kcrm_r_file_user` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_file_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_file_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_finance_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_finance_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_finance_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `finance_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_finance_log`
--

LOCK TABLES `new5kcrm_r_finance_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_finance_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_finance_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_leads_log`
--

DROP TABLE IF EXISTS `new5kcrm_r_leads_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_leads_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `leads_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_leads_log`
--

LOCK TABLES `new5kcrm_r_leads_log` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_leads_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_leads_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_leads_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_leads_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_leads_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `leads_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_leads_task`
--

LOCK TABLES `new5kcrm_r_leads_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_leads_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_leads_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_log_product`
--

DROP TABLE IF EXISTS `new5kcrm_r_log_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_log_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_log_product`
--

LOCK TABLES `new5kcrm_r_log_product` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_log_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_log_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_log_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_log_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_log_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) NOT NULL,
  `log_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='任务和日志id对应表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_log_task`
--

LOCK TABLES `new5kcrm_r_log_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_log_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_log_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_log_user`
--

DROP TABLE IF EXISTS `new5kcrm_r_log_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_log_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='员工备注信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_log_user`
--

LOCK TABLES `new5kcrm_r_log_user` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_log_user` DISABLE KEYS */;
INSERT INTO `new5kcrm_r_log_user` VALUES (1,1,2);
/*!40000 ALTER TABLE `new5kcrm_r_log_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_r_product_task`
--

DROP TABLE IF EXISTS `new5kcrm_r_product_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_r_product_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_r_product_task`
--

LOCK TABLES `new5kcrm_r_product_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_r_product_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_r_product_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_receivables`
--

DROP TABLE IF EXISTS `new5kcrm_receivables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_receivables` (
  `receivables_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应收款id',
  `customer_id` int(10) NOT NULL COMMENT '客户id',
  `contract_id` int(10) DEFAULT NULL COMMENT '合同id',
  `name` varchar(500) NOT NULL COMMENT '应收款名',
  `price` decimal(10,2) NOT NULL COMMENT '应收金额',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `owner_role_id` int(10) NOT NULL,
  `description` text NOT NULL COMMENT '描述',
  `pay_time` int(10) NOT NULL COMMENT '收款时间',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `status` int(2) NOT NULL COMMENT '状态0未收1部分收2已收',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `is_deleted` int(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`receivables_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='应收款表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_receivables`
--

LOCK TABLES `new5kcrm_receivables` WRITE;
/*!40000 ALTER TABLE `new5kcrm_receivables` DISABLE KEYS */;
INSERT INTO `new5kcrm_receivables` VALUES (1,1,1,'月满盈打款',100000.00,1,1,'',1444233600,1444280606,2,1444280606,0,0,0);
/*!40000 ALTER TABLE `new5kcrm_receivables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_receivingorder`
--

DROP TABLE IF EXISTS `new5kcrm_receivingorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_receivingorder` (
  `receivingorder_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '收款单id',
  `name` varchar(500) NOT NULL COMMENT '收款单主题',
  `money` decimal(10,2) NOT NULL COMMENT '收款金额',
  `receivables_id` int(10) NOT NULL COMMENT '应收款id',
  `description` text NOT NULL COMMENT '描述',
  `pay_time` int(10) NOT NULL COMMENT '付款时间',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者id',
  `owner_role_id` int(10) NOT NULL COMMENT '负责人',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL COMMENT '审核时间',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `is_deleted` int(1) NOT NULL DEFAULT '0' COMMENT ' 是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`receivingorder_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='收款单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_receivingorder`
--

LOCK TABLES `new5kcrm_receivingorder` WRITE;
/*!40000 ALTER TABLE `new5kcrm_receivingorder` DISABLE KEYS */;
INSERT INTO `new5kcrm_receivingorder` VALUES (1,'5kcrm201510081511',100000.00,1,'',1444233600,1,1,1,1444280780,1444280780,0,0,0);
/*!40000 ALTER TABLE `new5kcrm_receivingorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_role`
--

DROP TABLE IF EXISTS `new5kcrm_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_role` (
  `role_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '岗位id',
  `position_id` int(10) NOT NULL COMMENT '岗位组名',
  `user_id` int(10) NOT NULL COMMENT '员工id',
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='本表存放用户岗位信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_role`
--

LOCK TABLES `new5kcrm_role` WRITE;
/*!40000 ALTER TABLE `new5kcrm_role` DISABLE KEYS */;
INSERT INTO `new5kcrm_role` VALUES (1,1,1),(2,36,2);
/*!40000 ALTER TABLE `new5kcrm_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_role_department`
--

DROP TABLE IF EXISTS `new5kcrm_role_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_role_department` (
  `department_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '部门id',
  `parent_id` int(10) NOT NULL COMMENT '父类部门id',
  `name` varchar(50) NOT NULL COMMENT '部门名',
  `description` varchar(200) NOT NULL COMMENT '部门描述',
  PRIMARY KEY (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='本表存放部门信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_role_department`
--

LOCK TABLES `new5kcrm_role_department` WRITE;
/*!40000 ALTER TABLE `new5kcrm_role_department` DISABLE KEYS */;
INSERT INTO `new5kcrm_role_department` VALUES (1,0,'互联网事业部','负责线下团队的中和管理'),(2,0,'总部财务','总部财务'),(3,0,'总部审核','总部审核'),(4,0,'世纪广场',''),(5,0,'南桥职场','南桥职场'),(6,0,'平安职场','平安职场'),(7,0,'川沙职场','川沙职场'),(8,0,'李华团队','李华团队'),(9,0,'圣爱职场','圣爱职场'),(10,0,'桐庐职场','桐庐职场');
/*!40000 ALTER TABLE `new5kcrm_role_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_sign`
--

DROP TABLE IF EXISTS `new5kcrm_sign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_sign` (
  `sign_id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `role_id` int(10) NOT NULL,
  `x` float(10,6) NOT NULL COMMENT 'x坐标',
  `y` float(10,6) NOT NULL COMMENT 'y坐标',
  `title` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `log` varchar(100) NOT NULL,
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`sign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_sign`
--

LOCK TABLES `new5kcrm_sign` WRITE;
/*!40000 ALTER TABLE `new5kcrm_sign` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_sign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_sign_img`
--

DROP TABLE IF EXISTS `new5kcrm_sign_img`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_sign_img` (
  `img_id` int(10) NOT NULL AUTO_INCREMENT,
  `sign_id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '图片上传时名字',
  `save_name` varchar(100) NOT NULL COMMENT '图片保存名',
  `path` varchar(200) NOT NULL,
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`img_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_sign_img`
--

LOCK TABLES `new5kcrm_sign_img` WRITE;
/*!40000 ALTER TABLE `new5kcrm_sign_img` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_sign_img` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_sms_record`
--

DROP TABLE IF EXISTS `new5kcrm_sms_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_sms_record` (
  `sms_record_id` int(10) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL COMMENT '发件人',
  `telephone` varchar(800) NOT NULL COMMENT '发送手机号码',
  `content` text NOT NULL COMMENT '发送的内容',
  `sendtime` int(10) NOT NULL COMMENT '发送时间',
  PRIMARY KEY (`sms_record_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='发送短息记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_sms_record`
--

LOCK TABLES `new5kcrm_sms_record` WRITE;
/*!40000 ALTER TABLE `new5kcrm_sms_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_sms_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_sms_template`
--

DROP TABLE IF EXISTS `new5kcrm_sms_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_sms_template` (
  `template_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `subject` varchar(200) NOT NULL COMMENT '主题',
  `content` varchar(500) NOT NULL COMMENT '内容',
  `order_id` int(4) NOT NULL COMMENT '顺序id',
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='短信模板';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_sms_template`
--

LOCK TABLES `new5kcrm_sms_template` WRITE;
/*!40000 ALTER TABLE `new5kcrm_sms_template` DISABLE KEYS */;
INSERT INTO `new5kcrm_sms_template` VALUES (1,'默认模板','有一个特别的日子，鲜花都为你展现；有一个特殊的日期，阳光都为你温暖；有一个美好的时刻，百灵都为你欢颜；有一个难忘的今天，亲朋都为你祝愿；那就是今天是你的生日，祝你幸福安康顺意连年！',1);
/*!40000 ALTER TABLE `new5kcrm_sms_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_task`
--

DROP TABLE IF EXISTS `new5kcrm_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_task` (
  `task_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '任务id',
  `owner_role_id` varchar(200) NOT NULL COMMENT '任务所有者岗位',
  `about_roles` varchar(200) NOT NULL COMMENT '任务相关人',
  `subject` varchar(100) NOT NULL COMMENT '任务主题',
  `due_date` int(10) NOT NULL COMMENT '任务结束时间',
  `status` varchar(20) NOT NULL COMMENT '任务状态',
  `priority` varchar(10) NOT NULL COMMENT '优先级',
  `send_email` varchar(50) NOT NULL COMMENT '是否发送通知邮件  1发送0不发送',
  `description` text NOT NULL COMMENT '描述',
  `creator_role_id` int(10) NOT NULL COMMENT '创建者岗位',
  `create_date` int(10) NOT NULL COMMENT '创建时间',
  `update_date` int(10) NOT NULL COMMENT '修改时间',
  `isclose` int(1) NOT NULL COMMENT '是否关闭',
  `is_deleted` int(1) NOT NULL COMMENT '是否删除',
  `delete_role_id` int(10) NOT NULL COMMENT '删除人',
  `delete_time` int(10) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='任务信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_task`
--

LOCK TABLES `new5kcrm_task` WRITE;
/*!40000 ALTER TABLE `new5kcrm_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_user`
--

DROP TABLE IF EXISTS `new5kcrm_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_user` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `role_id` int(10) NOT NULL COMMENT '当前使用岗位',
  `category_id` int(11) NOT NULL COMMENT '用户类别',
  `status` int(1) NOT NULL,
  `name` varchar(20) NOT NULL COMMENT '用户名',
  `img` varchar(100) NOT NULL COMMENT '头像',
  `password` varchar(32) NOT NULL COMMENT '用户密码',
  `salt` varchar(4) NOT NULL COMMENT '安全符',
  `sex` int(1) NOT NULL COMMENT '用户性别1男2女',
  `email` varchar(30) NOT NULL COMMENT '用户邮箱',
  `telephone` varchar(20) NOT NULL COMMENT '用户的电话',
  `address` varchar(100) NOT NULL COMMENT '用户的联系地址',
  `navigation` varchar(1000) NOT NULL COMMENT '用户自定义导航菜单',
  `simple_menu` varchar(1000) NOT NULL COMMENT '自定义快捷添加菜单',
  `dashboard` text NOT NULL COMMENT '个人面板',
  `reg_ip` varchar(15) NOT NULL COMMENT '注册时的ip',
  `reg_time` int(10) NOT NULL COMMENT '用户的注册时间',
  `last_login_time` int(10) NOT NULL COMMENT '用户最后一次登录的时间',
  `lostpw_time` int(10) NOT NULL COMMENT '用户申请找回密码的时间',
  `weixinid` varchar(150) NOT NULL,
  `last_read_time` varchar(500) NOT NULL COMMENT '商机等最后阅读时间',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='本表用来存放用户的相关基本信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_user`
--

LOCK TABLES `new5kcrm_user` WRITE;
/*!40000 ALTER TABLE `new5kcrm_user` DISABLE KEYS */;
INSERT INTO `new5kcrm_user` VALUES (1,1,1,1,'admin','','cbcbfe17be74b3fac5410a76ee1420dd','1af3',0,'','','','','','','192.168.27.17',1441691935,0,0,'','{\"task\":1444961066,\"event\":1444960952,\"contract\":1444811294}'),(2,2,2,1,'周萍','','31a3cb3572e84da5a07ed66c1ef562a3','c481',0,'','','','a:3:{s:3:\"top\";a:6:{i:0;s:1:\"1\";i:1;s:1:\"2\";i:2;s:1:\"4\";i:3;s:1:\"5\";i:4;s:1:\"7\";i:5;s:1:\"8\";}s:4:\"more\";a:6:{i:0;s:1:\"3\";i:1;s:1:\"9\";i:2;s:2:\"10\";i:3;s:2:\"11\";i:4;s:2:\"12\";i:5;s:1:\"6\";}s:4:\"user\";a:7:{i:0;s:2:\"13\";i:1;s:2:\"14\";i:2;s:2:\"15\";i:3;s:2:\"16\";i:4;s:2:\"17\";i:5;s:2:\"18\";i:6;s:2:\"19\";}}','','','140.206.126.42',1442389416,0,0,'','');
/*!40000 ALTER TABLE `new5kcrm_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_user_attribute`
--

DROP TABLE IF EXISTS `new5kcrm_user_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_user_attribute` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '属性id',
  `group_id` int(10) NOT NULL COMMENT '用户的属性组id',
  `name` varchar(50) NOT NULL COMMENT '属性名',
  `description` varchar(100) DEFAULT NULL COMMENT '属性注释',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表用来存放用户的分类属性';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_user_attribute`
--

LOCK TABLES `new5kcrm_user_attribute` WRITE;
/*!40000 ALTER TABLE `new5kcrm_user_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_user_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_user_attribute_group`
--

DROP TABLE IF EXISTS `new5kcrm_user_attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_user_attribute_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '属性组id',
  `name` varchar(20) NOT NULL COMMENT '属性组名',
  `description` varchar(100) DEFAULT NULL COMMENT '属性组描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表用来存放用户属性组信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_user_attribute_group`
--

LOCK TABLES `new5kcrm_user_attribute_group` WRITE;
/*!40000 ALTER TABLE `new5kcrm_user_attribute_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_user_attribute_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_user_attribute_relation`
--

DROP TABLE IF EXISTS `new5kcrm_user_attribute_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_user_attribute_relation` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `attribute_id` int(10) NOT NULL COMMENT '关系id',
  `description` varchar(100) DEFAULT NULL COMMENT '用户属性关系注释',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='本表存放用户和属性对应关系';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_user_attribute_relation`
--

LOCK TABLES `new5kcrm_user_attribute_relation` WRITE;
/*!40000 ALTER TABLE `new5kcrm_user_attribute_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_user_attribute_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_user_category`
--

DROP TABLE IF EXISTS `new5kcrm_user_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_user_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '类别id',
  `name` varchar(20) NOT NULL COMMENT '类别的名字',
  `description` varchar(100) NOT NULL COMMENT '备注',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='本表存放用户类别信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_user_category`
--

LOCK TABLES `new5kcrm_user_category` WRITE;
/*!40000 ALTER TABLE `new5kcrm_user_category` DISABLE KEYS */;
INSERT INTO `new5kcrm_user_category` VALUES (1,'管理员',''),(2,'员工','');
/*!40000 ALTER TABLE `new5kcrm_user_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new5kcrm_user_smtp`
--

DROP TABLE IF EXISTS `new5kcrm_user_smtp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new5kcrm_user_smtp` (
  `smtp_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '发件箱名称',
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `settinginfo` text NOT NULL COMMENT 'smtp设置',
  PRIMARY KEY (`smtp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='smtp设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new5kcrm_user_smtp`
--

LOCK TABLES `new5kcrm_user_smtp` WRITE;
/*!40000 ALTER TABLE `new5kcrm_user_smtp` DISABLE KEYS */;
/*!40000 ALTER TABLE `new5kcrm_user_smtp` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-10-16 14:32:57
