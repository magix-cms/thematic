CREATE TABLE IF NOT EXISTS `mc_thematic` (
 `id_tc` int(7) UNSIGNED NOT NULL AUTO_INCREMENT,
 `id_parent` int(7) UNSIGNED DEFAULT NULL,
 `img_tc` varchar(125) DEFAULT NULL,
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
     `alt_img` varchar(70) DEFAULT NULL,
     `title_img` varchar(70) DEFAULT NULL,
     `caption_img` varchar(125) DEFAULT NULL,
     `seo_title_tc` varchar(180) DEFAULT NULL,
     `seo_desc_tc` text,
     `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `published_tc` smallint(1) NOT NULL DEFAULT '0',
     PRIMARY KEY (`id_content`),
     KEY `id_tc` (`id_tc`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `mc_thematic_content`
    ADD CONSTRAINT `mc_thematic_content_ibfk_1` FOREIGN KEY (`id_tc`) REFERENCES `mc_thematic` (`id_tc`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `mc_thematic_data` (
      `id_data` smallint(2) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_lang` smallint(3) UNSIGNED NOT NULL,
      `name_info` varchar(30) DEFAULT NULL,
      `value_info` text,
      PRIMARY KEY (`id_data`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `mc_config_img` (`id_config_img`, `module_img`, `attribute_img`, `width_img`, `height_img`, `type_img`, `resize_img`) VALUES
(NULL, 'plugins', 'thematic', '256', '256', 'small', 'basic'),
(NULL, 'plugins', 'thematic', '512', '512', 'medium', 'basic'),
(NULL, 'plugins', 'thematic', '1200', '1200', 'large', 'basic');

INSERT INTO `mc_admin_access` (`id_role`, `id_module`, `view`, `append`, `edit`, `del`, `action`)
SELECT 1, m.id_module, 1, 1, 1, 1, 1 FROM mc_module as m WHERE name = 'thematic';