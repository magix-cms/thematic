<?php

/**
 * Class plugins_club_public
 * Fichier pour l'éxecution frontend d'un plugin
 */
//require('model.php');
class plugins_thematic_public extends plugins_thematic_dbfront
{
    protected $template, $data, $logo, $imagesComponent, $seo, $log, $module, $mods;
    public $controller, $id, $imagePlaceHolder, $fetchConfig, $imgPrefix, $model;

	/**
	 * plugins_tc_public constructor.
	 * @param frontend_model_template $t
	 */
    public function __construct($t = null){
        $this->template = $t instanceof frontend_model_template ? $t : new frontend_model_template();
		$this->data = new frontend_model_data($this,$this->template);
        $formClean = new form_inputEscape();
		$this->seo = new frontend_model_seo('thematic', '', '',$this->template);
		$this->log = new debug_logger(MP_LOG_DIR);

        if(http_request::isGet('controller')) $this->controller = $formClean->simpleClean($_GET['controller']);
        if (http_request::isGet('id')) $this->id = $formClean->numeric($_GET['id']);
    }

    /**
     *
     */
    private function loadModules() {
        $this->module = $this->module instanceof frontend_model_module ? $this->module : new frontend_model_module($this->template);
		if(empty($this->mods)) $this->mods = $this->module->load_module('thematic');
    }

	/**
	 * Assign data to the defined variable or return the data
	 * @param string $type
	 * @param string|int|null $id
	 * @param string $context
	 * @param boolean $assign
	 * @return mixed
	 */
	public function getItems($type, $id = null, $context = null, $assign = true) {
		return $this->data->getItems($type, $id, $context, $assign);
	}

	/**
	 * @return mixed
	 */
	public function getThematics()
	{
		$parents = $this->getItems('mainThematics',['iso' => $this->template->lang],'all',false);
		if(!empty($parents)) {
			/*foreach ($parents as &$parent) {
				$parent['subdata'] = $this->getItems('childThematics',['id' => $parent['id_tc'], 'iso' => $this->template->lang],'all',false);
			}*/
			$parents = $this->data->setPagesTree($parents,'tc','root',1);
		}
		return $parents;
	}

	/**
	 * Get the thematics of a specific plugger
	 * @param $id1
	 * @param $id2
	 * @return array
	 */
	public function getPluggerThematics($id1, $id2)
	{
		$tcs = [];
		$tcs[] = $this->getItems('thematic_short',['id' => $id1, 'iso' => $this->template->lang],'one',false);
		$tcs[] = $this->getItems('thematic_short',['id' => $id2, 'iso' => $this->template->lang],'one',false);
		return $tcs;
	}

	/**
	 * @param $conf
	 * @throws Exception
	 */
	private function initImageComponents($conf)
	{
		$this->imagesComponent = new component_files_images($this->template);
		$this->logo = new frontend_model_logo();
		$this->imagePlaceHolder = $this->logo->getImagePlaceholder();
		$this->imgPrefix = $this->imagesComponent->prefix();
		$this->fetchConfig = $this->imagesComponent->getConfigItems($conf);
	}

