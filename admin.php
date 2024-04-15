<?php
include_once ('dbadmin.php');
/**
 * Class plugins_test_admin
 * Fichier pour l'administration d'un plugin
 */
class plugins_thematic_admin extends plugins_thematic_dbadmin{
    public $edit, $action, $tabs, $search, $plugin, $controller;
    protected $message, $template, $header, $data, $modelLanguage, $collectionLanguage, $order, $upload, $config, $imagesComponent, $modelPlugins,$routingUrl,$makeFiles,$finder,$plugins, $xml, $sitemap;
    public $id_tc,$parent_id,$content,$pages,$img_multiple,$iso,$del_img,$ajax,$tableaction,$tableform,$offset,$name_img,$menu_tc,$type,$id_img;
    protected component_core_feedback $progress;
    public $tableconfig = array(
        'all' => array(
            'id_tc',
            'name_tc' => array('title' => 'name'),
            'parent_tc' => array('col' => 'name_tc', 'title' => 'name'),
            'default_img' => array('title' => 'img','type' => 'bin', 'input' => null, 'class' => ''),
            'resume_tc' => array('type' => 'bin', 'input' => null),
            'content_tc' => array('type' => 'bin', 'input' => null),
            'seo_title_tc' => array('title' => 'seo_title', 'class' => '', 'type' => 'bin', 'input' => null),
            'seo_desc_tc' => array('title' => 'seo_desc', 'class' => '', 'type' => 'bin', 'input' => null),
            'menu_tc',
            'date_register'
        ),
        'parent' => array(
            'id_tc',
            'name_tc' => array('title' => 'name'),
            'default_img' => array('title' => 'img','type' => 'bin', 'input' => null, 'class' => ''),
            'resume_tc' => array('class' => 'fixed-td-lg', 'type' => 'bin', 'input' => null),
            'content_tc' => array('class' => 'fixed-td-lg', 'type' => 'bin', 'input' => null),
            'seo_title_tc' => array('title' => 'seo_title', 'class' => 'fixed-td-lg', 'type' => 'bin', 'input' => null),
            'seo_desc_tc' => array('title' => 'seo_desc', 'class' => 'fixed-td-lg', 'type' => 'bin', 'input' => null),
            'menu_tc',
            'date_register'
        )
    );
    /**
     * frontend_controller_home constructor.
     */
    public function __construct($t = null){
        $this->template = $t ? $t : new backend_model_template;
        $this->message = new component_core_message($this->template);
        $this->header = new http_header();
        $this->data = new backend_model_data($this);
        $formClean = new form_inputEscape();
        $this->modelLanguage = new backend_model_language($this->template);
        $this->collectionLanguage = new component_collections_language();
        $this->upload = new component_files_upload();
        $this->imagesComponent = new component_files_images($this->template);
        $this->modelPlugins = new backend_model_plugins();
        $this->routingUrl = new component_routing_url();
        $this->makeFiles = new filesystem_makefile();
        $this->finder = new file_finder();
        $this->xml = new xml_sitemap();
        $this->sitemap = new backend_model_sitemap($this->template);
        // --- GET
        if(http_request::isGet('controller')) $this->controller = $formClean->simpleClean($_GET['controller']);
        if (http_request::isGet('edit')) $this->edit = $formClean->numeric($_GET['edit']);
        if (http_request::isGet('action')) $this->action = $formClean->simpleClean($_GET['action']);
        elseif (http_request::isPost('action')) $this->action = $formClean->simpleClean($_POST['action']);
        if (http_request::isGet('tabs')) $this->tabs = $formClean->simpleClean($_GET['tabs']);
        if (http_request::isGet('ajax')) $this->ajax = $formClean->simpleClean($_GET['ajax']);
        if (http_request::isGet('offset')) $this->offset = intval($formClean->simpleClean($_GET['offset']));

        if (http_request::isGet('tableaction')) {
            $this->tableaction = $formClean->simpleClean($_GET['tableaction']);
            $this->tableform = new backend_controller_tableform($this,$this->template);
        }

        // --- Search
        if (http_request::isGet('search')) {
            $this->search = $formClean->arrayClean($_GET['search']);
            $this->search = array_filter($this->search, function ($value) { return $value !== ''; });
        }

        // --- ADD or EDIT
        if (http_request::isGet('id')) $this->id_tc = $formClean->simpleClean($_GET['id']);
        elseif (http_request::isPost('id')) $this->id_tc = $formClean->simpleClean($_POST['id']);
        if (http_request::isPost('parent_id')) $this->parent_id = $formClean->simpleClean($_POST['parent_id']);
        if (http_request::isPost('type')) $this->type = $formClean->simpleClean($_POST['type']);
        if (http_request::isPost('menu_tc')) $this->menu_tc = $formClean->simpleClean($_POST['menu_tc']);
        if (http_request::isPost('del_img')) $this->del_img = $formClean->simpleClean($_POST['del_img']);
        if (http_request::isPost('content')) {
            $array = $_POST['content'];
            foreach($array as $key => $arr) {
                foreach($arr as $k => $v) {
                    $array[$key][$k] = ($k == 'content_tc' OR $k == 'thematic_content') ? $formClean->cleanQuote($v) : $formClean->simpleClean($v);
                }
            }
            $this->content = $array;
        }

        // --- Image Upload
        if (isset($_FILES['img']["name"])) $this->img = http_url::clean($_FILES['img']["name"]);
        if (http_request::isPost('name_img')) $this->name_img = http_url::clean($_POST['name_img']);
        if (isset($_FILES['img_multiple']["name"])) $this->img_multiple = ($_FILES['img_multiple']["name"]);
        if (http_request::isPost('id_img')) $this->id_img = $formClean->simpleClean($_POST['id_img']);

        // --- Recursive Actions
        if (http_request::isGet('thematic'))  $this->pages = $formClean->arrayClean($_GET['league']);

        # ORDER PAGE
        if (http_request::isPost('thematic')) $this->order = $formClean->arrayClean($_POST['thematic']);
        if (http_request::isGet('plugin')) $this->plugin = $formClean->simpleClean($_GET['plugin']);

        # JSON LINK (TinyMCE)
        //if (http_request::isGet('iso')) $this->iso = $formClean->simpleClean($_GET['iso']);
    }

