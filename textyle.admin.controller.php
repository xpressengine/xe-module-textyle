<?php
    /**
     * @class  textyleAdminController
     * @author sol (sol@ngleader.com)
     * @brief  textyle 모듈의 admin controller class
     **/

    class textyleAdminController extends textyle {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief textyle 생성
         **/
        function procTextyleAdminCreate() {
            $oModuleModel = &getModel('module');

            $user_id = Context::get('user_id');
            $domain = preg_replace('/^(http|https):\/\//i','', trim(Context::get('domain')));
            $vid = trim(Context::get('site_id'));

            if($domain && $vid) unset($vid);
            if(!$domain && $vid) $domain = $vid;

            if(!$user_id) return new Object(-1,'msg_invalid_request');
            if(!$domain) return new Object(-1,'msg_invalid_request');

            $tmp_user_id_list = explode(',',$user_id);
            $user_id_list = array();
            foreach($tmp_user_id_list as $k => $v){
                $v = trim($v);
                if($v) $user_id_list[] = $v;
            }
            if(count($user_id_list)==0) return new Object(-1,'msg_invalid_request');

            // textyle 생성
            $output = $this->insertTextyle($domain, $user_id_list);
            if(!$output->toBool()) return $output;

            $this->add('module_srl', $output->get('module_srl'));
            $this->setMessage('msg_create_textyle');
        }

        function insertTextyle($domain, $user_id_list) {
            if(!is_array($user_id_list)) $user_id_list = array($user_id_list);

            $oAddonAdminController = &getAdminController('addon');
            $oMemberModel = &getModel('member');
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');
            $oRssAdminController = &getAdminController('rss');
            $oTextyleModel = &getModel('textyle');
            $oTextyleController = &getController('textyle');
            $oDocumentController = &getController('document');

            // 관리자 아이디 검사
            $member_srl = $oMemberModel->getMemberSrlByUserID($user_id_list[0]);
            if(!$member_srl) return new Object(-1,'msg_not_user');

            // 관리자의 정보를 구함
            $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);

            // 가상 사이트 생성
            $output = $oModuleController->insertSite($domain, 0);
            if(!$output->toBool()) return $output;
            $site_srl = $output->get('site_srl');

            // textyle 모듈 생성
            $textyle->site_srl = $site_srl;
            $textyle->mid = $this->textyle_mid;
            $textyle->module = 'textyle';
            $textyle->module_srl = getNextSequence();
            $textyle->skin = $this->skin;
            $textyle->browser_title = sprintf("%s's Textyle",$member_info->nick_name);
            $output = $oModuleController->insertModule($textyle);
            if(!$output->toBool()) return $output;
            $module_srl = $output->get('module_srl');

            // 가상사이트에 index_module_srl 업데이트
            $site->site_srl = $site_srl;
            $site->index_module_srl = $module_srl;
            $output = $oModuleController->updateSite($site);

            // 가상 사이트의 관리자 지정
            $output = $oModuleController->insertSiteAdmin($site_srl, $user_id_list);

            // 텍스타일 정보 기록
            $args->textyle_title = $textyle->browser_title;
            $args->module_srl = $module_srl;
            $args->member_srl = $member_srl;
            $args->post_style = $this->post_style;
            $args->post_list_count = $this->post_list_count;
            $args->comment_list_count = $this->comment_list_count;
            $args->guestbook_list_count = $this->guestbook_list_count;
            $args->input_email = $this->input_email;//'R'; // Y, N
            $args->input_website = $this->input_website;//'R'; // Y, N
            $args->post_editor_skin = $this->post_editor_skin;
            $args->post_use_prefix = $this->post_use_prefix;
            $args->post_use_suffix = $this->post_use_suffix;
            $args->comment_editor_skin = 'xpresseditor';
            $args->comment_editor_colorset = 'white';
            $args->guestbook_editor_skin = 'xpresseditor';
            $args->guestbook_editor_colorset = 'white';
            $args->timezone = $GLOBALS['_time_zone'];
            $output = executeQuery('textyle.insertTextyle', $args);
            if(!$output->toBool()) return $output;

            $oTextyleController->updateTextyleCommentEditor($module_srl, $args->comment_editor_skin, $args->comment_editor_colorset);

            //rss 등록
            $output = $oRssAdminController->setRssModuleConfig($module_srl, 'Y', 'Y');
            if(!$output->toBool()) return $output;

            // file upload limit
            // TODO : just site total check
            $file_config->allowed_attach_size = 1024*1024*1024*5;
            $output = $oModuleController->insertModuleConfig('file',$file_config);
            if(!$output->toBool()) return $output;

            //addon 설정
            $oAddonAdminController->doInsert('autolink', $site_srl);
            $oAddonAdminController->doInsert('counter', $site_srl);
            $oAddonAdminController->doInsert('member_communication', $site_srl);
            $oAddonAdminController->doInsert('member_extra_info', $site_srl);
            $oAddonAdminController->doInsert('mobile', $site_srl);
            $oAddonAdminController->doInsert('smartphone', $site_srl);
            $oAddonAdminController->doInsert('referer', $site_srl);
            $oAddonAdminController->doInsert('resize_image', $site_srl);
            $oAddonAdminController->doInsert('blogapi', $site_srl);
            $oAddonAdminController->doActivate('autolink', $site_srl);
            $oAddonAdminController->doActivate('counter', $site_srl);
            $oAddonAdminController->doActivate('member_communication', $site_srl);
            $oAddonAdminController->doActivate('member_extra_info', $site_srl);
            $oAddonAdminController->doActivate('mobile', $site_srl);
            $oAddonAdminController->doActivate('smartphone', $site_srl);
            $oAddonAdminController->doActivate('referer', $site_srl);
            $oAddonAdminController->doActivate('resize_image', $site_srl);
            $oAddonAdminController->doActivate('blogapi', $site_srl);
            $oAddonAdminController->makeCacheFile($site_srl);

            // 기본 에디터 컴포넌트 On
            $oEditorController = &getAdminController('editor');
            $oEditorController->insertComponent('colorpicker_text',true, $info->site_srl);
            $oEditorController->insertComponent('colorpicker_bg',true, $info->site_srl);
            $oEditorController->insertComponent('emoticon',true, $info->site_srl);
            $oEditorController->insertComponent('url_link',true, $info->site_srl);
            $oEditorController->insertComponent('image_link',true, $info->site_srl);
            $oEditorController->insertComponent('multimedia_link',true, $info->site_srl);
            $oEditorController->insertComponent('quotation',true, $info->site_srl);
            $oEditorController->insertComponent('table_maker',true, $info->site_srl);
            $oEditorController->insertComponent('poll_maker',true, $info->site_srl);
            $oEditorController->insertComponent('image_gallery',true, $info->site_srl);


            // set category
            $obj->module_srl = $module_srl;
            $obj->title = Context::getLang('init_category_title');
            $oDocumentController->insertCategory($obj);

            // 기본 스킨 디자인 복사
            FileHandler::copyDir($this->module_path.'skins/'.$textyle->skin, $oTextyleModel->getTextylePath($module_srl));

            // 모듈의 관리자 아이디로 지정
            foreach($user_id_list as $k => $v){
                $output = $oModuleController->insertAdminId($module_srl, $v);
                if(!$output->toBool()) return $output;
            }

            // 첫 글 등록
            $langType = Context::getLangType();
            $file = sprintf('%ssample/%s.html',$this->module_path,$langType);
            if(!file_exists(FileHandler::getRealPath($file))){
                $file = sprintf('%ssample/ko.html',$this->module_path);
            }
            // 소유자 회원정보
            $oMemberModel = &getModel('member');
            $member_info = $oMemberModel->getMemberInfoByUserID($user_id_list[0]);

            $doc->module_srl = $module_srl;
            $doc->title = Context::getLang('sample_title');
            $doc->tags = Context::getLang('sample_tags');
            $doc->content = FileHandler::readFile($file);
            $doc->member_srl = $member_info->member_srl;
            $doc->user_id = $member_info->user_id;
            $doc->user_name = $member_info->user_name;
            $doc->nick_name = $member_info->nick_name;
            $doc->email_address = $member_info->email_address;
            $doc->homepage = $member_info->homepage;
            $oDocumentController->insertDocument($doc, true);

            $output = new Object();
            $output->add('module_srl',$module_srl);
            return $output;
        }

        function procTextyleAdminUpdate(){
            $vars = Context::gets('site_srl','user_id','domain','access_type','vid','module_srl');
            if(!$vars->site_srl) return new Object(-1,'msg_invalid_request');

            if($vars->access_type=='domain') $args->domain = $vars->domain;
            else $args->domain = $vars->vid;
            if(!$args->domain) return new Object(-1,'msg_invalid_request');

            // 관리자 아이디 검사
            $oMemberModel = &getModel('member');

            $tmp_member_list = explode(',',$vars->user_id);
            $admin_list = array();
            foreach($tmp_member_list as $k => $v){
                $v = trim($v);
                if($v){
                    $member_srl = $oMemberModel->getMemberSrlByUserID($v);
                    if($member_srl){
                        $admin_list[] = $v;
                    }else{
                        return new Object(-1,'msg_not_user');
                    }
                }
            }

            $oModuleModel = &getModel('module');
            $site_info = $oModuleModel->getSiteInfo($vars->site_srl);
            if(!$site_info) return new Object(-1,'msg_invalid_request');

            $oModuleController = &getController('module');
            $output = $oModuleController->insertSiteAdmin($vars->site_srl, $admin_list);
            if(!$output->toBool()) return $output;

            // 모듈의 관리자 아이디로 지정
            $oModuleController->deleteAdminId($vars->module_srl);

            foreach($admin_list as $k => $v){
                $output = $oModuleController->insertAdminId($vars->module_srl, $v);
                // TODO : insertAdminId return value
                if(!$output) return new Object(-1,'msg_not_user');
                if(!$output->toBool()) return $output;
            }

            // 도메인 변경
            $args->site_srl = $vars->site_srl;
            $output = $oModuleController->updateSite($args);
            if(!$output->toBool()) return $output;

            $this->setMessage('success_updated');

            $output = new Object();
            $output->add('module_srl',$vars->module_srl);
            return $output;
        }

        function procTextyleAdminDelete() {
            $oModuleController = &getController('module');
            $oCounterController = &getController('counter');
            $oAddonController = &getController('addon');
            $oEditorController = &getController('editor');
            $oTextyleModel = &getModel('textyle');
            $oModuleModel = &getModel('module');

            $site_srl = Context::get('site_srl');
            if(!$site_srl) return new Object(-1,'msg_invalid_request');

            $site_info = $oModuleModel->getSiteInfo($site_srl);
            $module_srl = $site_info->index_module_srl;

            // 원본을 구해온다
            $oTextyle = new TextyleInfo($module_srl);
            if($oTextyle->module_srl != $module_srl) return new Object(-1,'msg_invalid_request');

            // 모듈 삭제
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;

            // 가상 사이트 삭제
            $args->site_srl = $oTextyle->site_srl;
            executeQuery('module.deleteSite', $args);
            executeQuery('module.deleteSiteAdmin', $args);
            executeQuery('member.deleteMemberGroup', $args);
            executeQuery('member.deleteSiteGroup', $args);
            executeQuery('module.deleteLangs', $lang_args);
            $lang_supported = Context::get('lang_supported');
            foreach($lang_supported as $key => $val) {
                $lang_cache_file = _XE_PATH_.'files/cache/lang_defined/'.$args->site_srl.'.'.$key.'.php';
                FileHandler::removeFile($lang_cache_file);
            }
            $oCounterController->deleteSiteCounterLogs($args->site_srl);
            $oAddonController->removeAddonConfig($args->site_srl);
            $oEditorController->removeEditorConfig($args->site_srl);

            // 관련 DB 삭제
            $args->module_srl = $module_srl;
            executeQuery('textyle.deleteTextyle', $args);
            executeQuery('textyle.deleteTextyleFavorites', $args);
            executeQuery('textyle.deleteTextyleTags', $args);
            executeQuery('textyle.deleteTextyleVoteLogs', $args);
            executeQuery('textyle.deleteTextyleMemos', $args);
            executeQuery('textyle.deleteTextyleReferer', $args);
            executeQuery('textyle.deleteTextyleApis', $args);

            // 파일들 삭제
            @FileHandler::removeFile(sprintf("files/cache/textyle/textyle_deny/%d.php",$module_srl));

            FileHandler::removeDir($oTextyleModel->getTextylePath($module_srl));

            $this->add('module','textyle');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }

        function procTextyleAdminInsertCustomMenu() {
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');

            $config = $oModuleModel->getModuleConfig('textyle');
            $second_menus = Context::getLang('textyle_second_menus');

            $args = Context::getRequestVars();
            foreach($args as $key => $val) {
                if(strpos($key, 'hidden_')===false || $val!='Y') continue;
                $k = substr($key, 7);
                if(preg_match('/^([0-9]+)$/', $k)) {
                    $subs = $second_menus[$k];
                    if(count($subs)) {
                        $h = array_keys($subs);
                        for($i=0,$c=count($h);$i<$c;$i++) $hidden_menu[] = strtolower($h[$i]);
                    }
                }
                $hidden_menu[] = $k;
            }

            $config->hidden_menu = $hidden_menu;

            if(!$config->attached_menu || !is_array($config->attached_menu)) $config->attached_menu = array();

            $attached = array();
            foreach($args as $key => $val) {
                if(strpos($key, 'custom_act_')!==false && $val) {
                    $idx = substr($key, 11);
                    $attached[$idx]->act = $val;
                } elseif(strpos($key, 'custom_name_')!==false && $val) {
                    $idx = substr($key, 12);
                    $attached[$idx]->name = $val;
                }
            }

            if(count($attached)) {
                foreach($attached as $key => $val) {
                    if(!$val->act || !$val->name) continue;
                    $config->attached_menu[$key][$val->act] = $val->name;
                }
            }

            foreach($args as $key => $val) {
                if(strpos($key, 'delete_')===false || $val!='Y') continue;
                $delete_menu[] = substr($key, 7);
            }

            if(count($delete_menu)) {
                foreach($config->attached_menu as $key => $val) {
                    if(!count($val)) continue;
                    foreach($val as $k => $v) {
                        if(in_array(strtolower($k), $delete_menu)) unset($config->attached_menu[$key][$k]);
                    }
                }
            }
            $oModuleController->insertModuleConfig('textyle', $config);
        }
    }
?>
