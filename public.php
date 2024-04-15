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
        if(!isset($this->module)) $this->module = new frontend_model_module();
        if(!isset($this->mods)) $this->mods = $this->module->load_module('thematic');
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
     * @return void
     */
    private function initImageComponent() {
        if(!isset($this->imagesComponent)) $this->imagesComponent = new component_files_images($this->template);
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
     * Formate les valeurs principales d'un élément suivant la ligne passées en paramètre
     * @param $row
     * @param $current
     * @param bool $newRow
     * @return array|null
     * @throws Exception
     */
    public function setItemData($row,$current,$newRow = false)
    {
        $this->initImageComponent();
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
                if (isset($row['img'])) {
                    if(is_array($row['img'])) {
                        foreach ($row['img'] as $val) {
                            $image = $this->imagesComponent->setModuleImage('thematic','thematic',$val['name_img'],$row['id_tc'],$val['alt_img'] ?? $row['name_tc'], $val['title_img'] ?? $row['name_tc']);
                            if($val['default_img']) {
                                $data['img'] = $image;
                                $image['default'] = 1;
                            }
                            $data['imgs'][] = $image;
                        }
                        $data['img']['default'] = $this->imagesComponent->setModuleImage('thematic','thematic');
                    }
                }
                else {
                    if(isset($row['name_img'])) {
                        $data['img'] = $this->imagesComponent->setModuleImage('thematic','thematic',$row['name_img'],$row['id_tc'],$row['alt_img'] ?? $row['name_tc'], $row['title_img'] ?? $row['name_tc']);
                    }
                    $data['img']['default'] = $this->imagesComponent->setModuleImage('thematic','thematic');
                }
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
    private function getRootData() {
        $collection = $this->getItems('root',['iso'=>$this->template->lang],'all',false);

        $newData = [];
        foreach ($collection as $item) {
            $newData[$item['name_info']] = $item['value_info'];
        }

        return $this->setItemData($newData,null);
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getThematicData(): array {

        $collection = $this->getItems('thematic',['id' => $this->id, 'iso' => $this->template->lang],'one',false);
        $imgCollection = $this->getItems('imgs', array('id' => $this->id, 'iso' => $this->template->lang), 'all', false);
        $collection['img'] = $imgCollection;
        $this->template->breadcrumb->addItem($collection['name_tc']);//name_tc

        return $this->setItemData($collection,null);
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
    public function setHrefLangData(array $row): array {
        $arr = [];
        foreach ($row as $item) {
            $arr[$item['id_lang']] = '/'.$row['iso_lang'].'/thematic/'.$row['id_tc'].'-'.$row['url_tc'].'/';
        }
        return $arr;
    }
    /**
     * Return data Lang
     * @param $type
     * @return array
     */
    private function getBuildLangItems(){
        $collection = $this->getItems('langs',['id'=>$this->id],'all',false);
        return $this->setHrefLangData($collection);
    }

    /**
     * @param $id
     * @param $limit
     * @return array
     * @throws Exception
     */
    public function getThematicList($id = null, $limit = 1) : array {
        $limit = $limit ? ' LIMIT '.$limit : '';
        $conditions = ' WHERE lang.iso_lang = :iso AND pc.published_tc = 1 AND p.id_parent '.($id === null ? 'IS NULL' : '= '.$id).' AND (img.default_img = 1 OR img.default_img IS NULL) ORDER BY p.order_tc ASC, p.id_tc DESC'.$limit;
        $collection = parent::fetchData(
            ['context' => 'all', 'type' => 'pages', 'conditions' => $conditions],
            ['iso' => $this->template->lang]
        );
        $newArr = [];
        if(!empty($collection)) {
            foreach ($collection as $item) {
                $newArr[] = $this->setItemData($item,null);
            }
        }
        return $newArr;
    }

    /**
     * Assign page's data to smarty
     * @access private
     * @param $type
     */
    private function getData($type)
    {
        $data = $this->getRootData();
        $this->template->assign('root',$data,true);
        if($type !== 'root') {
            $hreflang = $this->getBuildLangItems();
            $this->template->assign('hreflang',$hreflang,true);
        }

        switch($type){
            case 'root':
                $this->template->breadcrumb->addItem($this->template->getConfigVars('thematic'));
                $cats = $this->getThematicList();
                $this->template->assign('thematics',$cats,true);
                break;
            case 'thematic':
                $this->template->breadcrumb->addItem($this->template->getConfigVars('thematic'),'/'.$this->template->lang.'/thematic/');
                $data = $this->getThematicData();
                $this->template->assign('thematic',$data,true);
				$cats = $this->getThematicList($this->id);
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
            $parent = $this->getThematicList();
            $this->template->assign('parent',$parent,true);
        }
    }

    /**
     * @throws Exception
     */
    public function run() {
    	//$this->setBreadcrumb();
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