    /**
     * Formate les valeurs principales d'un élément suivant la ligne passées en paramètre
     * @param $row
     * @param $current
     * @param bool $newRow
     * @return array|null
     * @throws Exception
     */
    public function setItemData($row,$current,$newRow = false)
    {
        $string_format = new component_format_string();
        $data = null;
        $extwebp = 'webp';

        if ($row !== null) {
            if (isset($row['name'])) {
                $data['name'] = $row['name'];
                $data['content'] = $row['content'];

				$this->seo->level = 'root';
                if (!isset($row['seo_title']) || empty($row['seo_title'])) {
                    $seoTitle = $this->seo->replace_var_rewrite('','','title');
                    $data['seo']['title'] = $seoTitle ? $seoTitle : $data['name'];
                }else{
                    $data['seo']['title'] = $row['seo_title'];
                }
                if (!isset($row['seo_desc']) || empty($row['seo_desc'])) {
                    $seoTitle = $this->seo->replace_var_rewrite('','','title');
                    $data['seo']['description'] = $seoTitle ? $seoTitle : $data['content'];
                }else{
                    $data['seo']['description'] = $string_format->truncate(strip_tags($row['content']));
                }
            }
            elseif (isset($row['name_tc'])) {
                $data['id'] = $row['id_tc'];
                $data['id_parent'] = !is_null($row['id_parent']) ? $row['id_parent'] : NULL;
                $data['name'] = $row['name_tc'];
                $data['title'] = $row['title_tc'];
                $data['iso'] = $row['iso_lang'];
                $data['url'] = '/'.$row['iso_lang'].'/thematic/'.$row['id_tc'].'-'.$row['url_tc'].'/';
                $data['active'] = $row['id_tc'] == $current['controller']['id'];
                if (isset($row['img_tc'])) {
                    // # return filename without extension
                    $pathinfo = pathinfo($row['img_tc']);
                    $filename = $pathinfo['filename'];

                    foreach ($this->fetchConfig as $key => $value) {
                        $imginfo = $this->imagesComponent->getImageInfos(component_core_system::basePath().'/upload/thematic/' . $row['id_tc'] . '/' . $this->imgPrefix[$value['type_img']] . $row['img_tc']);
                        $data['img'][$value['type_img']]['src'] = '/upload/thematic/' . $row['id_tc'] . '/' . $this->imgPrefix[$value['type_img']] . $row['img_tc'];
                        $data['img'][$value['type_img']]['src_webp'] = '/upload/thematic/' . $row['id_tc'] . '/' . $this->imgPrefix[$value['type_img']] . $filename. '.' .$extwebp;
                        $data['img'][$value['type_img']]['w'] = $value['resize_img'] === 'basic' ? $imginfo['width'] : $value['width_img'];
                        $data['img'][$value['type_img']]['h'] = $value['resize_img'] === 'basic' ? $imginfo['height'] : $value['height_img'];
                        $data['img'][$value['type_img']]['crop'] = $value['resize_img'];
                        $data['img'][$value['type_img']]['ext'] = mime_content_type(component_core_system::basePath().'/upload/thematic/' . $row['id_tc'] . '/' . $this->imgPrefix[$value['type_img']] . $row['img_tc']);
                    }
                    $data['img']['name'] = $row['img_tc'];
                }
                $data['img']['default'] = isset($this->imagePlaceHolder['pages']) ? $this->imagePlaceHolder['pages'] : '/skin/'.$this->template->theme.'/img/pages/default.png' ;
                $data['img']['alt'] = $row['alt_img'];
                $data['img']['title'] = $row['title_img'];
                $data['img']['caption'] = $row['caption_img'];
                $data['content'] = $row['content_tc'];
                $data['resume'] = $row['resume_tc'] ? $row['resume_tc'] : ($row['content_tc'] ? $string_format->truncate(strip_tags($row['content_tc'])) : '');
                $data['menu'] = $row['menu_tc'];
                $data['date']['update'] = $row['last_update'];
                $data['date']['register'] = $row['date_register'];
                //$data['seo']['title'] = $row['seo_title_tc'];
                //$data['seo']['description'] = $row['seo_desc_tc'] ? $row['seo_desc_tc'] : ($data['resume'] ? $data['resume'] : $data['seo']['title']);
                $data['title_tc'] = $row['title_tc'];

				$this->seo->level = 'record';
				if (!isset($row['seo_title_tc']) || empty($row['seo_title_tc'])) {
					$seoTitle = $this->seo->replace_var_rewrite('',$data['name'],'title');
					$data['seo']['title'] = $seoTitle ? $seoTitle : $data['name'];
				}
				else {
					$data['seo']['title'] = $row['seo_title_tc'];
				}
				if (!isset($row['seo_desc_tc']) || empty($row['seo_desc_tc'])) {
					$seoDesc = $this->seo->replace_var_rewrite('',$data['name'],'description');
					$data['seo']['description'] = $seoDesc ? $seoDesc : ($data['resume'] ? $data['resume'] : $data['seo']['title']);
				}
				else {
					$data['seo']['description'] = $row['seo_desc_tc'];
				}
            }
            return $data;
        }
    }

	/**
	 * Formate les valeurs principales d'un élément suivant la ligne passées en paramètre
	 * @param $row
	 * @return array|null
	 * @throws Exception
	 */
	public function setItemShortData($row)
	{
		$data = null;
		if ($row != null) {
            if (isset($row['name'])) {
				$data['name'] = $row['name'];
			}
			elseif (isset($row['name_tc'])) {
				$data['name'] = !empty($row['title_tc']) ? $row['title_tc'] : $row['name_tc'];
				$data['url'] = '/'.$row['iso_lang'].($this->template->is_amp() ? '/amp' : '').'/thematic/'.$row['id_tc'].'-'.$row['url_tc'].'/';
				$data['seo']['title'] = $row['seo_title_tc'];
			}
			return $data;
		}
	}

