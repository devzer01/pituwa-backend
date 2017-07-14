<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

	try {

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS users (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  gcm_regid TEXT,
								  ios_fcm_regid TEXT,
								  admob INT(10) UNSIGNED DEFAULT 1,
								  gcm INT(10) UNSIGNED DEFAULT 1,
								  state INT(10) UNSIGNED DEFAULT 0,
								  surname VARCHAR(75) NOT NULL DEFAULT '',
								  fullname VARCHAR(150) NOT NULL DEFAULT '',
								  salt CHAR(3) NOT NULL DEFAULT '',
								  passw VARCHAR(32) NOT NULL DEFAULT '',
								  login VARCHAR(50) NOT NULL DEFAULT '',
								  email VARCHAR(64) NOT NULL DEFAULT '',
								  lang CHAR(10) DEFAULT 'en',
								  language CHAR(10) DEFAULT 'en',
								  bYear SMALLINT(6) UNSIGNED DEFAULT 2000,
								  bMonth SMALLINT(6) UNSIGNED DEFAULT 0,
								  bDay SMALLINT(6) UNSIGNED DEFAULT 1,
								  status VARCHAR(500) NOT NULL DEFAULT '',
								  country VARCHAR(30) NOT NULL DEFAULT '',
								  country_id INT(10) UNSIGNED DEFAULT 0,
								  city VARCHAR(50) NOT NULL DEFAULT '',
								  city_id INT(10) UNSIGNED DEFAULT 0,
								  lat float(10,6) DEFAULT 0,
								  lng float(10,6) DEFAULT 0,
								  vk_page VARCHAR(150) NOT NULL DEFAULT '',
								  fb_page VARCHAR(150) NOT NULL DEFAULT '',
								  tw_page VARCHAR(150) NOT NULL DEFAULT '',
								  my_page VARCHAR(150) NOT NULL DEFAULT '',
								  phone VARCHAR(30) NOT NULL DEFAULT '',
								  verify SMALLINT(6) UNSIGNED DEFAULT 0,
								  removed SMALLINT(6) UNSIGNED DEFAULT 0,
								  vk_id varchar(40) DEFAULT 0,
								  fb_id varchar(40)	DEFAULT 0,
								  gl_id varchar(40) DEFAULT 0,
								  tw_id varchar(40) DEFAULT 0,
								  regtime INT(10) UNSIGNED DEFAULT 0,
								  lasttime INT(10) UNSIGNED DEFAULT 0,
								  items_count INT(11) UNSIGNED DEFAULT 0,
								  likes_count INT(11) UNSIGNED DEFAULT 0,
								  comments_count INT(11) UNSIGNED DEFAULT 0,
								  rating INT(11) UNSIGNED DEFAULT 0,
								  balance INT(11) UNSIGNED DEFAULT 10,
								  sex SMALLINT(6) UNSIGNED DEFAULT 0,
								  last_notify_view INT(10) UNSIGNED DEFAULT 0,
								  last_feed_view INT(10) UNSIGNED DEFAULT 0,
								  last_guests_view INT(10) UNSIGNED DEFAULT 0,
								  last_authorize INT(10) UNSIGNED DEFAULT 0,
								  ip_addr CHAR(32) NOT NULL DEFAULT '',
								  allowShowOnline SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowCommentReplyGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  lowPhotoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  originPhotoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  normalPhotoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  bigPhotoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  originCoverUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  normalCoverUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  coverPosition VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '0px 0px',
  								PRIMARY KEY  (id), UNIQUE KEY (login)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS gcm_reg_ids (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  gcm_regid TEXT,
								  createAt int(11) UNSIGNED DEFAULT 0,
								  u_agent varchar(300) DEFAULT '',
								  ip_addr CHAR(32) NOT NULL DEFAULT '',
  								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS settings (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  name VARCHAR(150) NOT NULL DEFAULT '',
								  intValue INT(10) UNSIGNED DEFAULT 0,
								  textValue CHAR(32) NOT NULL DEFAULT '',
  								PRIMARY KEY  (id), UNIQUE KEY (name)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS admins (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								username VARCHAR(50) NOT NULL DEFAULT '',
                                salt CHAR(3) NOT NULL DEFAULT '',
                                password VARCHAR(32) NOT NULL DEFAULT '',
                                fullname VARCHAR(150) NOT NULL DEFAULT '',
                                email VARCHAR(64) NOT NULL DEFAULT '',
                                createAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS categories (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								itemsCount int(11) UNSIGNED DEFAULT 0,
                                title varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                description varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                imgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                createAt int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS items (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								allowComments int(11) UNSIGNED DEFAULT 1,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								category int(11) UNSIGNED DEFAULT 0,
								categoryTitle varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								likesCount int(11) UNSIGNED DEFAULT 0,
								commentsCount int(11) UNSIGNED DEFAULT 0,
								rating int(11) UNSIGNED DEFAULT 0,
								itemTitle varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								itemDesc varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								itemContent TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								imgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewGifUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								gifUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewVideoImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								videoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								area VARCHAR(150) NOT NULL DEFAULT '',
								country VARCHAR(150) NOT NULL DEFAULT '',
								city VARCHAR(150) NOT NULL DEFAULT '',
								lat float(10,6),
								lng float(10,6),
								moderatedId int(11) UNSIGNED DEFAULT 0,
								moderatedAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								createAt int(11) UNSIGNED DEFAULT 0,
								modifyAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS comments (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								replyToUserId int(11) UNSIGNED DEFAULT 0,
								likesCount int(11) UNSIGNED DEFAULT 0,
								itemId int(11) UNSIGNED DEFAULT 0,
                                comment varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                commentOriginImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                commentNormalImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                createAt int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
                                notifyId int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS support (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								clientId int(11) UNSIGNED DEFAULT 0,
                                accountId int(11) UNSIGNED DEFAULT 0,
                                email varchar(64) DEFAULT '',
                                subject varchar(180) DEFAULT '',
                                text varchar(400) DEFAULT '',
                                reply varchar(400) DEFAULT '',
                                replyAt int(11) UNSIGNED DEFAULT 0,
                                replyFrom int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
                                createAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS likes (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                toUserId int(11) UNSIGNED DEFAULT 0,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								itemId int(11) UNSIGNED DEFAULT 0,
                                notifyId int(11) UNSIGNED DEFAULT 0,
								createAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS comments_likes (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                toUserId int(11) UNSIGNED DEFAULT 0,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								commentId int(11) UNSIGNED DEFAULT 0,
                                notifyId int(11) UNSIGNED DEFAULT 0,
								createAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS notifications (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								notifyToId int(11) UNSIGNED NOT NULL DEFAULT 0,
								notifyFromId int(11) UNSIGNED NOT NULL DEFAULT 0,
								notifyType int(11) UNSIGNED NOT NULL DEFAULT 0,
								itemId int(11) UNSIGNED NOT NULL DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS access_data (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								accountId int(11) UNSIGNED NOT NULL,
								accessToken varchar(32) DEFAULT '',
								clientId int(11) UNSIGNED DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS restore_data (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								accountId int(11) UNSIGNED NOT NULL,
								hash varchar(32) DEFAULT '',
								email VARCHAR(64) NOT NULL DEFAULT '',
								clientId int(11) UNSIGNED DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS profile_abuse_reports (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								abuseFromUserId INT(11) UNSIGNED DEFAULT 0,
								abuseToUserId INT(11) UNSIGNED DEFAULT 0,
								abuseId INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS items_abuse_reports (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								abuseFromUserId INT(11) UNSIGNED DEFAULT 0,
								abuseToItemId INT(11) UNSIGNED DEFAULT 0,
								abuseId INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS comments_abuse_reports (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								abuseFromUserId INT(11) UNSIGNED DEFAULT 0,
								abuseToCommentId INT(11) UNSIGNED DEFAULT 0,
								abuseId INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();


		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS gcm_history (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  msg VARCHAR(150) NOT NULL DEFAULT '',
								  msgType INT(10) UNSIGNED DEFAULT 0,
								  accountId int(11) UNSIGNED DEFAULT 0,
								  status INT(10) UNSIGNED DEFAULT 0,
								  success INT(10) UNSIGNED DEFAULT 0,
								  createAt int(10) UNSIGNED DEFAULT 0,
  								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

	} catch (Exception $e) {

		die ($e->getMessage());
	}
