<?php
	require_once(_XE_PATH_.'modules/textyle/textyle.view.php');

	class textyleMobile extends textyleView {

		function init() {

            $oTextyleModel = &getModel('textyle');
            $oTextyleController = &getController('textyle');
            $oModuleModel = &getModel('module');

			$site_module_info = Context::get('site_module_info');
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

            // @brief Textyle 모듈의 기본 설정은 view에서는 언제든지 사용하도록 load하여 Context setting
            if(!$this->module_info->skin) $this->module_info->skin = $this->skin;

            // 만약 skin 미리보기일 경우 관리자라면 스킨을 변경해 보여줌
			$preview_skin = Context::get('preview_skin');
            if($oModuleModel->isSiteAdmin(Context::get('logged_info'))&&$preview_skin) {
                if(is_dir($this->module_path.'skins/'.$preview_skin)) {
                    $textyle_config->skin = $this->module_info->skin = $preview_skin;
                }
            }

            // 모듈 정보에 textyle 정보를 합쳐서 저장
            Context::set('module_info',$this->module_info);
            Context::set('current_module_info', $this->module_info);

            $this->textyle = $oTextyleModel->getTextyle($this->module_info->module_srl);
            $this->site_srl = $this->textyle->site_srl;
            Context::set('textyle',$this->textyle);

            // time zone 지정
            if($this->textyle->timezone) $GLOBALS['_time_zone'] = $this->textyle->timezone;

            // favicon 지정
            Context::addHtmlHeader('<link rel="shortcut icon" href="'.$this->textyle->getFaviconSrc().'" />');

            // publish subscription
            if($this->textyle->getSubscriptionDate() <= date('YmdHis')){
                $output = $oTextyleController->publishSubscriptedPost($this->module_info->module_srl);
            }

            // textyle 관리
            if(preg_match("/TextyleTool/",$this->act) || $oTextyleModel->isAttachedMenu($this->act) ) {

				$this->custom_menu = $oTextyleModel->getTextyleCustomMenu();

				// 모바일 뷰를 사용안한다면 보여줄 필요는 없다.
				$info = Context::getDBInfo();
				if($info->use_mobile_vie=='Y'){
					$this->custom_menu->hidden_menu[] = strtolower('dispTextyleToolLayoutConfigMobileSkin');
				}

				Context::set('custom_menu', $this->custom_menu);


                // 숨김 메뉴를 요청할 경우 대시보드로 변경
                if($oTextyleModel->ishiddenMenu($this->act) || ($this->act == 'dispTextyleToolDashboard' && $oTextyleModel->isHiddenMenu(0)) ) {
                    if($oTextyleModel->isHiddenMenu(0)) Context::set('act', $this->act = 'dispTextyleToolPostManageList', true);
                    else Context::set('act', $this->act= 'dispTextyleToolDashboard', true);
                }

                $template_path = sprintf("%stpl",$this->module_path);
                $this->setTemplatePath($template_path);
                $this->setTemplateFile(str_replace('dispTextyleTool','',$this->act));

                if($_COOKIE['tclnb']) Context::addBodyClass('lnbClose');
                else Context::addBodyClass('lnbToggleOpen');

                // browser title 지정
                Context::setBrowserTitle($this->textyle->get('browser_title') . ' - admin');

            // textyle 서비스
            } else {

                if(!$preview_skin){
                    $oTextyleModel->checkTextylePath($this->module_srl, $this->module_info->skin);
                    $this->setTemplatePath($oTextyleModel->getTextylePath($this->module_srl));
                }
                else $this->setTemplatePath($this->module_path.'skins/'.$preview_skin);


                // Textyle에서 쓰기 위해 변수를 미리 정하여 세팅
                Context::set('root_url', Context::getRequestUri());
                Context::set('home_url', getFullSiteUrl($this->textyle->domain));
                Context::set('profile_url', getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleProfile'));
                Context::set('guestbook_url', getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleGuestbook'));
                Context::set('tag_url', getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleTag'));
                if(Context::get('is_logged')) Context::set('admin_url', getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleToolDashboard'));
                else Context::set('admin_url', getSiteUrl($textyle->domain,'','mid','textyle','act','dispTextyleToolLogin'));
                Context::set('textyle_title', $this->textyle->get('textyle_title'));
                if($this->textyle->get('post_use_prefix')=='Y' && $this->textyle->get('post_prefix')) Context::set('post_prefix', $this->textyle->get('post_prefix'));
                if($this->textyle->get('post_use_suffix')=='Y' && $this->textyle->get('post_suffix')) Context::set('post_suffix', $this->textyle->get('post_suffix'));

				$extra_menus = array();				
				$args->site_srl = $this->site_srl;
				$output = executeQueryArray('textyle.getExtraMenus',$args);
				if($output->toBool() && $output->data){
					foreach($output->data as $i => $menu){
						$extra_menus[$menu->name] = getUrl('','mid',$menu->mid);
					}
				}

                // 추가 메뉴
                Context::set('extra_menus', $extra_menus);

                // browser title 지정
                Context::setBrowserTitle($this->textyle->get('browser_title'));
            }

            $template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
            if(!is_dir($template_path)||!$this->module_info->mskin) {
                $this->module_info->mskin = 'default';
                $template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
            }
            $this->setTemplatePath($template_path);
		}

		function dispTextyle(){
            $oTextyleModel = &getModel('textyle');
            $oTextyleController = &getController('textyle');
            $oDocumentModel = &getModel('document');

            $args->category_srl = Context::get('category_srl');
            $args->search_target = Context::get('search_target');
            $args->search_keyword = Context::get('search_keyword');
			$args->module_srl = $this->module_srl;
			$args->site_srl = $this->site_srl;

            $args->page = Context::get('page');
            $args->page = $args->page>0 ? $args->page : 1;
            Context::set('page',$args->page);

            // set category
            $category_list = $oDocumentModel->getCategoryList($this->module_srl);
            Context::set('category_list', $category_list);

			$document_srl = Context::get('document_srl');

			// 글 고유 링크가 있으면 처리
			if($document_srl) {
				$oDocument = $oDocumentModel->getDocument($document_srl,false,false);

				// 문서가 있으면 처리
				if($oDocument->isExists()) {
					// 글과 요청된 모듈이 다르다면 오류 표시
					if($oDocument->get('module_srl')!=$this->module_info->module_srl ) return $this->stop('msg_invalid_request');

					// html title에 글제목 추가
					Context::setBrowserTitle($this->textyle->get('browser_title') . ' »  ' . $oDocument->getTitle());

					// meta keywords category + tag
					$tag_array = $oDocument->get('tag_list');
					if($tag_array) {
						$tag = htmlspecialchars(join(', ',$tag_array));
					} else {
						$tag = '';
					}
					$category_srl = $oDocument->get('category_srl');
					if($tag && $category_srl >0) $tag = $category_list[$category_srl]->title .', ' . $tag;
					Context::addHtmlHeader(sprintf('<meta name="keywords" content="%s" />',$tag));

					// 관리 권한이 있다면 권한을 부여
					if($this->grant->manager) $oDocument->setGrant();

				// 요청된 문서번호의 문서가 없으면 document_srl null 처리 및 경고 메세지 출력
				} else {
					Context::set('document_srl','',true);
					$oDocument = $oDocumentModel->getDocument(0,false,false);
				}

			}

			if(!$document_srl){
				if($args->category_srl || ($args->search_target && $args->search_keyword)){
					$args->list_count = 10;
					$output = $oDocumentModel->getDocumentList($args, false, false);
					$document_list = $output->data;
					Context::set('page_navigation', $output->page_navigation);
					Context::set('document_list', $document_list);

					$this->setTemplateFile('list');
				}
			}


			if((!$oDocument || !$oDocument->isExists()) && !$document_list){
                $args->list_count = 1;
                $output = $oDocumentModel->getDocumentList($args, false, false);
                if($output->data && count($output->data)) $oDocument = array_pop($output->data);
			}

			if($oDocument && $oDocument->isExists()){
				$args->document_srl = $oDocument->document_srl;
				$output = executeQuery('textyle.getNextDocument', $args);
				if($output->data->document_srl) Context::set('prev_document', new documentItem($output->data->document_srl,false));
				$output = executeQuery('textyle.getPrevDocument', $args);
				if($output->data->document_srl) Context::set('next_document', new documentItem($output->data->document_srl,false));

				Context::set('oDocument', $oDocument);
				$this->setTemplateFile('read');

				Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');
			}
		}	

        function dispTextyleProfile() {
			parent::dispTextyleProfile();
			$this->setTemplateFile('profile');
		}

		function dispTextyleCommentReply() {
			parent::dispTextyleCommentReply();
			$this->setTemplateFile('comment_form');
		}
		
		function dispTextylePasswordForm() {
			$type = Context::get('type');

			if($type=='delete_comment'){
				$callback_url = getUrl('','document_srl',Context::get('document_srl'
			));
			}elseif($type=='delete_guestbook'){
				$callback_url = getUrl('','act','dispTextyleGuestbook','mid',$this->mid);
			}
			Context::set('callback_url', $callback_url);
			$this->setTemplateFile('input_password_form');
		}

		function dispTextyleGuestbookWrite() {
			$textyle_guestbook_srl = Context::get('textyle_guestbook_srl');
			if($textyle_guestbook_srl){
				$oTextyleModel = &getModel('textyle');
				$output = $oTextyleModel->getTextyleGuestbook($textyle_guestbook_srl);
				$guestbook_list = $output->data;
				if(is_array($guestbook_list) && count($guestbook_list)){
					if(!$guestbook_list[0]->parent_srl) Context::set('guestbook',$guestbook_list[0]);
				}
			}

            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_guestbook.xml');
			$this->setTemplateFile('guestbook_form');
		}

        function dispTextyleGuestbook() {
            $page = Context::get('page');
            $page = $page ? $page : 1;
            Context::set('page',$page);

            $args->module_srl = $this->module_srl;
            $args->search_text = Context::get('search_text');
            $args->page = $page;
			$args->list_count = $this->textyle->getGuestbookListCount();

            $oTextyleModel = &getModel('textyle');
            $output = $oTextyleModel->getTextyleGuestbookList($args);
            Context::set('guestbook_list',$output->data);
            Context::set('page_navigation', $output->page_navigation);

			$this->setTemplateFile('guestbook');
		}

		function dispTextyleCategory() {
            $oDocumentModel = &getModel('document');
            $category_list = $oDocumentModel->getCategoryList($this->module_srl);
            Context::set('category_list', $category_list);

			$this->setTemplateFile('category');
		}

		function getTextyleCommentPage() {
			$document_srl = Context::get('document_srl');
			$oDocumentModel =& getModel('document');
			if(!$document_srl) return new Object(-1, "msg_invalid_request");
			$oDocument = $oDocumentModel->getDocument($document_srl);
			if(!$oDocument->isExists()) return new Object(-1, "msg_invalid_request");
			Context::set('oDocument', $oDocument);
			$oTemplate = new TemplateHandler;
			$html = $oTemplate->compile($this->getTemplatePath(), "comment.html");
			$this->add("html", $html);
		}

		function dispTextylePostWrite(){
            $oDocumentModel = &getModel('document');

			$document_srl = Context::get('document_srl');
			if($document_srl){
				$oDocument = $oDocumentModel->getDocument($document_srl,false,false);
			}
			if(!$oDocument || $oDocument->isExists()){
				$oDocument = $oDocumentModel->getDocument(0,false,false);
			}

			Context::set('oDocument',$oDocument);

            $category_list = $oDocumentModel->getCategoryList($this->module_srl);
            Context::set('category_list', $category_list);

			$this->setTemplateFile('post_form');
            Context::addJsFilter($this->template_path.'filter', 'insert_mpost.xml');
		}

		function dispTextyleMenu(){
			$menu = array();				
            $menu['Home'] = getFullSiteUrl($this->textyle->domain);
			$menu['Profile'] = getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleProfile');
            $menu['Guestbook'] = getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleGuestbook');
            //$menu['Tags'] = getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextyleTag');

			$args->site_srl = $this->site_srl;
			$output = executeQueryArray('textyle.getExtraMenus',$args);
			if($output->toBool() && $output->data){
				foreach($output->data as $v){
					$menu[$v->name] = getUrl('','mid',$v->mid);
				}
			}
	
			$logged_info = Context::get('logged_info');
			if($logged_info->is_site_admin) $menu['Write Post'] = getSiteUrl($this->textyle->domain,'','mid',$this->module_info->mid,'act','dispTextylePostWrite');
			Context::set('menu',$menu);
			$this->setTemplateFile('menu');
		}

		function procTextylePostWrite(){
			$logged_info = Context::get('logged_info');
			if(!$logged_info->is_site_admin) return new Object(-1,'msg_invalid_request');

			$args = Context::getRequestVars();
			$args->module_srl = $this->module_srl;
			$oTextyleController = &getController('textyle');
			$output = $oTextyleController->insertPost($args);
			return $output;
		}
	}


?>
