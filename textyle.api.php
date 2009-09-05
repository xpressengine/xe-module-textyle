<?php
    /**
     * @class  textyleAPI
     * @author sol(sol@ngleader.com)
     * @brief  textyle 모듈의 View Action에 대한 API 처리
     **/

    class textyleAPI extends textyle {

        /**
         * @brief check alias
         **/
        function dispTextylePostCheckAlias(&$oModule) {
            $oModule->add('document_srl',Context::get('document_srl'));
        }

    }

?>
