<?php
    class publishObject {
        var $document_srl = null;
        var $oDocument = null;

        var $trackbacks = array(); // [url]->charset, log
        var $blogapis = array(); // [api_srl]->category, postid, log
        var $publish_me2day = false; // true/false
        var $published_me2day = false; // true/false
        var $publish_twitter = false; // true/false
        var $published_twitter = false; // true/false

        var $hosted_apis = array(
                'tistory.com' => array(
                    'title' => 'Tistory.com',
                    'blogapi_type' => 'metaweblog',
                    'blogapi_site_url' => '',
                    'blogapi_url' => 'http://[id].tistory.com/api',
                ),
                'textcube.com' => array(
                    'title' => 'Textcube.com',
                    'blogapi_type' => 'metaweblog',
                    'blogapi_site_url' => '',
                    'blogapi_url' => 'http://[id].textcube.com/api/blogapi',
                ),
                'naver_blog' => array(
                    'title' => 'Naver Blog',
                    'blogapi_type' => 'metaweblog',
                    'blogapi_site_url' => '',
                    'blogapi_url' => 'https://api.blog.naver.com/xmlrpc',
                ),
            );

        function publishObject($document_srl = 0) {
            if(!$document_srl) return;

            $oDocumentModel = &getModel('document');
            $this->oDocument = $oDocumentModel->getDocument($document_srl);
            if(!$this->oDocument->isExists()) return;

            $args->document_srl = $this->document_srl = $document_srl;
            $output = executeQuery('textyle.getPublishLogs', $args);
            if(!$output->data) return;
            $data = unserialize($output->data->logs);

            $this->trackbacks = is_array($data->trackbacks)?$data->trackbacks:array();
            $this->blogapis = is_array($data->blogapis)?$data->blogapis:array();
            $this->publish_me2day = $data->publish_me2day==true?true:false;
            $this->published_me2day = $data->publish_me2day==true?true:false;
            $this->publish_twitter = $data->published_twitter==true?true:false;
            $this->published_twitter = $data->published_twitter==true?true:false;
        }

        function getHostedApis() {
            return $this->hosted_apis;
        }

        function getBlogAPIInfo($service, $provider, $type, $url, $user_id, $password) {
            if(!preg_match('/^(http|https)/',$url)) $url = 'http://'.$url;

            $msg_lang = Context::getLang('msg_blogapi_registration');

            if(!$user_id) return new Object(-1,$msg_lang[3]);
            if(!$password ) return new Object(-1,$msg_lang[4]);

            if($service == 'hosted') {
                $host = $this->hosted_apis[$provider];

                if(!$host) return new Object(-1,'msg_invalid_request');

                $type = $host['blogapi_type'];
            }

            if(!$url) return new Object(-1,$msg_lang[2]);

            switch($type) {
                case 'blogger' :
                    break;
                case 'movalbletype' :
                    break;
                default :
                        require_once(_XE_PATH_.'modules/textyle/libs/metaweblog.class.php');
                        $oMeta = new metaWebLog($url, $user_id, $password);
                        $output = $oMeta->getUsersBlogs();
                        if(!$output->toBool()) return $output;
                    break;
            }
            return $output;
        }

        function getTrackbacks() {
            if(!$this->oDocument->isExists()) return array();
            return $this->trackbacks;
        }

        function getApis() {
            if(!$this->oDocument->isExists()) return array();

            $args->module_srl = $this->oDocument->get('module_srl');
            $output = executeQueryArray('textyle.getApis', $args);
            if(!$output->data) return array();

            foreach($output->data as $key => $val) {
                switch($val->blogapi_type) {
                    case 'blogger' :
                        break;
                    case 'movalbletype' :
                        break;
                    default :
                            require_once(_XE_PATH_.'modules/textyle/libs/metaweblog.class.php');
                            $oMeta = new metaWebLog($val->blogapi_url, $val->blogapi_user_id, $val->blogapi_password);
                            $val->categories = $oMeta->getCategories();
                        break;
                }
                if($this->blogapis[$val->api_srl]) {
                    $val->log = $this->blogapis[$val->api_srl]->log;
                }
                $apis[$val->api_srl] = $val;
            }
            return $apis;
        }

        function isMe2dayPublished() {
            return $this->published_me2day;
        }

        function isTwitterPublished() {
            return $this->published_twitter;
        }

        function addTrackback($trackback_url, $charset = 'UTF-8') {
            if(!$trackback_url || isset($this->trackbacks[$trackback_url])) return;
            $this->trackbacks[$trackback_url]->charset = $charset;
            $this->trackbacks[$trackback_url]->log = '';
        }

        function addBlogApi($api_srl, $category = null) {
            if(!$api_srl) return;
            if(isset($this->blogapis[$api_srl])) $this->blogapis[$api_srl]->reserve = true;
            else {
                $this->blogapis[$api_srl]->category = $category;
                $this->blogapis[$api_srl]->postid = null;
                $this->blogapis[$api_srl]->log = '';
            }
        }

        function setMe2day($flag = false) {
            $this->publish_me2day = $flag;
        }

        function setTwitter($flag = false) {
            $this->publish_twitter = $flag;
        }

        function save() {
            $logs->trackbacks = $this->trackbacks;
            $logs->blogapis = $this->blogapis;
            $logs->publish_me2day = $this->publish_me2day;
            $logs->published_me2day = $this->published_me2day;
            $logs->publish_twitter = $this->publish_twitter;
            $logs->published_twitter = $this->published_twitter;

            $args->document_srl = $this->document_srl;
            $args->logs = serialize($logs);
            $output = executeQuery('textyle.deletePublishLogs', $args);
            $output = executeQuery('textyle.insertPublishLogs', $args);
        }

        function publish() {
            $oTextyleModel = &getModel('textyle');
            $oTrackbackController = &getController('trackback');

            if(!$this->oDocument->isExists()) return;

            $oTextyle = $oTextyleModel->getTextyle($this->oDocument->get('module_srl'));

            if(count($this->trackbacks)) {
                foreach($this->trackbacks as $trackback_url => $val) {
                    $output = $oTrackbackController->sendTrackback($this->oDocument, $trackback_url, $val->charset);
                    if($output->toBool()) $this->trackbacks[$trackback_url]->log = Context::getLang('published').' ('.date("Y-m-d H:i").')';
                    else $this->trackbacks[$trackback_url]->log = $output->getMessage().' ('.date("Y-m-d H:i").')';
                }
            }

            if(count($this->blogapis)) {
                $apis = $this->getApis();
                foreach($this->blogapis as $api_srl => $val) {
                    if(!$apis[$api_srl] || !$val->reserve) continue;

                    if($val->postid) $this->modifyBlogApi($apis[$api_srl], $val->postid);
                    else $output = $this->sendBlogApi($apis[$api_srl]);

                    if($output->toBool()) {
                        $this->blogapis[$api_srl]->postid = $output->get('postid');
                        $this->blogapis[$api_srl]->log = Context::getLang('published').' ('.date("Y-m-d H:i").')';
                    } else {
                        $this->blogapis[$api_srl]->postid = null;
                        $this->blogapis[$api_srl]->log = $output->getMessage().' ('.date("Y-m-d H:i").')';
                    }
                    $this->blogapis[$api_srl]->reserve = false;
                }
            }

            if($this->publish_me2day && $oTextyle->getEnableMe2day()) $this->sendMe2day($oTextyle->getMe2dayUserID(), $oTextyle->getMe2dayUserKey());
            if($this->publish_twitter && $oTextyle->getEnableTwitter()) $this->sendTwitter($oTextyle->getTwitterUserID(), $oTextyle->getTwitterPassword());

            $this->save();
        }


        function sendBlogApi($api) {
            if(!$this->oDocument->isExists()) return;

            switch($api->blogapi_type) {
                case 'blogger' :
                    break;
                case 'movalbletype' :
                    break;
                default :
                        require_once(_XE_PATH_.'modules/textyle/libs/metaweblog.class.php');
                        $oMeta = new metaWebLog($api->blogapi_url, $api->blogapi_user_id, $api->blogapi_password);
                        $output = $oMeta->getUsersBlogs();
                        if(!$output->toBool()) return $output;

                        $blogid = $output->get('blogid');

                        if($this->oDocument->hasUploadedFiles()) {
                            $file_list = $this->oDocument->getUploadedFiles();
                            if(count($file_list)) {
                                foreach($file_list as $file) {
                                    $output = $oMeta->newMediaObject($blogid, $file->source_filename, $file->uploaded_filename);
                                    $target_file = $output->get('target_file');
                                    if($target_file) {
                                        $this->oDocument->add('content', str_replace($file->uploaded_filename, $target_file, $this->oDocument->get('content')));
                                    }
                                }
                            }
                        }

                        $output = $oMeta->newPost($blogid, $this->oDocument);
                    break;

            }
            return $output;
        }

        function modifyBlogApi($api, $postid) {
            if(!$this->oDocument->isExists()) return;

            switch($api->blogapi_type) {
                case 'blogger' :
                    break;
                case 'movalbletype' :
                    break;
                default :
                        require_once(_XE_PATH_.'modules/textyle/libs/metaweblog.class.php');
                        $oMeta = new metaWebLog($api->blogapi_url, $api->blogapi_user_id, $api->blogapi_password);
                        $output = $oMeta->getUsersBlogs();
                        if(!$output->toBool()) return $output;

                        $blogid = $output->get('blogid');

                        if($this->oDocument->hasUploadedFiles()) {
                            $file_list = $this->oDocument->getUploadedFiles();
                            if(count($file_list)) {
                                foreach($file_list as $file) {
                                    $output = $oMeta->newMediaObject($blogid, $file->source_filename, $file->uploaded_filename);
                                    $target_file = $output->get('target_file');
                                    if($target_file) {
                                        $this->oDocument->add('content', str_replace($file->uploaded_filename, $target_file, $this->oDocument->get('content')));
                                    }
                                }
                            }
                        }

                        $output = $oMeta->newPost($blogid, $this->oDocument);
                    break;

            }
            return $output;
        }

        function sendMe2day($user_id, $user_key) {
            require_once(_XE_PATH_.'modules/textyle/libs/me2day.api.php');

            if(!$user_id || !$user_key) return;
            $oMe2 = new me2api($user_id, $user_key);
            $output = $oMe2->doPost( sprintf('"%s":%s', $this->oDocument->getTitleText(), $this->oDocument->getPermanentUrl()) , $this->oDocument->get('tags'));
            if($output->toBool()) $this->published_me2day = true;
        }

        function sendTwitter($user_id, $password) {
            if(!$user_id || !$password) return;

            $url = 'http://twitter.com/statuses/update.xml';
            $buff = FileHandler::getRemoteResource($url, 'status='.urlencode(sprintf('%s %s', $this->oDocument->getTitleText(), $this->oDocument->getPermanentUrl())), 3, 'POST', 'application/x-www-form-urlencoded', 
                        array(
                            'Authorization'=>'Basic '.base64_encode($user_id.':'.$password),
                        )
                    );
            $this->published_twitter = true;
        }

    }
?>
