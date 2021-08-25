<?php
class plugins_thematic_dbfront
{
    /**
     * @param $config
     * @param bool $params
     * @return mixed|null
     * @throws Exception
     */
    public function fetchData($config, $params = false)
    {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';
        $dateFormat = new component_format_date();
        if(is_array($config)) {
            if ($config['context'] === 'all') {
                switch ($config['type']) {
                    case 'root':
                        $sql = 'SELECT d.name_info, d.value_info 
                                FROM mc_thematic_data AS d
                                JOIN mc_lang AS lang ON(d.id_lang = lang.id_lang)
                                WHERE lang.iso_lang = :iso';
                        break;
                    case 'langs':
                        $sql = 'SELECT
									h.*,
									c.name_tc,
									c.url_tc,
									c.resume_tc,
									c.content_tc,
									c.published_tc,
									COALESCE(c.alt_img, c.name_tc) as alt_img,
									COALESCE(c.title_img, c.alt_img, c.name_tc) as title_img,
									COALESCE(c.caption_img, c.title_img, c.alt_img, c.name_tc) as caption_img,
       								COALESCE(c.seo_title_tc, c.name_tc) as seo_title_tc,
       								COALESCE(c.seo_desc_tc, c.resume_tc) as seo_desc_tc,
       								li.title_tc,
       								li.active_tc,
       								lang.id_lang,
									lang.iso_lang
								FROM mc_thematic AS h
								JOIN mc_thematic_content AS c ON(h.id_tc = c.id_tc) 
								left join mc_thematic_info AS li ON(h.id_tc = li.id_tc)
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
								WHERE h.id_tc = :id AND c.published_tc = 1';
                        break;
                    case 'pages':
                        $config["conditions"] ? $conditions = $config["conditions"] : $conditions = '';
                        $sql = "SELECT
									p.*,
									c.name_tc,
									c.title_tc,
								   	c.url_tc,
								   	c.resume_tc,
								   	c.content_tc,
								   	c.published_tc,
								   	c.last_update,
       								COALESCE(c.alt_img, c.name_tc) as alt_img,
									COALESCE(c.title_img, c.alt_img, c.name_tc) as title_img,
									COALESCE(c.caption_img, c.title_img, c.alt_img, c.name_tc) as caption_img,
       								COALESCE(c.seo_title_tc, c.name_tc) as seo_title_tc,
       								COALESCE(c.seo_desc_tc, c.resume_tc) as seo_desc_tc,
       								lang.id_lang,
									lang.iso_lang,
									lang.default_lang
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc) 
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                    			$conditions";
                        break;
                    case 'pages_short':
                        $conditions = isset($config["conditions"]) ? $config["conditions"] : '';
                        $sql = "SELECT
									p.id_tc,
									c.name_tc,
								   	c.url_tc,
       								COALESCE(c.seo_title_tc, c.name_tc) as seo_title_tc,
									lang.iso_lang
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc)
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                    			WHERE lang.iso_lang = :iso AND p.id_tc IN (".(is_array($params['id']) ? implode(',',$params['id']) : $params['id']).")
                    			 ORDER BY FIELD(p.id_tc,".(is_array($params['id']) ? implode(',',$params['id']) : $params['id']).')';
                        unset($params['id']);
                        break;
                    case 'child':
                        $config["conditions"] ? $conditions = $config["conditions"] : $conditions = '';
                        $sql = "SELECT 
									p.id_tc,
									p.id_parent,
									p.img_tc,
									p.menu_tc, 
									p.date_register, 
									c.name_tc,
									c.url_tc,
									c.resume_tc,
									c.content_tc,
									c.published_tc,
									COALESCE(c.alt_img, c.name_tc) as alt_img,
									COALESCE(c.title_img, c.alt_img, c.name_tc) as title_img,
									COALESCE(c.caption_img, c.title_img, c.alt_img, c.name_tc) as caption_img,
       								COALESCE(c.seo_title_tc, c.name_tc) as seo_title_tc,
       								COALESCE(c.seo_desc_tc, c.resume_tc) as seo_desc_tc,
       								li.title_tc,
       								li.active_tc,
									ls.active_season,
									ls.id_season,
									ls.season_end,
									ls.season_start,
									ls.season_year,
       								ls.league_id,
       								ls.ranking,
									lang.iso_lang
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c USING ( id_tc )
								JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
								LEFT JOIN mc_thematic AS pa ON ( p.id_parent = pa.id_tc )
								left join mc_thematic_info AS li ON(p.id_tc = li.id_tc)
								LEFT JOIN mc_thematic_content AS ca ON ( pa.id_tc = ca.id_tc ) 
								left join mc_thematic_seasons AS ls ON(p.id_tc = ls.id_tc)
								$conditions";
                        break;
                    case 'parents':
                        $sql = "SELECT t.id_tc AS parent, GROUP_CONCAT(f.id_tc) AS children
								FROM mc_thematic t
								JOIN mc_thematic f ON t.id_tc=f.id_parent
								GROUP BY t.id_tc";
                        break;
					case 'thematics':
						$sql = "SELECT
									c.id_tc,
       								c.name_tc
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc) 
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                    			WHERE iso_lang = :iso AND c.published_tc = 1";
						break;
					case 'mainThematics':
						$sql = "SELECT
									c.id_tc,
									p.id_parent,
       								c.name_tc
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc) 
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                    			WHERE iso_lang = :iso AND c.published_tc = 1
                    			ORDER BY c.name_tc";
						break;
					case 'childThematics':
						$sql = "SELECT
									c.id_tc,
       								c.name_tc
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc) 
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                    			WHERE iso_lang = :iso AND p.id_parent = :id AND c.published_tc = 1
                    			ORDER BY c.name_tc";
						break;
                }

                return $sql ? component_routing_db::layer()->fetchAll($sql, $params, array('debugParams'=>false)) : null;
            }
            elseif ($config['context'] === 'one') {
                switch ($config['type']) {
                    case 'thematic':
                        $sql = 'SELECT p.*,
									c.name_tc,
									c.title_tc,
								   	c.url_tc,
								   	c.resume_tc,
								   	c.content_tc,
								   	c.published_tc,
								   	c.last_update,
       								COALESCE(c.alt_img, c.name_tc) as alt_img,
									COALESCE(c.title_img, c.alt_img, c.name_tc) as title_img,
									COALESCE(c.caption_img, c.title_img, c.alt_img, c.name_tc) as caption_img,
       								c.seo_title_tc,
       								c.seo_desc_tc,
       								lang.id_lang,
									lang.iso_lang,
									lang.default_lang
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc) 
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                                WHERE p.id_tc = :id AND lang.iso_lang = :iso AND c.published_tc = 1';
                        break;
                        //COALESCE(c.seo_title_tc, c.name_tc) as seo_title_tc,
                    //       								COALESCE(c.seo_desc_tc, c.resume_tc) as seo_desc_tc,
                    case 'thematic_short':
                        $sql = 'SELECT
       								c.id_tc,
									c.name_tc,
									c.title_tc,
								   	c.url_tc
								FROM mc_thematic AS p
								JOIN mc_thematic_content AS c ON(p.id_tc = c.id_tc) 
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang) 
                                WHERE p.id_tc = :id AND lang.iso_lang = :iso AND c.published_tc = 1';
                        break;
                    case 'root':
                        $sql = 'SELECT * FROM mc_thematic ORDER BY id_tc DESC LIMIT 0,1';
                        break;
                    case 'wsEdit':
                        $sql = 'SELECT * FROM mc_thematic WHERE `id_tc` = :id';
                        break;
                    case 'image':
                        $sql = 'SELECT img_tc FROM mc_thematic WHERE `id_tc` = :id_tc';
                        break;
                    case 'content':
                        $sql = 'SELECT * FROM `mc_thematic_content` WHERE `id_tc` = :id_tc AND `id_lang` = :id_lang';
                        break;
                    case 'pageLang':
                        $sql = 'SELECT p.*,c.*,lang.*
							FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING(id_tc)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE p.id_tc = :id
							AND lang.iso_lang = :iso';
                        break;
                }

                return $sql ? component_routing_db::layer()->fetch($sql, $params) : null;
            }
        }
    }


	/**
	 * @param $config
	 * @param array $params
	 * @return bool|string
	 */
	public function insert($config,$params = array())
	{
		if (!is_array($config)) return '$config must be an array';

		$sql = '';

		switch ($config['type']) {
			case 'phase':
				$sql = 'INSERT INTO `mc_thematic_phase`(id_tc, phase_rounds, phase_name, phase_start, phase_end, phase_games, phase_group) 
				  		VALUES (:id_tc, :phase_rounds, :phase_name, :phase_start, :phase_end, :phase_games, :phase_group)';
				break;
		}

		if($sql === '') return 'Unknown request asked';

		try {
			component_routing_db::layer()->insert($sql,$params);
			return true;
		}
		catch (Exception $e) {
			return 'Exception reçue : '.$e->getMessage();
		}
	}

	/**
	 * @param $config
	 * @param array $params
	 * @return bool|string
	 */
	public function update($config,$params = array())
	{
		if (!is_array($config)) return '$config must be an array';

		$sql = '';

		switch ($config['type']) {
			case 'page':
				$sql = 'UPDATE mc_thematic 
							SET 
								id_parent = :id_parent,
							    menu_tc = :menu_tc
							WHERE id_tc = :id_tc';
				break;
		}

		if($sql === '') return 'Unknown request asked';

		try {
			component_routing_db::layer()->update($sql,$params);
			return true;
		}
		catch (Exception $e) {
			return 'Exception reçue : '.$e->getMessage();
		}
	}
}