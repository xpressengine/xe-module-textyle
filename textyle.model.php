<?php
    /**
     * @class  textyleModel
     * @author sol (sol@ngleader.com)
     * @brief  textyle 모듈의 Model class
     **/

    class textyleModel extends textyle {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief Textyle 기본 설정 return
         **/
        function getTextyleConfig() {
            static $module_info = null;
            if(is_null($module_info)) {
                // module module_info의 값을 구함
                $oModuleModel = &getModel('module');
                $module_info = $oModuleModel->getModuleConfig('textyle');

                $skin_info->module_srl = $module_info->module_srl;
                $oModuleModel->syncSkinInfoToModuleInfo($skin_info);

                // textyle dummy module의 is_default 값을 구함
                $dummy = $oModuleModel->getModuleInfoByMid($module_info->mid);
                $module_info->is_default = $dummy->is_default;
                $module_info->module_srl = $dummy->module_srl;
                $module_info->browser_title = $dummy->browser_title;
                $module_info->layout_srl = $dummy->layout_srl;

                if(count($skin_info)) foreach($skin_info as $key => $val) $module_info->{$key} = $val;

                unset($module_info->grants);
            }
            return $module_info;
        }

        /**
         * @brief 특정 회원의 Textyle 정보 얻기
         * 회원 번호를 입력하지 않으면 현재 로그인 사용자의 Textyle 정보를 구함
         **/
        function getMemberTextyle($member_srl = 0) {
            if(!$member_srl && !Context::get('is_logged')) return new TextyleInfo();

            if(!$member_srl) {
                $logged_info = Context::get('logged_info');
                $args->member_srl = $logged_info->member_srl;
            } else {
                $args->member_srl = $member_srl;
            }

            $output = executeQuery('textyle.getMemberTextyle', $args);
            if(!$output->toBool() || !$output->data) return new TextyleInfo();

            $textyle = $output->data;

            $oTextyle = new TextyleInfo();
            $oTextyle->setAttribute($textyle);

            return $oTextyle;
        }

        /**
         * @brief Textyle 목록 return
         **/
        function getTextyleList($list_count=20, $page=1) {
            $args->list_count = $list_count;
            $args->page = $page;
            $output = executeQueryArray('textyle.getTextyleList', $args);
            if(!$output->toBool()) return $output;

            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    $oTextyle = null;
                    $oTextyle = new TextyleInfo();
                    $oTextyle->setAttribute($val);
                    $output->data[$key] = null;
                    $output->data[$key] = $oTextyle;
                }
            }
            return $output;
        }

        /**
         * @brief Textyle 개별 정보 return
         **/
        function getTextyle($module_srl=0) {
            return new TextyleInfo($module_srl);
        }

        /**
         * @brief 특정 회원의 Textyle 생성 개수 return
         **/
        function getTextyleCount($member_srl = null) {
            if(!$member_srl) {
                $logged_info = Context::get('logged_info');
                $member_srl = $logged_info->member_srl;
            }
            if(!$member_srl) return null;

            $args->member_srl = $member_srl;
            $output = executeQuery('textyle.getTextyleCount',$args);
            return $output->data->count;
        }

		function getTextyleGuestbookList($vars){

			$logged_info = Context::get('logged_info');

            $oMemberModel = &getModel('member');
            $oTextyleController = &getController('textyle');

			$args->module_srl = $vars->module_srl;
			$args->page = $vars->page;
			$args->list_count = $vars->list_count;
			if($vars->search_text) $args->content_search = $vars->search_text;
			$output = executeQueryArray('textyle.getTextyleGuestbookList',$args);
            if(!$output->toBool() || !$output->data) return array();

            foreach($output->data as $key => $val) {

				if($logged_info->is_site_admin || $val->is_secret!=1 || $val->member_srl == $logged_info->member_srl || $val->view_grant || $_SESSION['own_textyle_guestbook'][$val->textyle_guestbook_srl]){
					$val->view_grant = true;
					$oTextyleController->addGuestbookGrant($val->textyle_guestbook_srl);

					foreach($output->data as $k => $v) {
						if($v->parent_srl == $val->textyle_guestbook_srl){
							$v->view_grant=true;
						}
					}

				}else{
					$val->view_grant = false;
				}

                $profile_info = $oMemberModel->getProfileImage($val->member_srl);
                if($profile_info) $output->data[$key]->profile_image = $profile_info->src;
            }
			return $output;	
		}

		function getTextyleGuestbook($textyle_guestbook_srl){
            $oMemberModel = &getModel('member');

			$args->textyle_guestbook_srl = $textyle_guestbook_srl;
			$output = executeQueryArray('textyle.getTextyleGuestbook',$args);
			if($output->data){
				foreach($output->data as $key => $val) {
					if(!$val->member_srl) continue;
					$profile_info = $oMemberModel->getProfileImage($val->member_srl);
					if($profile_info) $output->data[$key]->profile_image = $profile_info->src;
				}
			}
			return $output;	
		}

		function getDenyCacheFile($module_srl){
            return sprintf("files/cache/textyle/textyle_deny/%d.php",$module_srl);
		}

		function getTextyleDenyList($module_srl){
			$args->module_srl = $this->module_srl;
			$cache_file = $this->getDenyCacheFile($module_srl);
			
			if($GlOBALS['XE_TEXTYLE_DENY_LIST'] && is_array($GLOBALS['XE_TEXTYLE_DENY_LIST'])){
				return $GLOBALS['XE_TEXTYLE_DENY_LIST'];
			}

			if(!file_exists($cache_file)) {
				$_textyle_deny = array();
				$buff = '<?php if(!defined("__ZBXE__")) exit(); $_textyle_deny=array();';

				$output = executeQueryArray('textyle.getTextyleDeny',$args);
				if(count($output->data) > 0){
					foreach($output->data as $k => $v){
						$_textyle_deny[$v->deny_type][$v->textyle_deny_srl] = $v->deny_content;
						$buff .= sprintf('$_textyle_deny[\'%s\'][%d]="%s";',$v->deny_type,$v->textyle_deny_srl,$v->deny_content);
					}
				}
				$buff .= '?>';
				
				if(!is_dir(dirname($cache_file))) FileHandler::makeDir(dirname($cache_file));
				FileHandler::writeFile($cache_file, $buff);
			}else{
				@include($cache_file);
			}
			$GLOBALS['XE_TEXTYLE_DENY_LIST'] = $_textyle_deny;
			return $GLOBALS['XE_TEXTYLE_DENY_LIST'];
		}

		function _checkDeny($module_srl,$type,$deny_content){
			$deny_content = trim($deny_content);
			if(strlen($deny_content) == 0) return false;

			$deny_list = $this->getTextyleDenyList($module_srl);

			if(!is_array($deny_list)) return false;
			if(!is_array($deny_list[$type])) return false;
			if(count($deny_list[$type])==0) return false;
			if(!in_array($deny_content,$deny_list[$type])) return false;
			return true;
		}

		function checkDenyIP($module_srl,$ip){
			$ip = trim($ip);
			if(!$ip) return false;
			return $this->_checkDeny($module_srl,'I',$ip);
		}

		function checkDenyEmail($module_srl,$email){
			$email = trim($email);
			if(!$email) return false;
			return $this->_checkDeny($module_srl,'M',$email);
		}

		function checkDenyUserName($module_srl,$user_name){
			$user_name = trim($user_name);
			if(!$user_name) return false;
			if(is_array($user_name)){
				foreach($user_name as $k => $v){
					if(!$this->_checkDeny($module_srl,'N',$v)) return false;
				}
				return true;
			}else{
				return $this->_checkDeny($module_srl,'N',$user_name);
			}
		}

		function checkDenySite($module_srl,$site){
			$site = trim($site);
			if(!$site) return false;

			return $this->_checkDeny($module_srl,'S',$site);
		}

		function getSubscription($args){
            $output = executeQueryArray('textyle.getTextyleSubscription', $args);
			//$output->add('date',$publish_date);
			return $output;
		}

		function getSubscriptionMinPublishDate($module_srl){
			$args->module_srl = $module_srl;
            $output = executeQuery('textyle.getTextyleSubscriptionMinPublishDate', $args);
			return $output;
		}

		function getSubscriptionByDocumentSrl($document_srl){
			$args->document_srl = $document_srl;
			$output = executeQueryArray('textyle.getTextyleSubscriptionByDocumentSrl',$args);
			return $output;
		}

        /**
         * @brief Textyle 이미지 유무 체크후 경로 return
         **/
        function getTextylePhotoSrc($member_srl) {
            $oMemberModel = &getModel('member');
            $info = $oMemberModel->getProfileImage($member_srl);
            $filename = $info->file;
            if(!file_exists($filename)) return $this->getTextyleDefaultPhotoSrc();
            return $info->src;
		}

		function getTextyleDefaultPhotoSrc(){
			return sprintf("%s%s%s", Context::getRequestUri(), $this->module_path, 'tpl/img/iconNoProfile.gif');
		}

        function getTextyleFaviconPath($module_srl) {
            return sprintf('files/attach/textyle/favicon/%s', getNumberingPath($module_srl,3));
        }

        function getTextyleFaviconSrc($module_srl) {
			$path = $this->getTextyleFaviconPath($module_srl);
            $filename = sprintf('%sfavicon.ico', $path);
            if(!is_dir($path) || !file_exists($filename)) return $this->getTextyleDefaultFaviconSrc();

            return Context::getRequestUri().$filename."?rnd=".filemtime($filename);
		}

		function getTextyleDefaultFaviconSrc(){
			return sprintf("%s%s", Context::getRequestUri(), 'modules/textyle/tpl/img/favicon.ico');
		}

		function getTextyleSupporterList($module_srl,$YYYYMM="",$sort_index="total_count"){
            $oMemberModel = &getModel('member');
			$args->module_srl = $module_srl;
			$args->sort_index = $sort_index;
            $args->list_count = $list_count;
            $args->page = $page;
			$args->regdate = $YYYYMM ? $YYYYMM : date('Ym');
            $output = executeQueryArray('textyle.getTextyleSupporterList', $args);
            if($output->data) {
                 foreach($output->data as $key => $val) {
                      if(!$val->member_srl) continue;
                      $img = $oMemberModel->getProfileImage($val->member_srl);
                      if($img) $output->data[$key]->profile_image = $img->src;
                 }
            }
			return $output;
		}
        function getTextylePath($module_srl) {
            return sprintf("files/attach/textyle/%s",getNumberingPath($module_srl));
		}

        function checkTextylePath($module_srl, $skin = null) {
            $path = $this->getTextylePath($module_srl);
			if(!file_exists($path)){
				$oTextyleController = &getController('textyle');
				$oTextyleController->resetSkin($module_srl, $skin);
			}
			return true;
        }

        function getTextyleUserHTMLFile($module_srl) {
            return sprintf("%stextyle.html", $this->getTextylePath($module_srl));
        }

        function getTextyleUserCSSFile($module_srl) {
            return sprintf("%stextyle.css", $this->getTextylePath($module_srl));
        }

        function getTextyleUserHTML($module_srl) {
            $filename = $this->getTextyleUserHTMLFile($module_srl);
            return FileHandler::readFile($filename);
        }

        function getTextyleUserCSS($module_srl) {
            $filename = $this->getTextyleUserCSSFile($module_srl);
            return FileHandler::readFile($filename);
        }

        function getTextyleSiteInfo() {
            require_once($this->module_path.'libs/metaweblog.class.php');

            $oTextyleController = &getController('textyle');
            $blogapi_site_url = Context::get('blogapi_site_url');
            if(!$blogapi_site_url) return new Object(-1,'msg_invalid_request');
            if(!preg_match('/^(http|https)/',$blogapi_site_url)) $blogapi_site_url = 'http://'.$blogapi_site_url;

            $oMeta = new metaWebLog($blogapi_site_url);
            $site_info = $oMeta->getSiteInfo();
            if(!$site_info) return new Object(-1,'msg_url_is_invalid');

            $this->add('site_url', $blogapi_site_url);
            $this->add('title', $site_info->title);
            $this->add('blogapi_url', $site_info->blogapi_url);
        }
   }
?>