    /**
     * Assign data to the defined variable or return the data
     * @param string $type
     * @param string|int|null $id
     * @param string $context
     * @param boolean $assign
     * @param boolean $pagination
     * @return mixed
     */
    private function getItems($type, $id = null, $context = null, $assign = true, $pagination = false) {
        return $this->data->getItems($type, $id, $context, $assign, $pagination);
    }
    /**
     * Method to override the name of the plugin in the admin menu
     * @return string
     */
    public function getExtensionName()
    {
        return $this->template->getConfigVars('thematic_plugin');
    }
    /**
     * @param $ajax
     * @return mixed
     * @throws Exception
     */
    public function tableSearch($ajax = false)
    {
        $this->modelLanguage->getLanguage();
        $defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
        $params = array();

        if($this->edit) {
            $results = $this->getItems('pagesChild',$this->edit,'all',false);
        }
        else {
            $results = $this->getItems('pages',array('default_lang'=>$defaultLanguage['id_lang']),'all',false, true);
        }

        $assign = $this->tableconfig[(($ajax || $this->edit) ? 'parent' : 'all')];

        if($ajax) {
            $params['section'] = 'pages';
            $params['idcolumn'] = 'id_tc';
            $params['activation'] = true;
            $params['sortable'] = true;
            $params['checkbox'] = true;
            $params['edit'] = true;
            $params['dlt'] = true;
            $params['readonly'] = array();
            $params['cClass'] = 'plugins_thematic_admin';
        }

        $this->data->getScheme(array('mc_thematic','mc_thematic_content'),array('id_tc','name_tc','img_tc','resume_tc','content_tc','seo_title_tc','seo_desc_tc','menu_tc','date_register'),$assign);

        return array(
            'data' => $results,
            'var' => 'pages',
            'tpl' => 'index.tpl',
            'params' => $params
        );
    }
    /**
     * Update data
     * @param $data
     * @throws Exception
     */
    private function add($data)
    {
        switch ($data['type']) {
            case 'page':
            case 'content':
            case 'root':
                parent::insert(
                    $data['type'],
                    $data['data']
                );
                break;
        }
    }

