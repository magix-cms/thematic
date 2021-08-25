<?php
class plugins_thematic_dbadmin
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

		if ($config['context'] === 'all') {
			switch ($config['type']) {
				case 'pages':
					$limit = '';
					if($config['offset']) {
						$limit = ' LIMIT 0, '.$config['offset'];
						if(isset($config['page']) && $config['page'] > 1) {
							$limit = ' LIMIT '.(($config['page'] - 1) * $config['offset']).', '.$config['offset'];
						}
					}

					$sql = "SELECT p.id_tc, c.name_tc, p.img_tc, c.content_tc, c.seo_title_tc, c.seo_desc_tc, p.menu_tc, p.date_register
						FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING ( id_tc )
							JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
							WHERE c.id_lang = :default_lang AND p.id_parent IS NULL 
							GROUP BY p.id_tc 
						ORDER BY p.order_tc".$limit;

					if(isset($config['search'])) {
						$cond = '';
						if(is_array($config['search']) && !empty($config['search'])) {
							$nbc = 1;
							foreach ($config['search'] as $key => $q) {
								if($q !== '') {
									$cond .= 'AND ';
									$p = 'p'.$nbc;
									switch ($key) {
										case 'id_tc':
										case 'menu_tc':
											$cond .= 'p.'.$key.' = :'.$p.' ';
											break;
										case 'published_tc':
											$cond .= 'c.'.$key.' = :'.$p.' ';
											break;
										case 'name_tc':
											$cond .= "c.".$key." LIKE CONCAT('%', :".$p.", '%') ";
											break;
										case 'parent_tc':
											$cond .= "ca.name_tc"." LIKE CONCAT('%', :".$p.", '%') ";
											break;
										case 'date_register':
											$q = $dateFormat->date_to_db_format($q);
											$cond .= "p.".$key." LIKE CONCAT('%', :".$p.", '%') ";
											break;
									}
									$params[$p] = $q;
									$nbc++;
								}
							}

							$sql = "SELECT p.id_tc, c.name_tc, p.img_tc, c.content_tc, c.seo_title_tc, c.seo_desc_tc, p.menu_tc, p.date_register, ca.name_tc AS parent_tc
								FROM mc_thematic AS p
									JOIN mc_thematic_content AS c USING ( id_tc )
									JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
									LEFT JOIN mc_thematic AS pa ON ( p.id_parent = pa.id_tc )
									LEFT JOIN mc_thematic_content AS ca ON ( pa.id_tc = ca.id_tc ) 
									WHERE c.id_lang = :default_lang $cond
									GROUP BY p.id_tc 
								ORDER BY p.order_tc".$limit;
						}
					}
					break;
				case 'pagesChild':
					$cond = '';
					if(isset($config['search']) && is_array($config['search']) && !empty($config['search'])) {
						$nbc = 0;
						foreach ($config['search'] as $key => $q) {
							if($q !== '') {
								$cond .= 'AND ';
								$p = 'p'.$nbc;
								switch ($key) {
									case 'id_tc':
										$cond .= 'c.'.$key.' = '.$p.' ';
										break;
									case 'name_tc':
										$cond .= "c.".$key." LIKE CONCAT('%', :".$p.", '%') ";
										break;
									case 'menu_tc':
										$cond .= 'p.'.$key.' = '.$p.' ';
										break;
									case 'date_register':
										$q = $dateFormat->date_to_db_format($q);
										$cond .= "p.".$key." LIKE CONCAT('%', :".$p.", '%') ";
										//$params[$key] = $q;
										break;
								}
								$params[$p] = $q;
								$nbc++;
							}
						}
					}

					$sql = "SELECT p.id_tc, c.name_tc, c.content_tc, c.seo_title_tc, c.seo_desc_tc, p.menu_tc, p.date_register
							FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING ( id_tc )
							JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
							LEFT JOIN mc_thematic AS pa ON ( p.id_parent = pa.id_tc )
							LEFT JOIN mc_thematic_content AS ca ON ( pa.id_tc = ca.id_tc ) 
							WHERE p.id_parent = :id $cond
							GROUP BY p.id_tc 
							ORDER BY p.order_tc";
					break;
				case 'pagesSelect':
					$sql = "SELECT p.id_parent,p.id_tc, c.name_tc , ca.name_tc AS parent_tc
							FROM mc_thematic AS p
								JOIN mc_thematic_content AS c USING ( id_tc )
								JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
								LEFT JOIN mc_thematic AS pa ON ( p.id_parent = pa.id_tc )
								LEFT JOIN mc_thematic_content AS ca ON ( pa.id_tc = ca.id_tc ) 
								WHERE c.id_lang = :default_lang
								GROUP BY p.id_tc 
							ORDER BY p.id_tc DESC";
					break;
				case 'pagesPublishedSelect':
					$sql = "SELECT p.id_parent,p.id_tc, c.name_tc , ca.name_tc AS parent_tc
							FROM mc_thematic AS p
								JOIN mc_thematic_content AS c USING ( id_tc )
								JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
								LEFT JOIN mc_thematic AS pa ON ( p.id_parent = pa.id_tc )
								LEFT JOIN mc_thematic_content AS ca ON ( pa.id_tc = ca.id_tc ) 
								WHERE c.id_lang = :default_lang
								AND c.published_tc = 1
								GROUP BY p.id_tc 
							ORDER BY p.id_tc DESC";
					break;
				case 'page':
					$sql = 'SELECT p.*,c.*,lang.*
							FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING(id_tc)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE p.id_tc = :edit';
					break;
				case 'img':
					$sql = 'SELECT p.id_tc, p.img_tc FROM mc_thematic AS p WHERE p.img_tc IS NOT NULL';
					break;
				case 'sitemap':
					$sql = 'SELECT p.id_tc, p.img_tc, c.name_tc, c.url_tc, lang.iso_lang, c.id_lang, c.last_update
							FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING ( id_tc )
							JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
							WHERE c.published_tc = 1 AND c.id_lang = :id_lang
							ORDER BY p.id_tc ASC';
					break;
				case 'lastPages':
					$sql = "SELECT p.id_tc, c.name_tc, p.date_register
							FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING ( id_tc )
							JOIN mc_lang AS lang ON ( c.id_lang = lang.id_lang )
							WHERE c.id_lang = :default_lang
							GROUP BY p.id_tc 
							ORDER BY p.id_tc DESC
							LIMIT 5";
					break;
                case 'rootContent':
                    $sql = 'SELECT a.*
							FROM mc_thematic_data AS a
							JOIN mc_lang AS lang ON(a.id_lang = lang.id_lang)';
                    break;
			}

			return $sql ? component_routing_db::layer()->fetchAll($sql, $params) : null;
		}
		elseif ($config['context'] === 'one') {
			switch ($config['type']) {
				case 'root':
					$sql = 'SELECT * FROM mc_thematic ORDER BY id_tc DESC LIMIT 0,1';
					break;
				case 'content':
					$sql = 'SELECT * FROM `mc_thematic_content` WHERE `id_tc` = :id_tc AND `id_lang` = :id_lang';
					break;
				case 'page':
					$sql = 'SELECT * FROM mc_thematic WHERE `id_tc` = :id_tc';
					break;
				case 'pageLang':
					$sql = 'SELECT p.*,c.*,lang.*
							FROM mc_thematic AS p
							JOIN mc_thematic_content AS c USING(id_tc)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE p.id_tc = :id
							AND lang.iso_lang = :iso';
					break;
                case 'rootContent':
                    $sql = 'SELECT * FROM `mc_thematic_data` WHERE `id_lang` = :id_lang';
                    break;
			}

			return $sql ? component_routing_db::layer()->fetch($sql, $params) : null;
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
			case 'page':
				$cond = $params['id_parent'] != NULL ? ' IN ('.$params['id_parent'].')' : ' IS NULL';
				$sql = "INSERT INTO `mc_thematic`(id_parent,menu_tc,order_tc,date_register) 
						SELECT :id_parent,:menu_tc,COUNT(id_tc),NOW() FROM mc_thematic WHERE id_parent".$cond;
				break;
			case 'content':
				$sql = 'INSERT INTO `mc_thematic_content`(id_tc,id_lang,name_tc,title_tc,url_tc,resume_tc,content_tc,seo_title_tc,seo_desc_tc,published_tc) 
				  		VALUES (:id_tc,:id_lang,:name_tc,:title_tc,:url_tc,:resume_tc,:content_tc,:seo_title_tc,:seo_desc_tc,:published_tc)';
				break;
            case 'root':
                $queries = array(
                    array(
                        'request' => "SET @lang = :id_lang",
                        'params' => array('id_lang' => $params['id_lang'])
                    ),
                    array(
                        'request' => "INSERT INTO `mc_thematic_data` (`id_lang`,`name_info`,`value_info`) VALUES
							(@lang,'name',:nm),(@lang,'content',:content),(@lang,'seo_desc',:seo_desc),(@lang,'seo_title',:seo_title)",
                        'params' => array(
                            'nm'        => $params['name'],
                            'content'   => $params['content'],
                            'seo_desc'  => $params['seo_desc'],
                            'seo_title' => $params['seo_title']
                        )
                    ),
                );

                try {
                    component_routing_db::layer()->transaction($queries);
                    return true;
                }
                catch (Exception $e) {
                    return 'Exception reçue : '.$e->getMessage();
                }
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
			case 'content':
				$sql = 'UPDATE mc_thematic_content 
						SET 
							name_tc = :name_tc,
							title_tc = :title_tc,
							url_tc = :url_tc,
							resume_tc = :resume_tc,
							content_tc=:content_tc,
							seo_title_tc=:seo_title_tc,
							seo_desc_tc=:seo_desc_tc, 
							published_tc=:published_tc
                		WHERE id_tc = :id_tc 
                		AND id_lang = :id_lang';
				break;
            case 'root':
                $sql = "UPDATE `mc_thematic_data`
                        SET `value_info` = CASE `name_info`
                            WHEN 'name' THEN :nm
                            WHEN 'content' THEN :content
                            WHEN 'seo_desc' THEN :seo_desc
						    WHEN 'seo_title' THEN :seo_title
                        END
                        WHERE `name_info` IN ('name','content','seo_desc','seo_title') AND id_lang = :id_lang";
                break;
			case 'img':
				$sql = 'UPDATE mc_thematic 
						SET img_tc = :img_tc
                		WHERE id_tc = :id_tc';
				break;
			case 'imgContent':
				$sql = 'UPDATE mc_thematic_content 
						SET 
							alt_img = :alt_img,
							title_img = :title_img,
							caption_img = :caption_img
                		WHERE id_tc = :id_tc 
                		AND id_lang = :id_lang';
				break;
			case 'pageActiveMenu':
				$sql = 'UPDATE mc_thematic 
						SET menu_tc = :menu_tc 
						WHERE id_tc IN ('.$params['id_tc'].')';
				$params = array('menu_tc' => $params['menu_tc']);
				break;
			case 'order':
				$sql = 'UPDATE mc_thematic 
						SET order_tc = :order_tc
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

	/**
	 * @param $config
	 * @param array $params
	 * @return bool|string
	 */
	public function delete($config, $params = array())
    {
		if (!is_array($config)) return '$config must be an array';
		$sql = '';

		switch ($config['type']) {
			case 'delPages':
				$sql = 'DELETE FROM mc_thematic 
						WHERE id_tc IN ('.$params['id'].')';
				$params = array();
				break;
		}

		if($sql === '') return 'Unknown request asked';

		try {
			component_routing_db::layer()->delete($sql,$params);
			return true;
		}
		catch (Exception $e) {
			return 'Exception reçue : '.$e->getMessage();
		}
    }
}