/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50554
Source Host           : localhost:3306
Source Database       : otochat

Target Server Type    : MYSQL
Target Server Version : 50554
File Encoding         : 65001

Date: 2018-12-29 11:45:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `chat`
-- ----------------------------
DROP TABLE IF EXISTS `chat`;
CREATE TABLE `chat` (
  `chat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '聊天消息',
  `client_id` char(20) DEFAULT NULL COMMENT '发送客户端id',
  `client_name` char(50) DEFAULT NULL COMMENT '发送客户端名称',
  `to_client_id` char(20) DEFAULT NULL COMMENT '接收客户端id',
  `to_client_name` char(50) DEFAULT NULL COMMENT '接收客户端名称',
  `content` varchar(500) DEFAULT NULL COMMENT '聊天内容',
  `chat_time` datetime DEFAULT NULL COMMENT '发送时间',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读0未读1已读',
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat
-- ----------------------------

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员id',
  `user_name` varchar(50) DEFAULT NULL COMMENT '会员名称',
  `user_password` varchar(50) DEFAULT NULL COMMENT '会员密码',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin', '123456');
INSERT INTO `user` VALUES ('3', '暗语寒飞', '123456');
INSERT INTO `user` VALUES ('4', 'qq', '123456');
INSERT INTO `user` VALUES ('5', 'google', '123456');
INSERT INTO `user` VALUES ('6', 'firefox', '123456');
INSERT INTO `user` VALUES ('7', 'firegoxx', '123456');
INSERT INTO `user` VALUES ('8', 'xu', '123456');
INSERT INTO `user` VALUES ('9', 'xv', '123456');
INSERT INTO `user` VALUES ('10', '齐秦', '5150317');

-- ----------------------------
-- Table structure for `user_chat`
-- ----------------------------
DROP TABLE IF EXISTS `user_chat`;
CREATE TABLE `user_chat` (
  `user_chat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '聊天记录',
  `send_user_id` int(11) DEFAULT NULL COMMENT '发送客户端id',
  `receive_user_id` int(11) DEFAULT NULL COMMENT '接收客户端id',
  `content` varchar(500) DEFAULT NULL COMMENT '内容',
  `chat_time` datetime DEFAULT NULL COMMENT '发送时间',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  PRIMARY KEY (`user_chat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_chat
-- ----------------------------
INSERT INTO `user_chat` VALUES ('1', '4', '5', '123', '2018-11-20 16:42:53', '0');
INSERT INTO `user_chat` VALUES ('2', '5', '4', '123', '2018-11-20 16:43:03', '0');
INSERT INTO `user_chat` VALUES ('3', '5', '4', 'qwe', '2018-11-20 16:43:19', '0');
INSERT INTO `user_chat` VALUES ('4', '4', '5', 'qwe', '2018-11-20 16:43:21', '0');
INSERT INTO `user_chat` VALUES ('5', '4', '5', 'qwe', '2018-11-20 16:43:39', '0');
INSERT INTO `user_chat` VALUES ('6', '5', '4', '132', '2018-11-20 16:45:17', '0');
INSERT INTO `user_chat` VALUES ('7', '4', '5', '2534', '2018-11-20 16:45:25', '0');
INSERT INTO `user_chat` VALUES ('8', '4', '5', 'wer', '2018-11-20 16:46:35', '0');
INSERT INTO `user_chat` VALUES ('9', '4', '5', 'qwr', '2018-11-20 16:47:12', '0');
INSERT INTO `user_chat` VALUES ('10', '5', '4', 'qwe', '2018-11-20 16:48:58', '0');
INSERT INTO `user_chat` VALUES ('11', '4', '5', 'sdf', '2018-11-20 16:51:34', '0');
INSERT INTO `user_chat` VALUES ('12', '5', '4', 'rety', '2018-11-20 16:51:41', '0');
INSERT INTO `user_chat` VALUES ('13', '5', '4', '456', '2018-11-20 16:52:03', '0');
INSERT INTO `user_chat` VALUES ('14', '4', '5', '123', '2018-11-20 16:58:47', '0');
INSERT INTO `user_chat` VALUES ('15', '5', '4', 'qweqwe', '2018-11-20 16:58:54', '0');
INSERT INTO `user_chat` VALUES ('16', '6', '4', '123', '2018-11-20 16:59:44', '0');
INSERT INTO `user_chat` VALUES ('17', '4', '6', '123', '2018-11-20 16:59:52', '0');
INSERT INTO `user_chat` VALUES ('18', '6', '4', '123123', '2018-11-20 17:00:00', '0');
INSERT INTO `user_chat` VALUES ('19', '6', '5', '123', '2018-11-20 17:01:21', '0');
INSERT INTO `user_chat` VALUES ('20', '5', '6', '345435', '2018-11-20 17:01:26', '0');
INSERT INTO `user_chat` VALUES ('21', '5', '6', '123123', '2018-11-20 17:01:35', '0');
INSERT INTO `user_chat` VALUES ('22', '6', '5', '4234234', '2018-11-20 17:01:37', '0');
INSERT INTO `user_chat` VALUES ('23', '4', '6', '12312312', '2018-11-20 17:01:40', '0');
INSERT INTO `user_chat` VALUES ('24', '6', '4', '234234', '2018-11-20 17:01:44', '0');
INSERT INTO `user_chat` VALUES ('25', '6', '5', 'adsad', '2018-11-20 17:02:33', '0');
INSERT INTO `user_chat` VALUES ('26', '5', '6', 'sadfasfd', '2018-11-20 17:02:41', '0');
INSERT INTO `user_chat` VALUES ('27', '5', '4', 'sadg', '2018-11-20 17:02:45', '0');
INSERT INTO `user_chat` VALUES ('28', '6', '4', 'sdfasdasd', '2018-11-20 17:03:03', '0');
INSERT INTO `user_chat` VALUES ('29', '4', '6', 'qwerwerwe', '2018-11-20 17:03:10', '0');
INSERT INTO `user_chat` VALUES ('30', '4', '5', 'asdfsaf', '2018-11-20 17:03:15', '0');
INSERT INTO `user_chat` VALUES ('31', '6', '4', '123', '2018-11-20 17:09:45', '0');
INSERT INTO `user_chat` VALUES ('32', '4', '1', '123', '2018-11-21 14:50:35', '0');
INSERT INTO `user_chat` VALUES ('33', '4', '1', '测试', '2018-11-23 10:20:54', '0');
INSERT INTO `user_chat` VALUES ('34', '10', '9', '123\n', '2018-11-30 11:15:28', '0');
INSERT INTO `user_chat` VALUES ('35', '9', '10', 'sdf', '2018-11-30 11:15:54', '0');
INSERT INTO `user_chat` VALUES ('36', '10', '9', '234', '2018-11-30 11:16:13', '0');

-- ----------------------------
-- Table structure for `user_of_client`
-- ----------------------------
DROP TABLE IF EXISTS `user_of_client`;
CREATE TABLE `user_of_client` (
  `user_of_client_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员表与client_id的关联表',
  `user_id` int(11) DEFAULT NULL COMMENT '会员id',
  `client_id` varchar(20) DEFAULT NULL COMMENT 'client_id',
  PRIMARY KEY (`user_of_client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_of_client
-- ----------------------------