    /**
     * @return array|null
     */
    private function getBuildRootItems() {
        $collection = $this->getItems('root',['iso'=>$this->template->lang],'all',false);

        $newData = [];
        foreach ($collection as $item) {
            $newData[$item['name_info']] = $item['value_info'];
        }

        return $this->setItemData($newData,null);
    }

    /**
     * set Data from database
     * @access private
     */
    private function getBuildThematicItems()
    {
        $collection = $this->getItems('thematic',['id' => $this->id, 'iso' => $this->template->lang],'one',false);
        $this->initImageComponents(array(
            'module_img' => 'plugins',
            'attribute_img' => 'thematic'
        ));
        return $this->setItemData($collection,null);
    }

    /**
     * Parse data for frontend use
     * @param $data
     * @return mixed|null
     * @throws Exception
     */
	public function parseData($data)
	{
		if($data){
			// ** Loop management var
			$deep = 1;
			$deep_minus = $deep  - 1;
			$deep_plus = $deep  + 1;
			$pass_trough = 0;
			$data_empty = false;

			// ** Loop format & output var
			$row = array();
			$items = array();
			$i[$deep] = 0;

			do{
				// *** loop management START
				if ($pass_trough == 0){
					// Si je n'ai plus de données à traiter je vide ma variable
					$row[$deep] = null;
				}else{
					// Sinon j'active le traitement des données
					$pass_trough = 0;
				}

				// Si je suis au premier niveaux et que je n'ai pas de donnée à traiter
				if ($deep == 1 AND $row[$deep] == null) {
					// récupération des données dans $data
					$row[$deep] = array_shift($data);
				}

				// Si ma donnée possède des sous-donnée sous-forme de tableau
				if (isset($row[$deep]['subdata']) ){
					if (is_array($row[$deep]['subdata']) AND $row[$deep]['subdata'] != null){
						// On monte d'une profondeur
						$deep++;
						$deep_minus++;
						$deep_plus++;
						// on récupére la  première valeur des sous-données en l'éffacant du tableau d'origine
						$row[$deep] = array_shift($row[$deep_minus]['subdata']);
						// Désactive le traitement des données
						$pass_trough = 1;
					}
				}elseif($deep != 1){
					if ( $row[$deep] == null) {
						if ($row[$deep_minus]['subdata'] == null){
							// Si je n'ai pas de sous-données & pas de données à traiter & pas de frères à récupérer dans mon parent
							// ====> désactive le tableaux de sous-données du parent et retourne au niveau de mon parent
							unset ($row[$deep_minus]['subdata']);
							unset ($i[$deep]);
							$deep--;
							$deep_minus = $deep  - 1;
							$deep_plus = $deep  + 1;
						}else{
							// Je récupère un frère dans mon parent
							$row[$deep] = array_shift($row[$deep_minus]['subdata']);
						}
						// Désactive le traitement des données
						$pass_trough = 1;
					}
				}
				// *** loop management END

				// *** list format START
				if ($row[$deep] != null AND $pass_trough != 1){
					$i[$deep]++;

					// Construit doonées de l'item en array avec clée nominative unifiée ('name' => 'monname,'descr' => '<p>ma descr</p>,...)
					$itemData = $this->setItemShortData($row[$deep]);

					// Récupération des sous-données (enfants)
					if(isset($items[$deep_plus]) != null) {
						$itemData['subdata'] = $items[$deep_plus];
						$items[$deep_plus] = null;
					}else{
						$subitems = null;
					}

					$items[$deep][] = $itemData;
				}
				// *** list format END

				// Si $data est vide ET que je n'ai plus de données en traitement => arrête la boucle
				if (empty($data) AND $row[1] == null){
					$data_empty = true;
				}

			}while($data_empty == false);

			return $items[$deep];
		}
		return null;
	}

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
	public function getParents($id)
	{
		$data = $this->getItems('parents');
		$p = array((int)$id);
		$parent = $id;

		do {
			$s = $parent;
			foreach ($data as $k => $row) {
				if(in_array($parent,explode(',',$row['children']))) {
					$parent = $row['parent'];
					$p[] = $row['parent'];
					unset($data[$k]);
				}
			}
			if($s === $parent) $parent = null;

		} while ($parent !== null);

		return $p;
	}

