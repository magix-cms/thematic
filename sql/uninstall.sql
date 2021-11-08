TRUNCATE TABLE `mc_thematic_content`;
DROP TABLE `mc_thematic_content`;
TRUNCATE TABLE `mc_thematic_data`;
DROP TABLE `mc_thematic_data`;
TRUNCATE TABLE `mc_thematic`;
DROP TABLE `mc_thematic`;

DELETE FROM `mc_config_img` WHERE `mc_config_img`.`attribute_img` = 'thematic';

DELETE FROM `mc_admin_access` WHERE `id_module` IN (
    SELECT `id_module` FROM `mc_module` as m WHERE m.name = 'thematic'
);