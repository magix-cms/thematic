CREATE TABLE IF NOT EXISTS `mc_thematic` (
 `id_tc` int(7) UNSIGNED NOT NULL AUTO_INCREMENT,
 `id_parent` int(7) UNSIGNED DEFAULT NULL,
 `menu_tc` smallint(1) UNSIGNED DEFAULT '1',
 `order_tc` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
 `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id_tc`),
 KEY `id_parent` (`id_parent`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mc_thematic_content` (
     `id_content` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
     `id_tc` int(7) UNSIGNED NOT NULL,
     `id_lang` smallint(3) UNSIGNED NOT NULL DEFAULT '1',
     `name_tc` varchar(150) DEFAULT NULL,
     `title_tc` varchar(150) DEFAULT NULL,
     `url_tc` varchar(150) DEFAULT NULL,
     `resume_tc` text,
     `content_tc` text,
     `seo_title_tc` varchar(180) DEFAULT NULL,
     `seo_desc_tc` text,
     `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `published_tc` smallint(1) NOT NULL DEFAULT '0',
     PRIMARY KEY (`id_content`),
     KEY `id_tc` (`id_tc`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mc_thematic_img` (
    `id_img` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_tc` int(7) UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint(1) NOT NULL DEFAULT 0,
    `order_img` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_img`),
    KEY `id_tc` (`id_tc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `mc_thematic_content`
    ADD CONSTRAINT `mc_thematic_content_ibfk_1` FOREIGN KEY (`id_tc`) REFERENCES `mc_thematic` (`id_tc`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_thematic_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_thematic_img`
    ADD CONSTRAINT `mc_thematic_img_img_ibfk_1` FOREIGN KEY (`id_tc`) REFERENCES `mc_thematic` (`id_tc`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `mc_thematic_data` (
      `id_data` smallint(2) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_lang` smallint(3) UNSIGNED NOT NULL,
      `name_info` varchar(30) DEFAULT NULL,
      `value_info` text,
      PRIMARY KEY (`id_data`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `mc_config_img` (`id_config_img`, `module_img`, `attribute_img`, `width_img`, `height_img`, `type_img`, `prefix_img`, `resize_img`) VALUES
(NULL, 'thematic', 'thematic', '340', '210', 'small', 's', 'adaptive'),
(NULL, 'thematic', 'thematic', '680', '420', 'medium', 'm', 'adaptive'),
(NULL, 'thematic', 'thematic', '1200', '1200', 'large', 'l', 'basic');