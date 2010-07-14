<?php
    /**
     * @class  textyle
     * @author sol (sol@ngleader.com)
     * @brief  textyle 모듈의 high class
     **/

    require_once(_XE_PATH_.'modules/textyle/textyle.info.php');

    class textyle extends ModuleObject {

        /**
         * @berif default mid
         **/
        var $textyle_mid = 'textyle';

        /**
         * @berif default skin
         **/
        var $skin = 'happyLetter';
        var $mskin = 'default';

        // post list
        var $post_style = 'content';//,'summary','list'
        var $post_list_count = 1;

        // list count
        var $comment_list_count = 30;
        var $guestbook_list_count = 30;

        // guestbook and comment input require
        var $input_email = 'R';//,'Y','N;
        var $input_website = 'R';//'Y','N';
        var $post_editor_skin = "dreditor";

        var $post_use_prefix = 'Y';//'Y','N';
        var $post_use_suffix = 'Y';//'Y','N';

        var $search_option = array('title','content','title_content','comment','user_name','nick_name','user_id','tag'); ///< 검색 옵션
        var $order_target = array('list_order', 'update_order', 'regdate', 'voted_count', 'readed_count', 'comment_count', 'title'); // 정렬 옵션

        var $add_triggers = array(
            array('display', 'textyle', 'controller', 'triggerMemberMenu', 'before'),
            array('comment.insertComment', 'textyle', 'controller', 'triggerInsertComment', 'after'),
            array('comment.deleteComment', 'textyle', 'controller', 'triggerDeleteComment', 'after'),
            array('trackback.insertTrackback', 'textyle', 'controller', 'triggerInsertTrackback', 'after'),
            array('trackback.deleteTrackback', 'textyle', 'controller', 'triggerDeleteTrackback', 'after'),
            array('moduleHandler.proc', 'textyle', 'controller', 'triggerApplyLayout', 'after')
        );

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            $oModuleController = &getController('module');

            // $this->add_triggers 트리거 일괄 추가
            foreach($this->add_triggers as $trigger) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }

        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

            // $this->add_triggers 트리거 일괄 검사
            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
            }

            if(!$oDB->isColumnExists("textyle_api","blogapi_type")) return true;
            if(!$oDB->isColumnExists("textyle_api","blogapi_service")) return true;
            if(!$oDB->isColumnExists("textyle_api","blogapi_host_provider")) return true;
            if(!$oDB->isColumnExists("textyle_publish_logs","module_srl")) return true;
            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            // $this->add_triggers 트리거 일괄 업데이트
            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                    $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
                }
            }

            if(!$oDB->isColumnExists("textyle_api","blogapi_type")) $oDB->addColumn('textyle_api',"blogapi_type","varchar",50);
            if(!$oDB->isColumnExists("textyle_api","blogapi_service")) $oDB->addColumn('textyle_api','blogapi_service','varchar',250);
            if(!$oDB->isColumnExists("textyle_api","blogapi_host_provider")) $oDB->addColumn('textyle_api','blogapi_host_provider','varchar',250);

            if(!$oDB->isColumnExists("textyle_publish_logs","module_srl")){
                $oDB->addColumn('textyle_publish_logs',"module_srl","number",11);
                $oDB->addIndex("textyle_publish_logs","idx_module_srl", array("module_srl"));
            }

            return new Object(0, 'success_updated');
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }


        function checkXECoreVersion($requried_version){
			$result = version_compare(__ZBXE_VERSION__,$requried_version,'>=');
			if($result != 1) return false;

			return true;
        }
    }
?>