    /**
     * Mise a jour des données
     * @param $data
     * @throws Exception
     */
    private function upd($data)
    {
        switch ($data['type']) {
            case 'order':
                $p = $this->order;
                for ($i = 0; $i < count($p); $i++) {
                    parent::update(
                        array(
                            'type'=>$data['type']
                        ),array(
                            'id_tc'       => $p[$i],
                            'order_tc'    => $i + (isset($this->offset) ? ($this->offset + 1) : 0)
                        )
                    );
                }
                break;
            case 'page':
            case 'content':
            case 'img':
            case 'pageActiveMenu':
            case 'root':
                parent::update(
                    $data['type'],
                    $data['data']
                );
            break;
            case 'imageDefault':
                parent::update(
                    $data['type'],
                    $data['data']
                );
                $this->message->json_post_response(true,'update');
                break;
        }
    }

    /**
     * Insertion de données
     * @param $data
     * @throws Exception
     */
    private function del($data)
    {
        switch($data['type']){
            case 'delPages':
                parent::delete(
                    array(
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                $this->message->json_post_response(true,'delete',$data['data']);
                break;
        }
    }
    /**
     * Active / Unactive page(s)
     * @param $params
     * @throws Exception
     */
    public function tableActive($params)
    {
        $this->upd(array(
            'type' => 'pageActiveMenu',
            'data' => array(
                'menu_tc' => $params['active'],
                'id_tc' => $params['ids']
            )
        ));
        $this->message->getNotify('update',array('method'=>'fetch','assignFetch'=>'message'));
    }
    /**
     * @return array
     * @throws Exception
     */
    private function setRootData(){
        $data = $this->getItems('rootContent', null, 'all', false);
        $newArr = array();
        foreach ($data as $item) {
            $newArr[$item['id_lang']][$item['name_info']] = $item['value_info'];
        }
        return $newArr;
    }
    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    private function setItemData($data){
        $arr = array();
        $conf = array();
        foreach ($data as $page) {
            /*$publicUrl = !empty($page['url_tc']) ? $this->routingUrl->getBuildUrl(array(
                    'type'      =>  'pages',
                    'iso'       =>  $page['iso_lang'],
                    'id'        =>  $page['id_tc'],
                    'url'       =>  $page['url_tc']
                )
            ) : '';*/
            $publicUrl = '/'.$page['iso_lang'].'/'.$this->controller.'/'.$page['id_tc'].'-'.$page['url_tc'].'/';

            if (!array_key_exists($page['id_tc'], $arr)) {
                $arr[$page['id_tc']] = array();
                $arr[$page['id_tc']]['id_tc'] = $page['id_tc'];
                $arr[$page['id_tc']]['menu_tc'] = $page['menu_tc'];
                $arr[$page['id_tc']]['date_register'] = $page['date_register'];
            }
            $arr[$page['id_tc']]['content'][$page['id_lang']] = array(
                'id_lang'           => $page['id_lang'],
                'iso_lang'          => $page['iso_lang'],
                'name_tc'        => $page['name_tc'],
                'title_tc'  => $page['title_tc'],
                'url_tc'         => $page['url_tc'],
                'resume_tc'      => $page['resume_tc'],
                'content_tc'     => $page['content_tc'],
                'seo_title_tc'   => $page['seo_title_tc'],
                'seo_desc_tc'    => $page['seo_desc_tc'],
                'published_tc'   => $page['published_tc'],
                'public_url'        => $publicUrl
            );
        }
        return $arr;
    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */
    private function saveContent($id)
    {
        $extendData = array();

        foreach ($this->content as $lang => $content) {
            $content['id_lang'] = $lang;
            $content['id_tc'] = $id;
            $content['published_tc'] = (!isset($content['published_tc']) ? 0 : 1);
            $content['title_tc'] = (!empty($content['title_tc']) ? $content['title_tc'] : NULL);
            $content['resume_tc'] = (!empty($content['resume_tc']) ? $content['resume_tc'] : NULL);
            $content['content_tc'] = (!empty($content['content_tc']) ? $content['content_tc'] : NULL);
            $content['seo_title_tc'] = (!empty($content['seo_title_tc']) ? $content['seo_title_tc'] : NULL);
            $content['seo_desc_tc'] = (!empty($content['seo_desc_tc']) ? $content['seo_desc_tc'] : NULL);

            if (empty($content['url_tc'])) {
                $content['url_tc'] = http_url::clean($content['name_tc'],
                    array(
                        'dot' => false,
                        'ampersand' => 'strict',
                        'cspec' => '', 'rspec' => ''
                    )
                );
            }

            $contentPage = $this->getItems('content', array('id' => $id, 'id_lang' => $lang), 'one', false);
            //print_r($contentPage);
            if ($contentPage != null) {
                $this->upd(
                    array(
                        'type' => 'page',
                        'data' => array(
                            'id_tc' => $id,
                            'id_parent' => empty($this->parent_id) ? NULL : $this->parent_id,
                            'menu_tc' => isset($this->menu_tc) ? 1 : 0
                        )
                    )
                );
                $this->upd(
                    array(
                        'type' => 'content',
                        'data' => $content
                    )
                );
            } else {
                $this->add(
                    array(
                        'type' => 'content',
                        'data' => $content
                    )
                );
            }

            if (isset($this->id_tc)) {
                $setEditData = $this->getItems('page', array('edit' => $this->edit), 'all', false);
                $setEditData = $this->setItemData($setEditData);
                $extendData[$lang] = $setEditData[$this->id_tc]['content'][$lang]['public_url'];
            }
        }

        //if (!empty($extendData)) return $extendData;
        if (!empty($extendData)){
            $this->message->json_post_response(true, 'update', array('result'=>$this->id_tc,'extend'=>$extendData));
        }
    }
    /**
     * save data
     */
    private function saveRoot(){
        if (isset($this->content)) {
            foreach ($this->content as $lang => $content) {
                $rootContent = $this->getItems('rootContent', array('id_lang' => $lang), 'one', false);

                if ($rootContent != null) {
                    $this->upd(
                        array(
                            'type' => 'root',
                            'data' => array(
                                'nm' => !empty($content['thematic_name']) ? $content['thematic_name'] : NULL,
                                'content' => !empty($content['thematic_content']) ? $content['thematic_content'] : NULL,
                                'seo_title' => !empty($content['seo_title']) ? $content['seo_title'] : NULL,
                                'seo_desc'  => !empty($content['seo_desc']) ? $content['seo_desc'] : NULL,
                                'id_lang' => $lang
                            )
                        )
                    );

                } else {
                    $this->add(
                        array(
                            'type' => 'root',
                            'data' => array(
                                'name' => !empty($content['thematic_name']) ? $content['thematic_name'] : NULL,
                                'content' => !empty($content['thematic_content']) ? $content['thematic_content'] : NULL,
                                'seo_title' => !empty($content['seo_title']) ? $content['seo_title'] : NULL,
                                'seo_desc'  => !empty($content['seo_desc']) ? $content['seo_desc'] : NULL,
                                'id_lang' => $lang
                            )
                        )
                    );
                }
            }
            $this->message->json_post_response(true, 'update', $this->content);
        }
    }

    /**
     * @throws Exception
     */
    public function run(){
        if(isset($this->tableaction)) {
            $this->tableform->run();
        }
        elseif(isset($this->action)) {
            switch ($this->action) {
                case 'add':
                    if(isset($this->content)) {
                        $this->add(
                            array(
                                'type' => 'page',
                                'data' => array(
                                    'id_parent' => empty($this->parent_id) ? NULL : $this->parent_id,
                                    'menu_tc' => isset($this->menu_tc) ? 1 : 0
                                )
                            )
                        );

                        $page = $this->getItems('root',null,'one',false);

                        if ($page['id_tc']) {
                            $this->saveContent($page['id_tc']);
                            $this->message->json_post_response(true,'add_redirect');
                        }
                    }
                    else {
                        $this->modelLanguage->getLanguage();
                        $defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
                        $this->getItems('pagesSelect',array('default_lang'=>$defaultLanguage['id_lang']),'all');
                        $this->template->display('add.tpl');
                    }
                    break;
                case 'edit':
                    /*if(isset($this->img) || isset($this->name_img)){
                        $defaultLanguage = $this->collectionLanguage->fetchData(array('context' => 'one', 'type' => 'default'));
                        $page = $this->getItems('pageLang', array('id' => $this->id_tc, 'iso' => $defaultLanguage['iso_lang']), 'one', false);
                        $settings = array(
                            'name' => $this->name_img !== '' ? $this->name_img : $page['url_tc'],
                            'edit' => $page['img_tc'],
                            'prefix' => array('s_', 'm_', 'l_'),
                            'module_img' => 'plugins',
                            'attribute_img' => 'thematic',
                            'original_remove' => false
                        );
                        $dirs = array(
                            'upload_root_dir' => 'upload/thematic', //string
                            'upload_dir' => $this->id_tc //string ou array
                        );
                        $filename = '';
                        $update = false;

                        if(isset($this->img)) {
                            $resultUpload = $this->upload->setImageUpload('img', $settings, $dirs, false);
                            $filename = $resultUpload['file'];
                            $update = true;
                        }
                        elseif(isset($this->name_img)) {
                            $img_tc = pathinfo($page['img_tc']);
                            $img_name = $img_tc['filename'];

                            if($this->name_img !== $img_name && $this->name_img !== '') {
                                $result = $this->upload->renameImages($settings,$dirs);
                                $filename = $result;
                                $update = true;
                            }
                        }

                        if($filename !== '' && $update) {
                            $this->upd(array(
                                'type' => 'img',
                                'data' => array(
                                    'id_tc' => $this->id_tc,
                                    'img_tc' => $filename
                                )
                            ));
                        }

                        foreach ($this->content as $lang => $content) {
                            $content['id_lang'] = $lang;
                            $content['id_tc'] = $this->id_tc;
                            $content['alt_img'] = (!empty($content['alt_img']) ? $content['alt_img'] : NULL);
                            $content['title_img'] = (!empty($content['title_img']) ? $content['title_img'] : NULL);
                            $content['caption_img'] = (!empty($content['caption_img']) ? $content['caption_img'] : NULL);
                            $this->upd(array(
                                'type' => 'imgContent',
                                'data' => $content
                            ));
                        }

                        $setEditData = $this->getItems('page',array('edit'=>$this->id_tc),'all',false);
                        $setEditData = $this->setItemData($setEditData);
                        $this->template->assign('page',$setEditData[$this->id_tc]);
                        $display = $this->template->fetch('brick/img.tpl');
                        $this->message->json_post_response(true, 'update',$display);
                    }*/
                    if (isset($this->img_multiple)) {
                        $this->template->configLoad();
                        $this->progress = new component_core_feedback($this->template);

                        usleep(200000);
                        $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('control_of_data'), 'progress' => 30));

                        $defaultLanguage = $this->collectionLanguage->fetchData(array('context' => 'one', 'type' => 'default'));
                        $page = $this->getItems('content', ['id' => $this->id_tc, 'id_lang' => $defaultLanguage['id_lang']], 'one', false);
                        //print_r($page);
                        $lastIndex = $this->getItems('lastImgId', ['id_tc' => $this->id_tc], 'one', false);
                        $lastIndex['index'] = $lastIndex['index'] ?? 0;

                        $resultUpload = $this->upload->multipleImageUpload(
                            'thematic','thematic','upload/thematic',["$this->id_tc"],[
                            'name' => http_url::clean($page['name_tc']),
                            'suffix' => (int)$lastIndex['index'],
                            'suffix_increment' => true,
                            'progress' => $this->progress,
                            'template' => $this->template
                        ]);

                        if (!empty($resultUpload)) {
                            $totalUpload = count($resultUpload);
                            $percent = $this->progress->progress;
                            $preparePercent = (90 - $percent) / $totalUpload;
                            $i = 1;

                            foreach ($resultUpload as $value) {
                                if ($value['status']) {
                                    $percent = $percent + $preparePercent;

                                    usleep(200000);
                                    $this->progress->sendFeedback(['message' => sprintf($this->template->getConfigVars('creating_records'),$i,$totalUpload), 'progress' => $percent]);

                                    $this->insert('img',[
                                        'id_tc' => $this->id_tc,
                                        'name_img' => $value['file']
                                    ]);
                                }
                                $i++;
                            }

                            usleep(200000);
                            $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('creating_thumbnails_success'), 'progress' => 90));

                            usleep(200000);
                            $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('upload_done'), 'progress' => 100, 'status' => 'success'));
                        }
                        else {
                            usleep(200000);
                            $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('creating_thumbnails_error'), 'progress' => 100, 'status' => 'error', 'error_code' => 'error_data'));
                        }
                    }
                    elseif (isset($this->id_tc)) {
                        $this->saveContent($this->id_tc);
                        //$extendData = $this->saveContent($this->id_tc);
                        //$this->message->json_post_response(true, 'update', array('result'=>$this->id_tc,'extend'=>$extendData));
                    }
                    elseif (isset($this->type)) {
                        $this->saveRoot();
                    }
                    else {
                        // Initialise l'API menu des plugins core
                        $this->modelLanguage->getLanguage();
                        $setEditData = $this->getItems('page', array('edit'=>$this->edit),'all',false);
                        $setEditData = $this->setItemData($setEditData);
                        $this->template->assign('page',$setEditData[$this->edit]);
                        $this->data->getScheme(array('mc_thematic','mc_thematic_content'),array('id_tc','name_tc','resume_tc','content_tc','seo_title_tc','seo_desc_tc','menu_tc','date_register'),$this->tableconfig['parent']);
                        $this->getItems('pagesChild',$this->edit,'all');
                        $defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
                        $this->getItems('pagesSelect',array('default_lang'=>$defaultLanguage['id_lang']),'all');
                        $this->getItems('img', $this->edit, 'all');

                        $this->template->display('edit.tpl');
                    }
                    break;
                case 'order':
                    if (isset($this->order)) {
                        $this->upd(
                            array(
                                'type' => 'order'
                            )
                        );
                    }
                    break;
                case 'setImgDefault':
                    if (isset($this->id_img)) {
                        $this->upd(array(
                            'type' => 'imageDefault',
                            'data' => array('id' => $this->edit, 'id_img' => $this->id_img)
                        ));
                    }
                    break;
                case 'getImgDefault':
                    if (isset($this->edit)) {
                        $imgDefault = $this->getItems('imgDefault', $this->edit, 'one', false);
                        print $imgDefault['id_img'];
                    }
                    break;
                case 'getImages':
                    if (isset($this->edit)) {
                        $this->getItems('img', $this->edit, 'all');
                        $display = $this->template->fetch('brick/img.tpl');
                        $this->message->json_post_response(true, '', $display);
                    }
                    break;
                case 'delete':
                    if(isset($this->id_tc)) {
                        $this->del(
                            array(
                                'type'=>'delPages',
                                'data'=>array(
                                    'id' => $this->id_tc
                                )
                            )
                        );
                    }
                    elseif(isset($this->del_img)) {
                        $this->upd(array(
                            'type' => 'img',
                            'data' => array(
                                'id_tc' => $this->del_img,
                                'img_tc' => NULL
                            )
                        ));

                        $setEditData = $this->getItems('page',array('edit'=>$this->del_img),'all',false);
                        $setEditData = $this->setItemData($setEditData);

                        $setImgDirectory = $this->upload->dirImgUpload(
                            array_merge(
                                array('upload_root_dir'=>'upload/thematic/'.$this->del_img),
                                array('imgBasePath'=>true)
                            )
                        );

                        if(file_exists($setImgDirectory)){
                            $setFiles = $this->finder->scanDir($setImgDirectory);
                            $clean = '';
                            if($setFiles != null){
                                foreach($setFiles as $file){
                                    $clean .= $this->makeFiles->remove($setImgDirectory.$file);
                                }
                            }
                        }
                        $this->template->assign('page',$setEditData[$this->del_img]);
                        $display = $this->template->fetch('brick/img.tpl');
                        $this->message->json_post_response(true, 'update',$display);
                    }
                    break;
                case 'getLink':
                    if(isset($this->id_tc) && isset($this->iso)) {
                        $page = $this->getItems('pageLang',array('id' => $this->id_tc,'iso' => $this->iso),'one',false);
                        if($page) {
                            $page['url'] = $this->routingUrl->getBuildUrl(array(
                                'type'      =>  'pages',
                                'iso'       =>  $page['iso_lang'],
                                'id'        =>  $page['id_tc'],
                                'url'       =>  $page['url_tc']
                            ));
                            $link = '<a title="'.$page['url'].'" href="'.$page['name_tc'].'">'.$page['name_tc'].'</a>';
                            $this->header->set_json_headers();
                            print '{"name":'.json_encode($page['name_tc']).',"url":'.json_encode($page['url']).'}';
                        }
                        else {
                            print false;
                        }
                    }
                    break;
            }
        }
        else {
            $this->modelLanguage->getLanguage();
            $this->template->assign('contentData',$this->setRootData());
            $defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
            $this->getItems('pages',array('default_lang'=>$defaultLanguage['id_lang']),'all',true,true);
            $this->data->getScheme(array('mc_thematic','mc_thematic_content'),array('id_tc','name_tc','resume_tc','content_tc','seo_title_tc','seo_desc_tc','menu_tc','date_register'),$this->tableconfig['parent']);
            $this->template->display('index.tpl');
        }
    }
}