    /**
     * Return data Lang
     * @param $type
     * @return array
     */
    private function getBuildLangItems($type){
        switch($type){
            case 'cat':
                $collection = $this->getItems('catLang',array(':id'=>$this->id),'all',false);
                return $this->modelCatalog->setHrefLangCategoryData($collection);
                break;
            case 'product':
                $collection = $this->getItems('productLang',array(':id'=>$this->id),'all',false);
                return $this->modelCatalog->setHrefLangProductData($collection);
                break;
        }
    }

	/**
	 * @param null $id
	 * @return array
	 * @throws Exception
	 */
    private function getBuildThematicList($id = null)
    {
        $conditions = ' WHERE lang.iso_lang = :iso AND c.published_tc = 1 AND p.id_parent '.($id === null ? 'IS NULL' : '= '.$id).' ORDER BY p.order_tc';
        $collection = parent::fetchData(
            ['context' => 'all', 'type' => 'pages', 'conditions' => $conditions],
            ['iso' => $this->template->lang]
        );
        $newarr = [];
        if(!empty($collection)) {
            $this->initImageComponents([
                'module_img' => 'plugins',
                'attribute_img' => 'thematic'
            ]);
            foreach ($collection as $item) {
                $newarr[] = $this->setItemData($item,null);
            }
        }
        return $newarr;
    }

    /**
     * Assign page's data to smarty
     * @access private
     * @param $type
     */
    private function getData($type)
    {
        $data = $this->getBuildRootItems();
        $this->template->assign('root',$data,true);
        if($type !== 'root') {
            $hreflang = $this->getBuildLangItems($type);
            $this->template->assign('hreflang',$hreflang,true);
        }

        switch($type){
            case 'root':
                $cats = $this->getBuildThematicList();
                $this->template->assign('thematics',$cats,true);
                break;
            case 'thematic':
                $data = $this->getBuildThematicItems();
                $this->template->assign('thematic',$data,true);
				$cats = $this->getBuildThematicList($this->id);
				$this->template->assign('thematics',$cats,true);

                $lists = [];
                $this->loadModules();
                if(!empty($this->mods)) {
                    foreach ($this->mods as $name => $mod){
                        if(method_exists($mod,'getThematicContent')) {
                            $lists[$name] = $mod->getThematicContent($this->id);
                        }
                    }
                }
                $this->template->assign('lists',$lists,true);
                break;
        }

        if(isset($data['id_parent'])) {
            $this->id = $data['id_parent'];
            $parent = $this->getBuildThematicItems();
            $this->template->assign('parent',$parent,true);
        }
    }

	/**
	 * @throws Exception
	 */
	public function setBreadcrumb()
	{
		$iso = $this->template->lang;

		$breadplugin = [];
		$breadplugin[] = ['name' => $this->template->getConfigVars('thematic')];

		if($this->id) {
			$breadplugin[0]['url'] = http_url::getUrl().'/'.$iso.'/thematic/';
			$breadplugin[0]['title'] = $this->template->getConfigVars('thematic');
		}

		if($this->id) {
			$dataPage = $this->getItems('pages_short',['id' => $this->id, 'iso' => $iso],'all',false);

			if($dataPage) {
				$dataPage = $this->parseData($dataPage);

				$ids = $this->getParents($this->id);
				if(count($ids) > 1) {
					array_shift($ids);
					$ids = array_reverse($ids);

					$data = $this->getItems('pages_short',['id' => $ids, 'iso' => $iso],'all',false);

					if($data) {
						$data = $this->parseData($data);
						foreach($data as $item) {
							$breadplugin[] = [
                                'name' => $item['name'],
                                'url' => $item['url'],
                                'title' => $item['name']
                            ];
						}
					}
				}

				$breadplugin[] = ['name' => $dataPage[0]['name']];
			}
		}

		$this->template->assign('breadplugin', $breadplugin);
    }

    /**
     * @throws Exception
     */
    public function run() {
    	$this->setBreadcrumb();
        if(isset($this->id)) {
            $this->getData('thematic');
            $this->template->display('thematic/thematic.tpl');
        }
        else {
            $this->getData('root');
            $this->template->display('thematic/index.tpl');
        }
    }
}