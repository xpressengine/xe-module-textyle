<?php
    /**
     * @class  textyleController
     * @author sol (sol@ngleader.com)
     * @brief  textyle 모듈의 Controller class
     **/

    class textyleController extends textyle {

        /**
         * @brief 초기화
         **/
		function init() {
            $oTextyleModel = &getModel('textyle');
            $oModuleModel = &getModel('module');

            if(!$this->module_srl) {
                $site_module_info = Context::get('site_module_info');
                $site_srl = $site_module_info->site_srl;
                if($site_srl) {
                    $this->module_srl = $site_module_info->index_module_srl;
                    $this->module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
                    Context::set('module_info',$this->module_info);
                    Context::set('mid',$this->module_info->mid);
                    Context::set('current_module_info',$this->module_info);
                }
            }

			$this->textyle = $oTextyleModel->getTextyle($this->module_srl);
            $this->site_srl = $this->textyle->site_srl;
			Context::set('textyle',$this->textyle); 

			// deny
			if(!$this->grant->manager){
				$vars = Context::gets('user_name','user_id','homepage','email_address');

				$deny = $oTextyleModel->checkDenyIP($this->module_srl,$_SERVER['REMOTE_ADDR']);
				if($deny) $this->stop('msg_not_permitted');

				$deny = $oTextyleModel->checkDenyUserName($this->module_srl,$vars->user_id);
				if($deny) $this->stop('msg_not_permitted');

				$deny = $oTextyleModel->checkDenyUserName($this->module_srl,$vars->user_name);
				if($deny) $this->stop('msg_not_permitted');

				$deny = $oTextyleModel->checkDenyEmail($this->module_srl,$vars->email_address);
				if($deny) $this->stop('msg_not_permitted');

				$deny = $oTextyleModel->checkDenySite($this->module_srl,$vars->homepage);
				if($deny) $this->stop('msg_not_permitted');
			}
		}

		function procTextyleConfigCommunicationInsert(){
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            // textyle 정보 업데이트
            $args = Context::getRequestVars();
			$args->module_srl = $this->module_srl;
			$output = $this->updateTextyle($args);
            if(!$output->toBool()) return $output;

            // RSS 정보등록
			$oRssAdminController = &getAdminController('rss');
            $open_rss = Context::get('rss_type');
			$output = $oRssAdminController->setRssModuleConfig($this->module_srl, $open_rss, 'Y');
            if(!$output->toBool()) return $output;

            // 댓글 에디터 설정
            $this->updateTextyleCommentEditor($this->module_srl, $args->comment_editor_skin, $args->comment_editor_colorset);

            // 미투발행/트위터 설정
            $config = $oModuleModel->getModulePartConfig('textyle', $this->module_srl);
            $config->enable_me2day = $args->enable_me2day=='Y'?'Y':'N';
            $config->me2day_userid = $args->me2day_userid;
            $config->me2day_userkey = $args->me2day_userkey;
            $config->enable_twitter = $args->enable_twitter=='Y'?'Y':'N';
            $config->twitter_userid = $args->twitter_userid;
            $config->twitter_password = $args->twitter_password;

            // 댓글/방명록 권한
            $config->comment_grant = (int)$args->comment_grant;
            $config->guestbook_grant = (int)$args->guestbook_grant;
            $oModuleController->insertModulePartConfig('textyle',$this->module_srl, $config);
		}

        function procTextyleLogin() {
            $oMemberController = &getController('member');

            // 변수 정리
            if(!$user_id) $user_id = Context::get('user_id');
            $user_id = trim($user_id);

            if(!$password) $password = Context::get('password');
            $password = trim($password);

            if(!$keep_signed) $keep_signed = Context::get('keep_signed');

            $stat = 0;

            // 아이디나 비밀번호가 없을때 오류 return
            if(!$user_id) {
                $stat = -1;
                $msg = Context::getLang('null_user_id');
            }
            if(!$password) {
                $stat = -1;
                $msg = Context::getLang('null_password');
            }

            if(!$stat) {
                $output = $oMemberController->doLogin($user_id, $password, $keep_signed=='Y'?true:false);
                if(!$output->toBool()) {
                    $stat = -1;
                    $msg = $output->getMessage();
                }
            }

            $this->add('stat',$stat);
            $this->setMessage($msg);
        }

        function procTextyleCheckMe2day() {
            require_once($this->module_path.'libs/me2day.api.php');
            $vars = Context::gets('me2day_userid','me2day_userkey');

            $oMe2 = new me2api($vars->me2day_userid, $vars->me2day_userkey);
            $output = $oMe2->chkNoop($vars->me2day_userid, $vars->me2day_userkey);
            if($output->toBool()) return new Object(-1,'msg_success_to_me2day');
            return new Object(-1,'msg_fail_to_me2day');
        }

        function updateTextyleCommentEditor($module_srl, $comment_editor_skin, $comment_editor_colorset) {
            $oEditorModel = &getModel('editor');
            $oModuleController = &getController('module');

            $editor_config = $oEditorModel->getEditorConfig($module_srl);

            $editor_config->editor_skin = 'dreditor';
            $editor_config->content_style = 'default';
            $editor_config->content_font = null;
            $editor_config->comment_editor_skin = $comment_editor_skin;
            $editor_config->sel_editor_colorset = null;
            $editor_config->sel_comment_editor_colorset = $comment_editor_colorset;
            $editor_config->enable_html_grant = array(1);
            $editor_config->enable_comment_html_grant = array(1);
            $editor_config->upload_file_grant = array(1);
            $editor_config->comment_upload_file_grant = array(1);
            $editor_config->enable_default_component_grant = array(1);
            $editor_config->enable_comment_default_component_grant = array(1);
            $editor_config->enable_component_grant = array(1);
            $editor_config->enable_comment_component_grant = array(1);
            $editor_config->editor_height = 500;
            $editor_config->comment_editor_height = 100;
            $editor_config->enable_autosave = 'N';
            $oModuleController->insertModulePartConfig('editor',$module_srl,$editor_config);
        }

		function updateTextyle($args){
			$output = executeQuery('textyle.updateTextyle', $args);
            return $output;
		}

		function procTextyleProfileUpdate(){
			$oMemberController = &getController('member');

			// nickname, email
            $args->member_srl = $this->textyle->member_srl;
            $args->nick_name = Context::get('nick_name');
            $args->email_address = Context::get('email_address');
			$output = $oMemberController->updateMember($args);
            if(!$output->toBool()) return $output;

			// 자기 소개글
            $tex->module_srl = $this->module_srl; 
            $tex->profile_content = Context::get('profile_content');
			$output = $this->updateTextyleInfo($this->module_srl,$tex);
            if(!$output->toBool()) return $output;
			
			// 사진 삭제
            if(Context::get('delete_photo')=='Y') {
                $this->deleteTextylePhoto($this->module_srl);
            }
		}

        function procTextyleProfileImageUpload() {
            $oMemberController = &getController('member');

            $photo = Context::get('photo');
            if($this->textyle && Context::isUploaded() && is_uploaded_file($photo['tmp_name'])) {
                $oMemberController->insertProfileImage($this->textyle->member_srl, $photo['tmp_name']);
            }

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('move_mytextyle');
        }

		function deleteTextylePhoto($module_srl){
            $oMemberController = &getController('member');
            Context::set('member_srl', $this->textyle->member_srl);
            $output = $oMemberController->procMemberDeleteProfileImage();
		}

		function updateTextyleInfo($module_srl,$args){
			$args->module_srl = $module_srl;
			$output = executeQuery('textyle.updateTextyle', $args);
			return $output;
		}

		function procTextyleInfoUpdate(){
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');
            $oTextyleModel = &getModel('textyle');

            // 텍스타일 정보 수정
			$val = Context::gets('textyle_title','textyle_content','timezone');
			$output = $this->updateTextyleInfo($this->module_srl,$val);
            if(!$output->toBool()) return $output;

            // 모듈정보의 browser_title 수정
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
            $module_info->browser_title = $val->textyle_title;
            $output = $oModuleController->updateModule($module_info);
            if(!$output->toBool()) return $output;

            // 언어 변경
            $args->index_module_srl = $this->module_srl;
            $args->default_language = Context::get('language');
			$args->site_srl = $this->site_srl;
            $output = $oModuleController->updateSite($args);
            if(!$output->toBool()) return $output;

            // favicon 이미지 제거
            if(Context::get('delete_icon')=='Y') $this->deleteTextyleFavicon($this->module_srl);

            // favicon 등록
            $favicon = Context::get('favicon'); 
            if(Context::isUploaded()&&is_uploaded_file($favicon['tmp_name'])) $this->insertTextyleFavicon($this->module_srl,$favicon['tmp_name']);

			$this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('move_mytextyle');
		}	

		function insertTextyleFavicon($module_srl, $source) {
            $oTextyleModel = &getModel('textyle');
            $path = $oTextyleModel->getTextyleFaviconPath($module_srl);
            if(!is_dir($path)) FileHandler::makeDir($path);
			$filename = sprintf('%sfavicon.ico', $path);
			move_uploaded_file($source, $filename);
		}

		function deleteTextyleFavicon($module_srl){
			$oTextyleModel = &getModel('textyle');
			$path = $oTextyleModel->getTextyleFaviconPath($module_srl);
			$filename = sprintf('%s/favicon.ico', $path);
			FileHandler::removeFile($filename);
		}

        /**
         * @brief comment insert
         **/
        function procTextyleInsertComment() {
            $oDocumentModel = &getModel('document');
            $oCommentModel = &getModel('comment');
            $oCommentController = &getController('comment');

            // 권한 체크
            if(!$this->grant->write_comment) return new Object(-1, 'msg_not_permitted');

            // 댓글 입력에 필요한 데이터 추출
            $obj = Context::gets('document_srl','comment_srl','parent_srl','content','password','nick_name','member_srl','email_address','homepage','is_secret','notify_message');
            $obj->module_srl = $this->module_srl;

            // 원글이 존재하는지 체크
            $oDocument = $oDocumentModel->getDocument($obj->document_srl);
            if(!$oDocument->isExists()) return new Object(-1,'msg_not_permitted');

            // comment_srl이 존재하는지 체크
            // 만일 comment_srl이 n/a라면 getNextSequence()로 값을 얻어온다.
            if(!$obj->comment_srl) $obj->comment_srl = getNextSequence();
            else $comment = $oCommentModel->getComment($obj->comment_srl, $this->grant->manager);

            // comment_srl이 없을 경우 신규 입력
            if($comment->comment_srl != $obj->comment_srl) {
                // parent_srl이 있으면 답변으로
                if($obj->parent_srl) {
                    $parent_comment = $oCommentModel->getComment($obj->parent_srl);
                    if(!$parent_comment->comment_srl) return new Object(-1, 'msg_invalid_request');

                    $output = $oCommentController->insertComment($obj);

                // 없으면 신규
                } else {
                    $output = $oCommentController->insertComment($obj);
                }

                // 문제가 없고 모듈 설정에 관리자 메일이 등록되어 있으면 메일 발송
                if($output->toBool() && $this->module_info->admin_mail) {
                    $oMail = new Mail();
                    $oMail->setTitle($oDocument->getTitleText());
                    $oMail->setContent( sprintf("From : <a href=\"%s#comment_%d\">%s#comment_%d</a><br/>\r\n%s", $oDocument->getPermanentUrl(), $obj->comment_srl, $oDocument->getPermanentUrl(), $obj->comment_srl, $obj->content));
                    $oMail->setSender($obj->nick_name, $obj->email_address);

                    $target_mail = explode(',',$this->module_info->admin_mail);
                    for($i=0;$i<count($target_mail);$i++) {
                        $email_address = trim($target_mail[$i]);
                        if(!$email_address) continue;
                        $oMail->setReceiptor($email_address, $email_address);
                        $oMail->send();
                    }
                }

            // comment_srl이 있으면 수정으로
            } else {
                $obj->parent_srl = $comment->parent_srl;
                $output = $oCommentController->updateComment($obj, $this->grant->manager);
                $comment_srl = $obj->comment_srl;
            }
            if(!$output->toBool()) return $output;

            $this->setMessage('success_registed');
            $this->add('mid', Context::get('mid'));
            $this->add('document_srl', $obj->document_srl);
            $this->add('comment_srl', $obj->comment_srl);
        }

        /**
         * @brief 코멘트 삭제
         **/
        function procTextyleDeleteComment() {
            $oCommentController = &getController('comment');
	
            $password = Context::get('password');
			if($password){
				$oBoardController = &getController('board');	
				$output = $oBoardController->procBoardVerificationPassword();
				if($output) return $output;
			}
            // 댓글 번호 확인
            $comment_srl = Context::get('comment_srl');
            if(!$comment_srl) return $this->doError('msg_invalid_request');

            $output = $oCommentController->deleteComment($comment_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
        }


        function procTextyleGuestbookVerificationPassword() {
			$oTextyleModel = &getModel('textyle');
            $password = Context::get('password');
            $textyle_guestbook_srl = Context::get('textyle_guestbook_srl');

			if(!$password || !$textyle_guestbook_srl) return new Object(-1, 'msg_invalid_request');

			$output = $oTextyleModel->getTextyleGuestbook($textyle_guestbook_srl);
			if($output->data){
				if($output->data[0]->password == md5($password)){
					$this->addGuestbookGrant($textyle_guestbook_srl);
				}else{
					return new Object(-1, 'msg_invalid_password');
				}
			}else{
				return new Object(-1, 'msg_invalid_request');
			}
		}

		function addGuestbookGrant($textyle_guestbook_srl){
			$_SESSION['own_textyle_guestbook'][$textyle_guestbook_srl]=true;
		}


		/**
         * @brief Guestbook insert
		 **/
		function procTextyleGuestbookWrite(){
			$val = Context::gets('mid','nick_name','homepage','email_address','password','content','parent_srl','textyle_guestbook_srl','page','is_secret');

			// set
			$obj->module_srl = $this->module_srl;
			$obj->content = $val->content;
			$obj->is_secret = $val->is_secret == 'Y' ?1:-1;


			// update
			if($val->textyle_guestbook_srl>0){
				$obj->user_name = $obj->nick_name = $val->nick_name;
				$obj->email_address = $val->email_address;
				$obj->homepage = $obj->homepage;
				$obj->password = md5($val->password);

				$obj->textyle_guestbook_srl = $val->textyle_guestbook_srl;
				$output = executeQuery('textyle.updateTextyleGuestbook', $obj);

			// insert
			}else{
				// if logined
				if(Context::get('is_logged')) {
					$logged_info = Context::get('logged_info');
					$obj->member_srl = $logged_info->member_srl;
					$obj->user_id = $logged_info->user_id;
					$obj->user_name = $logged_info->user_name;
					$obj->nick_name = $logged_info->nick_name;
					$obj->email_address = $logged_info->email_address;
					$obj->homepage = $logged_info->homepage;
				}else{
					$obj->user_name = $obj->nick_name = $val->nick_name;
					$obj->email_address = $val->email_address;
					$obj->homepage = $obj->homepage;
					$obj->password = md5($val->password);
				}

				$obj->textyle_guestbook_srl = getNextSequence();
				// reply
				if($val->parent_srl>0){
					$obj->parent_srl = $val->parent_srl;
					$obj->list_order = $obj->parent_srl * -1;
				}else{
					$obj->list_order = $obj->textyle_guestbook_srl * -1;
				}
				$output = executeQuery('textyle.insertTextyleGuestbook', $obj);
			}
            if(!$output->toBool()) return $output;

			$this->addGuestbookGrant($obj->textyle_guestbook_srl);
			$obj->guestbook_count = 1;
			$output = $this->updateTextyleSupporter($obj);
			$this->add('page',$val->page?$val->page:1);
		}	

		/**
         * @brief Guestbook item delete
		 **/
		function procTextyleGuestbookItemDelete(){
			$textyle_guestbook_srl = Context::get('textyle_guestbook_srl');
			if(!$textyle_guestbook_srl) return new Object(-1,'msg_invalid_request');

			$logged_info = Context::get('logged_info');  
			if(!($logged_info->is_site_admin || $_SESSION['own_textyle_guestbook'][$val->textyle_guestbook_srl])) return new Object(-1,'msg_not_permitted');
			$output = $this->deleteGuestbookItem($textyle_guestbook_srl);
			return $output;
		}

		/**
         * @brief Guestbook items delete
		 **/
		function procTextyleGuestbookItemsDelete(){
            $oTextyleModel = &getModel('textyle');

			$textyle_guestbook_srl = Context::get('textyle_guestbook_srl');
			if(!$textyle_guestbook_srl) return new Object(-1,'msg_invalid_request');

			$textyle_guestbook_srl = explode(',',trim($textyle_guestbook_srl));
			rsort($textyle_guestbook_srl);
			if(count($textyle_guestbook_srl)<1) return new Object(-1,'msg_invalid_request');

			foreach($textyle_guestbook_srl as $k => $srl){
				$output = $this->deleteGuestbookItem($srl);
				if(!$output->toBool()) return $output;
			}
		}

		function deleteGuestbookItem($textyle_guestbook_srl){
            $oTextyleModel = &getModel('textyle');
            $output = $oTextyleModel->getTextyleGuestbook($textyle_guestbook_srl);
            $oGuest = $output->data;

			if(!$oGuest) return new Object(-1,'msg_invalid_request');

			// delete children	
			$pobj->parent_srl = $textyle_guestbook_srl;
			$output = executeQueryArray('textyle.getTextyleGuestbook', $pobj);
            if($output->data){
				foreach($output->data as $k=>$v){
					$poutput = $this->deleteGuestbookItem($v->textyle_guestbook_srl);
					if(!$poutput->toBool()) return $poutput;
				}
			}


			$obj->textyle_guestbook_srl = $textyle_guestbook_srl;
			$output = executeQuery('textyle.deleteTextyleGuestbookItem', $obj);
            if(!$output->toBool()) return $output;
	
            if($oGuest->textyle_guestbook_srl) {
                $obj->module_srl = $oGuest->module_srl;
                $obj->member_srl = $oGuest->member_srl;
                $obj->nick_name = $oGuest->nick_name;
                $obj->homepage = $oGuest->homepage;
                $obj->guestbook_count = -1;
                $output = $this->updateTextyleSupporter($obj);
            }
			return $output;
		}


		/**
         * @brief Guestbook secret on/off
		 **/
		function procTextyleGuestbookItemsChangeSecret(){
			$s_args = Context::getRequestVars();
			$textyle_guestbook_srl = $s_args->textyle_guestbook_srl;

            if(preg_match('/^([0-9,]+)$/',$textyle_guestbook_srl)) $textyle_guestbook_srl = explode(',',$textyle_guestbook_srl);
			else $textyle_guestbook_srl = array($textyle_guestbook_srl);
			if(count($textyle_guestbook_srl)<1) return new Object(-1,'error');

			$args->textyle_guestbook_srl = join(',',$textyle_guestbook_srl);
			$output = executeQuery('textyle.updateTextyleGuestbookItemsChangeSecret', $args);
            if(!$output->toBool()) return $output;
		}

		/**
         * @brief Comment item delete
		 **/
		function procTextyleCommentItemDelete(){
			$comment_srl = Context::get('comment_srl');

			if($comment_srl<1) return new Object(-1,'error');
			$comment_srl = explode(',',trim($comment_srl));
			if(count($comment_srl)<1) return new Object(-1,'msg_invalid_request');

            // comment 모듈의 controller 객체 생성
            $oCommentController = &getController('comment');

			for($i=0,$c=count($comment_srl);$i<$c;$i++){
				$output = $oCommentController->deleteComment($comment_srl[$i], $this->grant->manager);
				if(!$output->toBool()) return $output;
			}

            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
		}

		function procTextyleCommentItemSetSecret(){
			$is_secret = Context::get('is_secret');
			$args->is_secret = $is_secret =='Y' ? 'Y' : 'N';

			$args->comment_srl = Context::get('comment_srl');
			$oCommentController = &getController('comment');
			$output = $oCommentController->updateComment($args, $this->grant->manager);
            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
		}

		/**
         * @brief Trackback item delete
		 **/
		function procTextyleTrackbackItemDelete(){
			$trackback_srl = Context::get('trackback_srl');

			if($trackback_srl<1) return new Object(-1,'error');
			$trackback_srl = explode(',',trim($trackback_srl));
			if(count($trackback_srl)<1) return new Object(-1,'msg_invalid_request');

            // comment 모듈의 controller 객체 생성
            $oTrackbackController = &getController('trackback');

			for($i=0,$c=count($trackback_srl);$i<$c;$i++){
				$output = $oTrackbackController->deleteTrackback($trackback_srl[$i], $this->grant->manager);
				if(!$output->toBool()) return $output;
			}

            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
		}

		/**
         * @brief deny insert
		 **/
		function procTextyleDenyInsert(){
			$var = Context::getRequestVars();
			if(!$var->deny_type || !$var->deny_content) return new Object(-1,'msg_invalid_request');

			$args->module_srl = $this->module_srl;
			$args->deny_type = $var->deny_type;
			$args->deny_content = $var->deny_content;
			return $this->insertDeny($args);
		}

		/**
         * @brief deny insert
		 **/
		function procTextyleDenyInsertList(){
			$var = Context::getRequestVars();
			$deny = array();
			$deny['S'] = explode('|',$var->homepage);
			$deny['M'] = explode('|',$var->email_address);
			$deny['I'] = explode('|',$var->ipaddress);
			$deny['N'] = explode('|',$var->user_name);
			
			$i=0;
			foreach($deny as $type => $contents){
				foreach($contents as $k => $content){
					if(!trim($content)) continue;
					unset($args);
					$args->textyle_deny_srl = getNextSequence();
					$args->module_srl = $this->module_srl;
					$args->deny_type = $type;
					$args->deny_content = trim($content);

					$output = $this->insertDeny($args);
					if(!$output->toBool()) return $output;
					$i++;
				}
			}
			
			return $output;
		}

		function insertDeny($obj){
			$oTextyleModel = &getModel('textyle');
			$check = $oTextyleModel->_checkDeny($obj->module_srl,$obj->deny_type,$obj->deny_content);
			if($check) return new Object();

			$this->deleteTextyleDenyFile($obj->module_srl);
			$args->textyle_deny_srl = getNextSequence();
			$args->module_srl = $obj->module_srl;
			$args->deny_type = $obj->deny_type;
			$args->deny_content = $obj->deny_content;
			$output = executeQuery('textyle.insertTextyleDeny', $args);
            return $output;
		}

		/**
         * @brief deny delete
		 **/
		function procTextyleDenyDelete(){
			$s_args = Context::getRequestVars();
			if(!$s_args->textyle_deny_srl) return new Object(-1,'msg_invalid_request');
			$this->deleteTextyleDenyFile($this->module_srl);
			$args->textyle_deny_srl = $s_args->textyle_deny_srl;
			$output = executeQuery('textyle.deleteTextyleDeny', $args);
            if(!$output->toBool()) return $output;
		}

		function deleteTextyleDenyFile($module_srl){
			$oTextyleModel = &getModel('textyle');
			$cache_file = $oTextyleModel->getDenyCacheFile($module_srl);
			FileHandler::removeFile($cache_file);
		}

		function procTextylePostwrite(){
			$var = Context::getRequestVars();
			$var->source_category_srl = Context::get('category_srl');
			if($var->temp_save =='Y'){
				$logged_info = Context::get('logged_info');
				$var->module_srl = $logged_info->member_srl;
			}else{
				$var->module_srl = $this->module_srl;
			}
			$var->this_module_srl = $this->module_srl;


            $oDocumentModel = &getModel('document');
            $oDocument = $oDocumentModel->getDocument($var->document_srl);
            if($oDocument->isExists()) $output = $this->updatePost($var);
            else {
                unset($GLOBALS['XE_DOCUMENT_LIST'][$var->document_srl]);
                $output = $this->insertPost($var);
            }

			$this->add('mid', Context::get('mid'));
            $this->add('document_srl', $output->get('document_srl'));

			if($var->temp_save =='Y'){
				$this->add('temp_save', 'Y');
	            $this->setMessage('success_temp_saved');
			}else{
				$this->add('temp_save', 'N');
	            $this->setMessage('success_registed');
			}
		}

		function insertPost($args,$manaul_inserted=false){
			// insert document
			$oDocumentController = &getController('document');
			$oDocumentModel = &getModel('document');
			$output = $oDocumentController->insertDocument($args,$manual_inserted);
			if(!$output->toBool()) return $output;

			$document_srl = $output->get('document_srl');

			// 임시저장일때는 category_srl을 다시 업데이트.
			if($args->temp_save=='Y'){
				$var->category_srl = $args->source_category_srl;
				$var->document_srl = $document_srl;
				executeQuery("textyle.updatePostCategorySrl",$var);
			}

			// insert alias
			$args->alias = trim($args->alias);
			if($args->use_alias=='Y' && $args->alias){
				$output = $oDocumentController->insertAlias($args->module_srl,$document_srl,$args->alias);
				if(!$output->toBool()) return $output;
			}

			// insert subscription
			$args->publish_date_yyyymmdd = ereg_replace("[^0-9]",'',str_replace("-",'',$args->publish_date_yyyymmdd));
			if($args->subscription=='Y' && $args->publish_date_yyyymmdd) {
				$args->publish_date_hh = ereg_replace("[^0-9]",'',str_replace('-','',$args->publish_date_hh));
				$args->publish_date_ii = ereg_replace("[^0-9]",'',str_replace('-','',$args->publish_date_ii));
				$args->publish_date_hh = $args->publish_date_hh ? $args->publish_date_hh : 0;
				$args->publish_date_ii = $args->publish_date_ii ? $args->publish_date_ii : 0;
				$args->publish_date = sprintf("%s%02d%02d00",$args->publish_date_yyyymmdd, $args->publish_date_hh , $args->publish_date_ii);

				// but time check
				if($args->publish_date <= date('YmdHis')){
					$args->subscription = 'N';
					$args->publish_date = null;
				}else{
					$this->insertPostSubscription($args->module_srl,$document_srl,$args->publish_date);
				}
			}

            $oDocument = $oDocumentModel->getDocument($document_srl);

			// trackback
			if($args->subscription == 'N' && trim($args->send_trackback)){
				$charset = Context::get('charset');
				$oTrackbackController = &getController('trackback');

				$trackback_url = explode('\n',$args->send_trackback);
				for($i=0,$c=count($trackback_url);$i<$c;$i++){
					$trackback_url[$i] = trim($trackback_url[$i]);
					if($trackback_url[$i]){
						$oTrackbackController->sendTrackback($oDocument,$trackback_url[$i],$charset);
					}
				}
			}

            // send me2day
            if($args->temp_save!='Y' && $args->subscription!='Y') {
                $this->textyle->sendMe2dayPost(sprintf('"%s":%s?document_srl=%s', $args->title, Context::getRequestUri(), $document_srl), $args->tags);
                $this->textyle->sendTwitterPost(sprintf('%s %s?document_srl=%s', $args->title, Context::getRequestUri(), $document_srl));
                $this->textyle->sendAPI($oDocument);
            }

			$output->add('document_srl',$document_srl);
            return $output;
		}

		function updatePost($args){
			$oDocumentModel = &getModel('document');	
			$oDocumentController = &getController('document');
			
            // 이미 존재하는 글인지 체크
            $oDocument = $oDocumentModel->getDocument($args->document_srl, $this->grant->manager);
			if(!$oDocument->isExists()) return new Object(-1,'msg_invalid_request');

			$output = $oDocumentController->updateDocument($oDocument, $args);
			if(!$output->toBool()) return $output;

			$logged_info = Context::get('logged_info');

			$from_tmp = false;
			$to_tmp = false;

			// 발행->
			if($oDocument->get('module_srl') == $args->this_module_srl){ 
				$from_tmp = false;
			// 임시저장->
			}else{
				$from_tmp = true;
			}

			// -> 발행
			if($args->module_srl == $args->this_module_srl){ 
				$to_tmp = false;
			// -> 임시저장
			}else{
				$to_tmp = true;
			}

			// 임시저장-> 임시저장, 발행->임시저장
			// 임시저장일때는 category_srl을 다시 업데이트.
			if($to_tmp){
				$var->category_srl = $args->source_category_srl;
				$var->document_srl = $args->document_srl;
				executeQuery("textyle.updatePostCategorySrl",$var);
			}
	
			// 임시저장-> 발행
			if($from_tmp && !$to_tmp){
				$oDocumentController->updateCategoryCount($args->this_module_srl,$args->source_category_srl);
				$var->document_srl = $args->document_srl;
				$output=executeQuery("textyle.updatePostRegdate",$var);
				$args->list_order = getNextSequence() *-1;
				$args->update_order = $args->list_order;
				$output = executeQuery('document.updateDocument', $args);

			}

			// 발행 -> 임시저장
			if(!$from_tmp && $to_tmp){
				$oDocumentController->updateCategoryCount($args->this_module_srl,$args->source_category_srl);
			}

			// insert alias
			$oDocumentController->deleteDocumentALiasByDocument($args->document_srl);
			$args->alias = trim($args->alias);
			if($args->use_alias=='Y' && $args->alias){
				$output = $oDocumentController->insertAlias($this->module_srl,$args->document_srl,$args->alias);
				if(!$output->toBool()) return $output;
			}

			// publish from subscription
			if(!$to_tmp){
                $subscription_changed = false;
				if($args->subscription!='Y'){
					$oTextyleModel = &getModel('textyle');
					$subscription_output = $oTextyleModel->getSubscriptionByDocumentSrl($args->document_srl);

					// is subscription 
					if($subscription_output->data){
						// update document
						$args->module_srl = abs($args->module_srl);
						$args->list_order = getNextSequence() *-1;
						$args->update_order = $args->list_order;
						$output = executeQuery('document.updateDocument', $args);

						// delete subscription
						$this->deletePostSubscription($args->document_srl); // delete first
					}

				}else{
					// insert subscription
					$this->deletePostSubscription($args->document_srl); // delete first

					$args->publish_date_yyyymmdd = ereg_replace("[^0-9]",'',str_replace("-",'',$args->publish_date_yyyymmdd));
					if($args->publish_date_yyyymmdd) {
						$args->publish_date_hh = ereg_replace("[^0-9]",'',str_replace('-','',$args->publish_date_hh));
						$args->publish_date_ii = ereg_replace("[^0-9]",'',str_replace('-','',$args->publish_date_ii));
						$args->publish_date_hh = $args->publish_date_hh ? $args->publish_date_hh : 0;
						$args->publish_date_ii = $args->publish_date_ii ? $args->publish_date_ii : 0;
						$args->publish_date = sprintf("%s%02d%02d00",$args->publish_date_yyyymmdd, $args->publish_date_hh , $args->publish_date_ii);


						// but time check
						if($args->publish_date <= date('YmdHis')){
							$args->subscription = 'N';
                            $subscription_changed = true;
							$args->publish_date = null;
						}else{
							$this->insertPostSubscription($args->module_srl,$args->document_srl,$args->publish_date);
						}
					}
				}

                $document_srl = $output->get('document_srl');
                $oDocument = $oDocumentModel->getDocument($document_srl);

				// trackback
				if($args->subscription == 'N' && trim($args->send_trackback)){
					$charset = Context::get('charset');
					$oTrackbackController = &getController('trackback');
					$trackback_url = explode('\n',$args->send_trackback);
					for($i=0,$c=count($trackback_url);$i<$c;$i++){
						$trackback_url[$i] = trim($trackback_url[$i]);
						if($trackback_url[$i]){
							$oTrackbackController->sendTrackback($oDocument,$trackback_url[$i],$charset);
						}
					}
				}

                // send me2day
                if($from_tmp || $subscription_changed) {
                    $this->textyle->sendMe2dayPost(sprintf('"%s":%s?document_srl=%s', $args->title, Context::getRequestUri(), $args->document_srl), $args->tags);
                    $this->textyle->sendTwitterPost(sprintf('%s %s?document_srl=%s', $args->title, Context::getRequestUri(), $args->document_srl));
                    $this->textyle->sendAPI($oDocument);
                }
			}

			$output->add('document_srl',$args->document_srl);
            return $output;
		}

		function insertPostSubscription($module_srl,$document_srl,$publish_date){
			$args->document_srl = $document_srl;
			$args->module_srl = $module_srl;
			$args->publish_date = $publish_date;

            $output = executeQuery('textyle.insertTextyleSubscription', $args);
			if(!$output->toBool()) return $output;

			// update module_srl for subscription
			$args->module_srl = abs($args->module_srl) * -1;
            $output = executeQuery('document.updateDocumentModule', $args);

			// sync to textyle
			$this->syncTextyleSubscriptionDate($module_srl);
			return $output;
		}

		function procTextylePostTrashRestore(){
			$document_srl = Context::get('document_srl');

            if(preg_match('/^([0-9,]+)$/',$document_srl)) $document_srl = explode(',',$document_srl);
            else $document_srl = array($document_srl);

			$oDocumentAdminController = &getAdminController('document');
			$oDocumentController = &getController('document');
			$oDocumentModel = &getModel('document');

            $oDB = &DB::getInstance();
            $oDB->begin();

			$args->document_srl = join(',',$document_srl);
            $output = executeQueryArray('document.getTrashByDocumentSrl', $args);
			
			$trash = array();
			if($output->data){
				foreach($output->data as $k => $v){
					$trash[] = $v;
				}
			}

			$updated_category_srls = array();

			foreach($trash as $k => $v){
				$output = $oDocumentAdminController->restoreTrash($v->trash_srl);
				if(!$output->toBool()){
					 return new Object(-1, 'fail_to_trash');
				}else{
					$oDocument = $oDocumentModel->getDocument($v->document_srl);
					$obj = $oDocument->getObjectVars();
					$updated_category_srls[] = $obj->category_srl;
					$trigger_output = ModuleHandler::triggerCall('document.updateDocument', 'after', $obj);
					if(!$trigger_output->toBool()) {
						$oDB->rollback();
						return $trigger_output;
					}
				}

				// TO DO : move DocumentController 
				unset($trash_args);
				$trash_args->document_srls = $v->document_srl;
				$trash_args->module_srl = $v->module_srl;
				$output = executeQuery('comment.updateCommentModule', $trash_args);
				if(!$output->toBool()){
					$oDB->rollback();
					return new Object(-1, 'fail_to_trash');
				}

				$output = executeQuery('trackback.updateTrackbackModule', $trash_args);
				if(!$output->toBool()){
					$oDB->rollback();
					return new Object(-1, 'fail_to_trash');
				}

			}

			// TODO : move code document trash 
			$updated_category_srls = array_unique($updated_category_srls);
			foreach($updated_category_srls as $k => $srl){
				$oDocumentController->updateCategoryCount($this->module_srl,$srl);
			}
		
			$oDB->commit();
			return $output;
		}


		function procTextylePostTrash(){
			$document_srl = Context::get('document_srl');

            if(preg_match('/^([0-9,]+)$/',$document_srl)) $document_srls = explode(',',$document_srl);
            else $document_srls = array($document_srl);

			$oDocumentController = &getController('document');
			$oDocumentModel = &getModel('document');
			$oCommentController = &getController('comment');

            $oDB = &DB::getInstance();
            $oDB->begin();

			for($i=0,$c=count($document_srls);$i<$c;$i++) {
				$args->document_srl = $document_srls[$i];
				$output = $oDocumentController->moveDocumentToTrash($args);
				if(!$output->toBool()){
					 return new Object(-1, 'fail_to_trash');
				}else{
					$oDocument = $oDocumentModel->getDocument($args->document_srl);
					$obj = $oDocument->getObjectVars();

					$trigger_output = ModuleHandler::triggerCall('document.updateDocument', 'after', $obj);
					if(!$trigger_output->toBool()) {
						$oDB->rollback();
						return $trigger_output;
					}
				}

				// TO DO : move DocumentController 
				unset($trash_args);
				$trash_args->document_srls = $document_srls[$i];
				$trash_args->module_srl = 0;
				$output = executeQuery('comment.updateCommentModule', $trash_args);
				if(!$output->toBool()){
					$oDB->rollback();
					return new Object(-1, 'fail_to_trash');
				}

				$output = executeQuery('trackback.updateTrackbackModule', $trash_args);
				if(!$output->toBool()){
					$oDB->rollback();
					return new Object(-1, 'fail_to_trash');
				}


			}

			$oDB->commit();
			$msg_code = 'success_trashed';
            $this->setMessage($msg_code);
		}
	
		function deletePostSubscription($document_srl){
			$args->document_srl = $document_srl;
            $output = executeQuery('textyle.deleteTextyleSubscriptionByDocumentSrl', $args);

			// sync to textyle
			$this->syncTextyleSubscriptionDate($module_srl);

			return $output;
		}

		function syncTextyleSubscriptionDate($module_srl){

			$oTextyleModel = &getModel('textyle');
			$output = $oTextyleModel->getSubscriptionMinPublishDate($module_srl);

			if($output->data && $output->data->publish_date){
				$args->subscription_date = $output->data->publish_date;
			}else{
				$args->subscription_date = '';
			}
			$output = $this->updateTextyleInfo($module_srl,$args);

		}


		function procTextylePostDelete(){
			$document_srl = Context::get('document_srl');
            if(preg_match('/^([0-9,]+)$/',$document_srl)) $document_srl = explode(',',$document_srl);
			else $document_srl = array($document_srl);
			if(count($document_srl)<1) return new Object(-1,'msg_invalid_request');

			$output = $this->deletePost($document_srl);
			if(!$output->toBool()) return $output;
            $this->setMessage('success_trashed');
		}

		function deletePost($document_srl, $is_admin=false){
			$document_srl = is_array($document_srl) ? $document_srl : array($document_srl);

			// delete document
			$oDocumentController = &getController('document');

			$oDB = &DB::getInstance();
			$oDB->begin();
			for($i=0,$c=count($document_srl);$i<$c;$i++) {
				$output = $oDocumentController->deleteDocument($document_srl[$i], $is_admin);
				if(!$output->toBool()) return new Object(-1, 'fail_to_delete');
			}
			$oDB->commit();
			return $output;
		}

		function triggerInsertComment(&$obj){
            $module_info = Context::get('module_info');
            if($module_info->module != 'textyle') return new Object();
			if(!$obj->comment_srl) return new Object();
            
			$args->module_srl = $module_info->module_srl;
			$args->nick_name = $obj->nick_name;
			$args->member_srl = $obj->member_srl;
			$args->homepage = $obj->homepage;
			$args->comment_count = 1;
            $this->updateTextyleSupporter($args);
            return new Object();
		}

		function triggerDeleteComment(&$obj){
            $module_info = Context::get('module_info');
            if($module_info->module != 'textyle') return new Object();
			if(!$obj->comment_srl) return new Object();

			$args->module_srl = $module_info->module_srl;
			$args->nick_name = $obj->nick_name;
			$args->member_srl = $obj->member_srl;
			$args->homepage = $obj->homepage;
			$args->comment_count = -1;
            $this->updateTextyleSupporter($args);

            return new Object();
		}

		function triggerInsertTrackback(&$obj){
            $module_info = Context::get('module_info');
            if($module_info->module != 'textyle') return new Object();
			if(!$obj->trackback_srl) return new Object();

			$args->module_srl = $module_info->module_srl;
			$args->nick_name = $obj->blog_name;
			$args->member_srl = 0;
			$args->homepage = $obj->url;
			$args->trackback_count = 1;
            $this->updateTextyleSupporter($args);

            return new Object();
		}

		function triggerDeleteTrackback(&$obj){
            $module_info = Context::get('module_info');
            if($module_info->module != 'textyle') return new Object();
			if(!$obj->trackback_srl) return new Object();

			$args->module_srl = $module_info->module_srl;
			$args->nick_name = $obj->blog_name;
            $args->member_srl = 0;
			$args->homepage = $obj->url;
			$args->trackback_count = -1;
            $this->updateTextyleSupporter($args);

            return new Object();
		}

		function updateTextyleSupporter($obj){
            $oMemberModel = &getModel('member');

            $args->module_srl = $obj->module_srl;
            if($obj->member_srl) $args->member_srl = $obj->member_srl;
            else if($obj->nick_name) $args->nick_name = $obj->nick_name;
            else if($obj->homepage) $args->homepage = $obj->homepage;
            $args->regdate = date("Ym");

            $output = executeQuery('textyle.getTextyleSupporter', $args);
            $sup = $output->data;

            $args->member_srl = $obj->member_srl;
            if($obj->member_srl) {
                $member_info = $oMemberModel->getMemberInfoByMemberSrl($obj->member_srl);
                $args->nick_name = $member_info->nick_name;
                if($member_info->blog) $args->homepage = $member_info->blog;
                else $args->homepage = $member_info->homepage;
            } else {
                $args->nick_name = $obj->nick_name;
                $args->homepage = $obj->homepage;
            }
            $args->comment_count = $sup->comment_count+$obj->comment_count;
            $args->trackback_count = $sup->trackback_count+$obj->trackback_count;
            $args->guestbook_count = $sup->guestbook_count+$obj->guestbook_count;
            $args->total_count = $args->comment_count+$args->trackback_count+$args->guestbook_count;

            if($sup->textyle_supporter_srl) {
				$args->textyle_supporter_srl = $sup->textyle_supporter_srl;
                $output = executeQuery('textyle.updateTextyleSupporter',$args);
            } else {
				$args->textyle_supporter_srl = getNextSequence();
                $output = executeQuery('textyle.insertTextyleSupporter',$args);
			}

			return $output;
		}

		function procTextylePostItemsCategoryMove(){
			$document_srl = Context::get('document_srl');
			$category_srl = Context::get('category_srl');
			if(!$document_srl || !$category_srl) return new Object(-1,'msg_invalid_request');

			if(preg_match('/^([0-9,]+)$/',$document_srl)) $document_srl = explode(',',$document_srl);
			else $document_srl = array($document_srl);

			$oDocumentAdminController = &getAdminController('document');
			$oDocumentAdminController->moveDocumentModule($document_srl,$this->module_srl,$category_srl);
		}

		function procTextylePostItemsSetSecret(){
			$document_srl = Context::get('document_srl');
			$set_secret = Context::get('set_secret');
			if(!$document_srl) return new Object(-1,'msg_invalid_request');
			$set_secret = $set_secret=='Y'?'Y':'N';

            if(preg_match('/^([0-9,]+)$/',$document_srl)) $document_srl = explode(',',$document_srl);
			else $document_srl = array($document_srl);

			$output = $this->setTextylePostItemsSecret($document_srl,$set_secret);
			return $output;
		}

		function setTextylePostItemsSecret($document_srls,$set_secret='Y'){
			$args->document_srl = join(',',$document_srls);
			$args->is_secret = $set_secret;
			$output = executeQuery('document.updateDocumentsSecret',$args);
			return $output;
		}

		function procTextylePostItemsAllowCommentTrackback(){
			$var = Context::getRequestVars();
			$allow_comment = $var->allow_comment!='Y'?'N':'Y';
			$allow_trackback = $var->allow_trackback!='Y'?'N':'Y';
			$document_srl = $var->document_srl;

			if(preg_match('/^([0-9,]+)$/',$document_srl)) $document_srl = explode(',',$document_srl);
			else $document_srl = array($document_srl);

			$args->allow_comment = $allow_comment;
			$args->trackback = $allow_trackback;
			$args->document_srl = join(',',$document_srl);
			$args->module_srl = $this->module_srl;
			$output = executeQuery('document.updateDocumentsAllowCommentTrackback',$args);
			return $output;	
		}


        /**
         * @brief 발행예약한 post 출판
         **/
		function publishPost($module_srl){
			$now = date('YmdHis');
			$oTextyleModel = &getModel('textyle');

			$args->module_srl = $module_srl;
			$args->less_publish_date = $now;
			$output = $oTextyleModel->getSubscription($args);
			$published = false;
			if($output->data){
				foreach($output->data as $k => $v){
					// publish
					if($v->publish_date <= $now){
						$this->_updatePublishPost($v->document_srl,$v->publish_date,$module_srl);
						$published = true;
					}
				}
			}

			if($published){
				$this->_deleteSubscription($module_srl,$now);
				$this->syncTextyleSubscriptionDate($module_srl);
			}
		}

		function _updatePublishPost($document_srl,$publish_date,$module_srl){
			$args->module_srl = $module_srl;
			$args->document_srl = $document_srl;
			$args->list_order =  getNextSequence() * -1;
			$args->update_order = $args->list_order;
			$args->reg_date = $publish_date;

			$output = executeQuery('document.updateDocumentOrder',$args);
			return $output;
		}

		function _deleteSubscription($module_srl,$less_publish_date){
			$args->module_srl = $module_srl;
			$args->publish_date = $less_publish_date;
			$output = executeQuery('textyle.deleteTextyleSubscriptionByPublishDate',$args);
			return $output;

		}

		function procTextyleConfigPostwriteInsert(){
            $oEditorModel = &getModel('editor');
            $oModuleController = &getController('module');

			$vars = Context::getRequestVars();

            // 텍스타일 쓰기 옵션 저장
			$args->post_editor_skin = $vars->post_editor_skin ? $vars->post_editor_skin : $vars->etc_post_editor_skin;
			$args->post_use_prefix = $vars->post_use_prefix;
			$args->post_use_suffix = $vars->post_use_suffix;
			$args->post_prefix = $vars->post_prefix;
			$args->post_suffix = $vars->post_suffix;
			$output = $this->updateTextyleInfo($this->module_srl,$args);
            if(!$output->toBool()) return $output;

            // 폰트종류/ 크기 저장 (editor 모듈 이용) 
            $editor_config = $oEditorModel->getEditorConfig($this->module_srl);

            $editor_config->editor_skin = $args->post_editor_skin;
            $editor_config->content_font = $vars->font_family;
            if($editor_config->content_font) {
                $font_list = array();
                $fonts = explode(',',$editor_config->content_font);
                for($i=0,$c=count($fonts);$i<$c;$i++) {
                    $font = trim(str_replace(array('"','\''),'',$fonts[$i]));
                    if(!$font) continue;
                    $font_list[] = $font;
                }
                if(count($font_list)) $editor_config->content_font = '"'.implode('","',$font_list).'"';
            }
            $editor_config->content_font_size = $vars->font_size;

            $oModuleController->insertModulePartConfig('editor',$this->module_srl,$editor_config);
		}

        /**
         * @brief 코멘트 삭제
         **/
        function procTextyleCommentDelete() {
            // 댓글 번호 확인
            $comment_srl = Context::get('comment_srl');
            if(!$comment_srl) return $this->doError('msg_invalid_request');

            // comment 모듈의 controller 객체 생성
            $oCommentController = &getController('comment');

            $output = $oCommentController->deleteComment($comment_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

            $this->add('comment_srl', $comment_srl);
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief textyle 컬러셋 변경
         **/
        function procTextyleColorsetModify() {
            $oTextyleModel = &getModel('textyle');
            $mytextyle = $oTextyleModel->getMemberTextyle();
            if(!$mytextyle->isExists()) return new Object(-1, 'msg_not_permitted');

            $colorset = Context::get('colorset');
            if(!$colorset) return new Object(-1,'msg_invalid_request');

            $this->updateTextyleColorset($mytextyle->getModuleSrl(), $colorset);

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('move_mytextyle');
        }

		/**
         * @brief textyle delete tag
         **/
		function procTextyleTagDelete(){
			$selected_tag = trim(Context::get('selected_tag'));
			if(!$selected_tag) return new Object(-1,'msg_invalid_request');

			// get document_srl
			$args->tag = $selected_tag; 
            $args->module_srl = $this->module_srl;

			$oTagModel = &getModel('tag');
			$output = $oTagModel->getDocumentSrlByTag($args);
			$document_srl = array();
			if($output->data){
				foreach($output->data as $k => $v) $document_srl[] = $v->document_srl;
			}

			// delete tag table
            $output = executeQuery('tag.deleteTagByTag', $args);
			if(!$output->toBool()) return $output;

			$this->syncDocumentTags($document_srl);
		}


		/**
		 * @brief textyle update tag
		 * not good;;
         **/
		function procTextyleTagUpdate(){
			$selected_tag = trim(Context::get('selected_tag'));
			$new_tag = trim(Context::get('tag'));

			if(!$selected_tag || !$new_tag) return new Object(-1,'msg_invalid_request');

			// get document_srl
			$args->tag = $selected_tag; 
            $args->module_srl = $this->module_srl;

			$oTagModel = &getModel('tag');
			$output = $oTagModel->getDocumentSrlByTag($args);
			$document_srl = array();
			if($output->data){
				foreach($output->data as $k => $v) $document_srl[] = $v->document_srl;
			}

			// delete tag table
            $output = executeQuery('tag.deleteTagByTag', $args);
			if(!$output->toBool()) return $output;
			
			$args->tag = $new_tag; 
			$has_tag_document_srl = array();
			$output = $oTagModel->getDocumentSrlByTag($args);
			if($output->data){
				foreach($output->data as $k => $v) $has_tag_document_srl[] = $v->document_srl;
			}
			
			for($i=0,$c=count($document_srl);$i<$c;$i++){
				$args->document_srl = $document_srl[$i];

				// already has
				if(in_array($args->document_srl,$has_tag_document_srl)) continue; 

				$args->tag_srl = getNextSequence();
				$args->tag = $new_tag; 
				$output = executeQuery('tag.insertTag', $args);
			}

			// sync documents table
			$this->syncDocumentTags($document_srl);
			$this->add('selected_tag',$new_tag);
		}

		/**
         * @brief sync documents table tags
         **/
		function syncDocumentTags($document_srls){
			$args->document_srl = join(',',$document_srls);
			$output = executeQueryArray('tag.getAllTagList', $args);

			$tags = array();
			if($output->data){
				foreach($output->data as $k => $v){
					if(!is_array($tags[$v->document_srl])) $tags[$v->document_srl] = array();
					$tags[$v->document_srl][] = $v->tag;
				}
			}

			unset($args);
			for($i=0,$c=count($document_srls);$i<$c;$i++){
				$args->document_srl = $document_srls[$i];
				if(is_array($tags[$args->document_srl])) $args->tags = join(',',$tags[$args->document_srl]);
				else $args->tags = "";
				$output = executeQuery('document.updateDocumentTags', $args);
			}
		}

        /**
         * @brief 컨텐츠의 태그 수정
         **/
        function procTextyleContentTagModify(){
            $req = Context::getRequestVars();

			// document module의 model 객체 생성
            $oDocumentModel = &getModel('document');

            // document module의 controller 객체 생성
            $oDocumentController = &getController('document');
            $oDocument = $oDocumentModel->getDocument($req->document_srl);
            $oDocument->add('tags',$req->textyle_content_tag);
            $obj = $oDocument->getObjectVars();

            $output = $oDocumentController->updateDocument($oDocument, $obj);
            $this->setMessage('success_updated');
        }

        function procTextyleToolLayoutConfigSkin() {
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');
            $oTextyleModel = &getModel('textyle');

            $skin = Context::get('skin');
            if(!is_dir($this->module_path.'skins/'.$skin)) return new Object();

            $module_info  = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
            $module_info->skin = $skin;
            $output = $oModuleController->updateModule($module_info);
            if(!$output->toBool()) return $output;

            FileHandler::removeDir($oTextyleModel->getTextylePath($this->module_srl));
            FileHandler::copyDir($this->module_path.'skins/'.$skin, $oTextyleModel->getTextylePath($this->module_srl));
        }

        function procTextyleToolLayoutResetConfigSkin() {
            $oModuleModel = &getModel('module');
            $module_info  = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
            $skin = $module_info->skin;

			$this->resetSkin($this->module_srl,$skin);
        }

		function resetSkin($module_srl,$skin=null){
			if(!$skin) $skin = $this->skin;
			if(!file_exists($this->module_path.'skins/'.$skin)) $skin = $this->skin;
            $oTextyleModel = &getModel('textyle');
            FileHandler::removeDir($oTextyleModel->getTextylePath($module_srl));
            FileHandler::copyDir($this->module_path.'skins/'.$skin, $oTextyleModel->getTextylePath($module_srl));
		}


        function procTextyleToolLayoutConfigEdit() {
            $oTextyleModel = &getModel('textyle');

            $html = trim(Context::get('html'));
            $html_file = $oTextyleModel->getTextyleUserHTMLFile($this->module_srl);
            FileHandler::writeFile($html_file, $html);

            $css = trim(Context::get('css'));
            $css_file = $oTextyleModel->getTextyleUserCSSFile($this->module_srl);
            FileHandler::writeFile($css_file, $css);
        }

        /**
         * @brief textyle 기본 설정 저장
         * textyle의 전체 설정은 module config를 이용해서 저장함
         * 대상 : 기본 textyle 스킨, 권한, 스킨 정보
         **/
        function insertTextyleConfig($textyle) {
            $oModuleController = &getController('module');
            $oModuleController->insertModuleConfig('textyle', $textyle);
        }

        /**
         * @brief 회원 - textyle 브라우져 제목 수정
         * textyle의 제목은 modules테이블의 browser_title컬럼을 이용한다
         **/
        function updateTextyleBrowserTitle($module_srl, $browser_title) {
            $args->module_srl = $module_srl;
            $args->browser_title = $browser_title;
            return executeQuery('textyle.updateTextyleBrowserTitle', $args);
        }
        function procTextyleEnableRss() {
            $oTextyleModel = &getModel('textyle');
            $mytextyle = $oTextyleModel->getMemberTextyle();
            if(!$mytextyle->isExists()) return new Object(-1,'msg_not_permitted');

            $oRssAdminController = &getAdminController('rss');
            $oRssAdminController->setRssModuleConfig($mytextyle->getModuleSrl(), 'Y');
        }

        function procTextyleDisableRss() {
            $oTextyleModel = &getModel('textyle');
            $mytextyle = $oTextyleModel->getMemberTextyle();
            if(!$mytextyle->isExists()) return new Object(-1,'msg_not_permitted');

            $oRssAdminController = &getAdminController('rss');
            $oRssAdminController->setRssModuleConfig($mytextyle->getModuleSrl(), 'N');
        }

       /**
         * @brief 아이디 클릭시 나타나는 팝업메뉴에 "textyle" 메뉴를 추가하는 trigger
         **
        function triggerMemberMenu(&$obj) {
            $member_srl = Context::get('target_srl');
            if(!$member_srl) return new Object();

            $args->member_srl = $member_srl;
            $output = executeQuery('textyle.getTextyle', $args);
            if(!$output->toBool() || !$output->data) return new Object();

            $site_module_info = Context::get('site_module_info');
            $default_url = Context::getDefaultUrl();

            if($site_module_info->site_srl && !$default_url) return new Object();

            $url = getSiteUrl($default_url, '','mid',$output->data->mid);
            $oMemberController = &getController('member');
            $oMemberController->addMemberPopupMenu($url, 'textyle', './modules/textyle/tpl/images/textyle.gif');

            return new Object();
        }
		*/
        /**
         * @brief action forward이거나 다른 모듈이 호출될 경우 textyle의 레이아웃을 적용
         **/
        function triggerApplyLayout(&$oModule) {
            // 팝업 레이아웃이면 패스
            if(!$oModule || $oModule->getLayoutFile()=='popup_layout.html') return new Object();

            // 관리자 페이지는 무조건 pass~
            if(Context::get('module')=='admin') return new Object();

            // XMLRPC, JSON 형식이어도 pass~
            if(in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) return new Object();

            // 현재 가상사이트가 textyle이 아닐 경우 pass~
            $site_module_info = Context::get('site_module_info');
            if(!$site_module_info || !$site_module_info->site_srl || $site_module_info->mid != 'textyle' ) return new Object();

            // 현재 요청된 사이트가 textyle이고 textyle의 action이면 pass~
            if($oModule->mid == 'textyle' && isset($oModule->xml_info->action->{$oModule->act})) return new Object();

            // 일단 레이아웃을 있음으로 변경
            Context::set('layout',null);

            // 요청된 텍스타일의 정보를 구해서 레이아웃과 관련 정보를 설정
            $oTextyleModel = &getModel('textyle');
            $oTextyleView = &getView('textyle');
			$textyle = $oTextyleModel->getTextyle($site_module_info->index_module_srl);

            $oModule->module_info->layout_srl = null;
            $oModule->setLayoutPath($oTextyleModel->getTextylePath($site_module_info->index_module_srl));
            $oModule->setLayoutFile('textyle');

            $module_path = './modules/textyle/';
            Context::addHtmlHeader('<link rel="shortcut icon" href="'.$textyle->getFaviconSrc().'" />');
            Context::addJsFile($module_path.'tpl/js/textyle_service.js');
            Context::addCssFile($oModule->getLayoutPath().'textyle.css');

            // Textyle에서 쓰기 위해 변수를 미리 정하여 세팅
            Context::set('root_url', Context::getRequestUri());
            Context::set('home_url', getSiteUrl($textyle->domain));
            Context::set('profile_url', getSiteUrl($textyle->domain,'','mid','textyle','act','dispTextyleProfile'));
            Context::set('guestbook_url', getSiteUrl($textyle->domain,'','mid','textyle','act','dispTextyleGuestbook'));
            Context::set('tag_url', getSiteUrl($textyle->domain,'','mid','textyle','act','dispTextyleTag'));
            if(Context::get('is_logged')) Context::set('admin_url', getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleToolDashboard'));
            else Context::set('admin_url', getSiteUrl($textyle->domain,'','mid','textyle','act','dispTextyleToolLogin'));
            Context::set('textyle_title', $textyle->get('textyle_title'));
            Context::set('textyle', $textyle);

            // 추가 메뉴 
            $extra_menus = array(
            );
            Context::set('extra_menus', $extra_menus);
            Context::set('textyle_mode', 'module');

            return new Object();
        }

        /**
         * @brief 글별 referer 추가
         **/
        function insertReferer($oDocument) {
            if($_SESSION['textyleReferer'][$oDocument->document_srl]) return;
            $_SESSION['textyleReferer'][$oDocument->document_srl] = true;
            $referer = urldecode($_SERVER['HTTP_REFERER']);
            if(!$referer) return;

            $_url = parse_url(Context::getRequestUri());
            $url_info = parse_url($referer);
            if($_url['host']==$url_info['host']) return;

            $args->module_srl = $oDocument->get('module_srl');
            $args->document_srl = $oDocument->get('document_srl');
            $args->regdate = date("Ymd");
            $args->host = $url_info['host'];
            $output = executeQuery('textyle.getRefererHost', $args);
            if(!$output->data->textyle_host_srl) {
                $args->textyle_host_srl = getNextSequence();
                $output = executeQuery('textyle.insertRefererHost', $args);
            } else {
                $args->textyle_host_srl = $output->data->textyle_host_srl;
                $output = executeQuery('textyle.updateRefererHost', $args);
            }
            if(!$output->toBool()) return;

            if(preg_match('/(query|q|search_keyword)=([^&]+)/i',$referer, $matches)) $args->link_word = trim($matches[2]);
            $args->referer_url = $referer;

            $output = executeQuery('textyle.getReferer', $args);
            if($output->data->textyle_referer_srl) {
                $uArgs->textyle_referer_srl = $output->data->textyle_referer_srl;
                return executeQuery('textyle.updateReferer', $uArgs);
            } else {
                $args->textyle_referer_srl = getNextSequence();
                return executeQuery('textyle.insertReferer', $args);
            }
        }

        function procTextyleToolImportPrepare() {
            $xml_file = Context::get('xml_file');
            if(!$xml_file || $xml_file == 'http://') return new Object(-1,'msg_migration_file_is_null');

            $oImporterAdminController = &getAdminController('importer');
            $oImporterAdminController->procImporterAdminPreProcessing();
            $this->setError($oImporterAdminController->getError());
            $this->setMessage($oImporterAdminController->getMessage());
            $this->adds($oImporterAdminController->getVariables());
        }

        function procTextyleToolImport() {
            $oImporterAdminController = &getAdminController('importer');
            $oImporterAdminController->procImporterAdminImport();
            $this->setError($oImporterAdminController->getError());
            $this->setMessage($oImporterAdminController->getMessage());
            $this->adds($oImporterAdminController->getVariables());
        }

        function procTextyleInsertBlogApi() {
            require_once($this->module_path.'libs/metaweblog.class.php');

            $msg = Context::getLang('msg_blogapi_registration');
            $vars = Context::gets('blogapi_site_url', 'blogapi_site_title', 'blogapi_url', 'blogapi_user_id', 'blogapi_password');
            $idx = 0;
            foreach($vars as $key => $val) {
                if(!$val) return new Object(-1,$msg[$idx]);
                $idx++;
            }

            $oMeta = new metaWebLog($vars->blogapi_url, $vars->blogapi_user_id, $vars->blogapi_password);
            $output = $oMeta->getUsersBlogs();
            if(!$output->toBool()) return $output;

            $vars->api_srl = Context::get('api_srl');
            $vars->module_srl = $this->module_srl;
            if($vars->api_srl) {
                $output = executeQuery('textyle.getApiInfo',$vars);
                if($output->data->api_srl) return executeQuery('textyle.updateBlogAPI', $vars);
            } 
            $vars->api_srl = getNextSequence();
            return executeQuery('textyle.insertBlogAPI', $vars);
        } 

        function procTextyleToggleEnableAPI() {
            $vars->api_srl = Context::get('api_srl');
            $vars->module_srl = $this->module_srl;
            $output = executeQuery('textyle.getApiInfo',$vars);
            if(!$output->data) return new Object(-1,'msg_invalid_request');
            if($output->data->enable == 'Y') $vars->enable = 'N';
            else $vars->enable = 'Y';
            $output = executeQuery('textyle.updateEnableBlogAPI', $vars);
            if(!$output->toBool()) return $output;
            $this->add('enable', $vars->enable);

        }

        function procTextyleDeleteBlogApi() {
            $api_srl = Context::get('api_srl');
            if(!$api_srl) return new Object(-1,'msg_invalid_request');

            $output = $this->deleteBlogApi($this->module_srl,$api_srl);
            return $output;
        }

		function deleteBlogApis($module_srl){
			$args->module_srl = $module_srl;

            $output = executeQuery('textyle.deleteTextyleApis',$args);
            return $output;
		}

		function deleteBlogApi($module_srl,$api_srl){
			$args->module_srl = $module_srl;
			$args->api_srl = $api_srl;

            $output = executeQuery('textyle.deleteTextyleApi',$args);
            return $output;
		}

    }

?>
