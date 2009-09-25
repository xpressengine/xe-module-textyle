<?php
    /**
     * @class  textyle
     * @author sol (sol@ngleader.com)
     * @brief  textyle 모듈의 high class
     **/

    require_once(_XE_PATH_.'modules/textyle/textyle.info.php');

    class textyle extends ModuleObject {

		var $search_option = array('title','content','title_content','comment','user_name','nick_name','user_id','tag'); ///< 검색 옵션

		var $order_target = array('list_order', 'update_order', 'regdate', 'voted_count', 'readed_count', 'comment_count', 'title'); // 정렬 옵션

		// default skin
		var $skin = "happyLetter";

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

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            $oModuleController = &getController('module');

			$oModuleController->insertTrigger('display', 'textyle', 'controller', 'triggerMemberMenu', 'before');
			$oModuleController->insertTrigger('comment.insertComment', 'textyle', 'controller', 'triggerInsertComment', 'after');
			$oModuleController->insertTrigger('comment.deleteComment', 'textyle', 'controller', 'triggerDeleteComment', 'after');
			$oModuleController->insertTrigger('trackback.insertTrackback', 'textyle', 'controller', 'triggerInsertTrackback', 'after');
			$oModuleController->insertTrigger('trackback.deleteTrackback', 'textyle', 'controller', 'triggerDeleteTrackback', 'after');
			$oModuleController->insertTrigger('moduleHandler.proc', 'textyle', 'controller', 'triggerApplyLayout', 'after');

        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oModuleModel = &getModel('module');

            if(!$oModuleModel->getTrigger('display', 'textyle', 'controller', 'triggerMemberMenu', 'before')) return true;
			if(!$oModuleModel->getTrigger('comment.insertComment', 'textyle', 'controller', 'triggerInsertComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('comment.deleteComment', 'textyle', 'controller', 'triggerDeleteComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('trackback.insertTrackback', 'textyle', 'controller', 'triggerInsertTrackback', 'after')) return true;
            if(!$oModuleModel->getTrigger('trackback.deleteTrackback', 'textyle', 'controller', 'triggerDeleteTrackback', 'after')) return true;
            if(!$oModuleModel->getTrigger('moduleHandler.proc', 'textyle', 'controller', 'triggerApplyLayout', 'after')) return true;
            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            if(!$oModuleModel->getTrigger('display', 'textyle', 'controller', 'triggerMemberMenu', 'before') )
                $oModuleController->insertTrigger('display', 'textyle', 'controller', 'triggerMemberMenu', 'before');
			if(!$oModuleModel->getTrigger('comment.insertComment', 'textyle', 'controller', 'triggerInsertComment', 'after') )
                $oModuleController->insertTrigger('comment.insertComment', 'textyle', 'controller', 'triggerInsertComment', 'after');
            if(!$oModuleModel->getTrigger('comment.deleteComment', 'textyle', 'controller', 'triggerDeleteComment', 'after') )
                $oModuleController->insertTrigger('comment.deleteComment', 'textyle', 'controller', 'triggerDeleteComment', 'after');
            if(!$oModuleModel->getTrigger('trackback.insertTrackback', 'textyle', 'controller', 'triggerInsertTrackback', 'after') )
                $oModuleController->insertTrigger('trackback.insertTrackback', 'textyle', 'controller', 'triggerInsertTrackback', 'after');
            if(!$oModuleModel->getTrigger('trackback.deleteTrackback', 'textyle', 'controller', 'triggerDeleteTrackback', 'after') )
                $oModuleController->insertTrigger('trackback.deleteTrackback', 'textyle', 'controller', 'triggerDeleteTrackback', 'after');
            if(!$oModuleModel->getTrigger('moduleHandler.proc', 'textyle', 'controller', 'triggerApplyLayout', 'after') )
                $oModuleController->insertTrigger('moduleHandler.proc', 'textyle', 'controller', 'triggerApplyLayout', 'after');

            return new Object(0, 'success_updated');
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }
    }


	// TODO : remove function 
	if(!function_exists('getFullUrl')){
		function getFullUrl() {
			$num_args = func_num_args();
			$args_list = func_get_args();
			$request_uri = Context::getRequestUri();
			if(!$num_args) return $request_uri;

			$url = Context::getUrl($num_args, $args_list);
			if(!preg_match('/^http/i',$url)){
				preg_match('/^(http|https):\/\/([^\/]+)\//',$request_uri,$match);
				$url = Context::getUrl($num_args, $args_list);
				return substr($match[0],0,-1).$url;
			}
			return $url;
		}

	}

	if(!function_exists('getFullSiteUrl')){
        function getFullSiteUrl() {
            $num_args = func_num_args();
            $args_list = func_get_args();

            $request_uri = Context::getRequestUri();
            if(!$num_args) return $request_uri;

            $domain = array_shift($args_list);
            $num_args = count($args_list);

            $url = Context::getUrl($num_args, $args_list, $domain);
            if(!preg_match('/^http/i',$url)){
                preg_match('/^(http|https):\/\/([^\/]+)\//',$request_uri,$match);
                return substr($match[0],0,-1).$url;
            }
            return $url;
        }

	}

?>
