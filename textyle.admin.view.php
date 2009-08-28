<?php
    /**
     * @class  textyleAdminView
     * @author sol (sol@ngleader.com)
     * @brief  textyle 모듈의 admin view class
     **/

    class textyleAdminView extends textyle {

        /**
         * @brief 초기화
         **/
        function init() {
            $oTextyleModel = &getModel('textyle');

            $this->setTemplatePath($this->module_path."/tpl/");
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }

        function dispTextyleAdminList() {

            $page = Context::get('page');
            if(!$page) $page = 1;

            $oTextyleModel = &getModel('textyle');
            $output = $oTextyleModel->getTextyleList(20, $page, 'regdate');
            if(!$output->toBool()) return $output;

            Context::set('textyle_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('list');
        }

        function dispTextyleAdminInsert() {
			$oModuleModel = &getModel('module');

            $module_srl = Context::get('module_srl');

            if($module_srl) {
                $oTextyleModel = &getModel('textyle');
				$textyle = $oTextyleModel->getTextyle($module_srl);
                Context::set('textyle', $textyle);

				$admin_list = $oModuleModel->getSiteAdmin($textyle->site_srl);
				$site_admin = array();	
				if(is_array($admin_list)){
					foreach($admin_list as $k => $v){
						$site_admin[] = $v->user_id;
					}

					Context::set('site_admin', join(',',$site_admin));
				}
			}

			$skin_list = $oModuleModel->getSkins($this->module_path);
			Context::set('skin_list',$skin_list);

            $this->setTemplateFile('insert');
        }

        function dispTextyleAdminDelete() {
            if(!Context::get('module_srl')) return $this->dispTextyleAdminList();
            $module_srl = Context::get('module_srl');

            $oTextyleModel = &getModel('textyle');
            $oTextyle = $oTextyleModel->getTextyle($module_srl);
            $textyle_info = $oTextyle->getObjectVars();

            $oDocumentModel = &getModel('document');
            $document_count = $oDocumentModel->getDocumentCount($textyle_info->module_srl);
            $textyle_info->document_count = $document_count;

            Context::set('textyle_info',$textyle_info);

            // 템플릿 파일 지정
            $this->setTemplateFile('textyle_delete');
        }
    }

?>
