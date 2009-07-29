<?php
    class metaWebLog {
        var $url = null;
        var $user_id = null;
        var $password = null;

        function metaWebLog($url, $user_id = null, $password = null) {
            $this->url = $url;
            $this->user_id = $user_id;
            $this->password = $password;
        }

        function getSiteInfo() {
            $body = $this->_request($this->url);
            if(!$body) return null;

            preg_match('/<title>(.+)<\/title>/i', $body, $matches);
            $title = $matches[1];

            preg_match('/<link(.+)rel="EditURI"(.+)>/i', $body, $matches);
            if($matches[2]) {
                preg_match('/href=\"(.+)"/i',$matches[2],$matches);
                $blogapi_url = $matches[1];
                if(!preg_match('/^(http|https)/',$blogapi_url)) $blogapi_url= 'http://'.$blogapi_url;
            } else {
                $blogapi_url = '';
            }

            $output->title = $title;
            $output->blogapi_url = $blogapi_url;
            return $output;

        }

        function getUsersBlogs() {
            $oXmlParser = new XmlParser();
            
            $input = sprintf(
                '<?xml version="1.0" encoding="utf-8" ?><methodCall><methodName>blogger.getUsersBlogs</methodName><params><param><value><string>%s</string></value></param><param><value><string>%s</string></value></param><param><value><string>%s</string></value></param></params></methodCall>',
                'textyle',
                $this->user_id,
                $this->password
            );
            $output = $this->_request($this->url, $input, 'application/octet-stream','POST');
            $xmlDoc = $oXmlParser->parse($output);

            if(isset($xmlDoc->methodresponse->fault)) {
                $code = $xmlDoc->methodresponse->fault->value->struct->member[0]->value->int->body;
                $message = $xmlDoc->methodresponse->fault->value->struct->member[1]->value->string->body;
                return new Object($code, $message);
            } 

            $val = $xmlDoc->methodresponse->params->param->value->array->data->value->struct->member;
            $output = new Object();
            $output->add('url', $val[0]->value->string->body);
            $output->add('blogid', $val[1]->value->string->body);
            $output->add('name', $val[2]->value->string->body);
            return $output;
        }

        function newMediaObject($blogid, $filename, $source_file) {
            $oXmlParser = new XmlParser();
            if(preg_match('/\.(jpg|gif|jpeg|png)$/i',$filename)) {
                list($width, $height, $type, $attrs) = @getimagesize($source_file);
                switch($type) {
                    case '1' :
                        $type = 'image/gif';
                        break;
                    case '2' :
                        $type = 'image/jpeg';
                        break;
                    case '3' :
                        $type = 'image/png';
                        break;
                    case '6' :
                        $type = 'image/bmp';
                        break;
                }
            }

            $input = sprintf('<?xml version="1.0" encoding="utf-8"?><methodCall><methodName>metaWeblog.newMediaObject</methodName><params><param><value><string>%s</string></value></param><param><value><string>%s</string></value></param><param><value><string>%s</string></value></param><param><value><struct><member><name>name</name><value><string>%s</string></value></member><member><name>type</name><value><string>%s</string></value></member><member><name>bits</name><value><base64>%s</base64></value></member></struct></value></param></params></methodCall>',
                $blogid,
                $this->user_id,
                $this->password,
                str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),$filename),
                $type,
                base64_encode(FileHandler::readFile($source_file))
            );
            $output = $this->_request($this->url, $input,  'application/octet-stream','POST');
            $xmlDoc = $oXmlParser->parse($output);

            if(isset($xmlDoc->methodresponse->fault)) {
                $code = $xmlDoc->methodresponse->fault->value->struct->member[0]->value->int->body;
                $message = $xmlDoc->methodresponse->fault->value->struct->member[1]->value->string->body;
                return new Object($code, $message);
            } 

            $target_file = $xmlDoc->methodresponse->params->param->value->struct->member->value->string->body;
            $output = new Object();
            $output->add('target_file',$target_file);
            return $output;

        }

        function newPost($blogid, $oDocument) {
            $oXmlParser = new XmlParser();
            $oDocumentModel = &getModel('document');
            $category_list = $oDocumentModel->getCategoryList($oDocument->get('module_srl'));

            $input = sprintf('<?xml version="1.0" encoding="utf-8"?><methodCall><methodName>metaWeblog.newPost</methodName><params><param><value><string>%s</string></value></param><param><value><string>%s</string></value></param><param><value><string>%s</string></value></param><param><value><struct><member><name>title</name><value><string>%s</string></value></member><member><name>description</name><value><string>%s</string></value></member><member><name>categories</name><value><array><data><value><string>%s</string></value></data></array></value></member><member><name>tagwords</name><value><array><data><value><string>%s</string></value></data></array></value></member></struct></value></param><param><value><boolean>1</boolean></value></param></params></methodCall>',
                    $blogid,
                    $this->user_id,
                    $this->password,
                    str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),$oDocument->get('title')),
                    str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),$oDocument->get('content')),
                    str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),$category_list[$oDocument->get('category_srl')]->title),
                    str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),$oDocument->get('tags'))
            );
            $output = $this->_request($this->url, $input,  'application/octet-stream','POST');
            $xmlDoc = $oXmlParser->parse($output);

            if(isset($xmlDoc->methodresponse->fault)) {
                $code = $xmlDoc->methodresponse->fault->value->struct->member[0]->value->int->body;
                $message = $xmlDoc->methodresponse->fault->value->struct->member[1]->value->string->body;
                return new Object($code, $message);
            } 
            return new Object();
        }


        function _request($url, $body = null, $content_type = 'text/html', $method='GET', $headers = array(), $cookies = array()) {
            set_include_path(_XE_PATH_."libs/PEAR");
            require_once('PEAR.php');
            require_once('HTTP/Request.php');

            $url_info = parse_url($url);
            $host = $url_info['host'];

            if(__PROXY_SERVER__!==null) {
                $oRequest = new HTTP_Request(__PROXY_SERVER__);
                $oRequest->setMethod('POST');
                $oRequest->addPostData('arg', serialize(array('Destination'=>$url, 'method'=>$method, 'body'=>$body, 'content_type'=>$content_type, "headers"=>$headers)));
            } else {
                $oRequest = new HTTP_Request($url);
                if(count($headers)) {
                    foreach($headers as $key => $val) {
                        $oRequest->addHeader($key, $val);
                    }
                }
                if($cookies[$host]) {
                    foreach($cookies[$host] as $key => $val) {
                        $oRequest->addCookie($key, $val);
                    }
                }
                if(!$content_type) $oRequest->addHeader('Content-Type', 'text/html');
                else $oRequest->addHeader('Content-Type', $content_type);
                $oRequest->setMethod($method);
                if($body) $oRequest->setBody($body);
            }

            $oResponse = $oRequest->sendRequest();

            $code = $oRequest->getResponseCode();
            $header = $oRequest->getResponseHeader();
            $response = $oRequest->getResponseBody();
            if($c = $oRequest->getResponseCookies()) {
                foreach($c as $k => $v) {
                    $cookies[$host][$v['name']] = $v['value'];
                }
            }
            if($code > 300 && $code < 399 && $header['location']) {
                return $this->_request($header['location'], $body, $content_type, $method, $headers, $cookies);
            }

            if($code != 200) return;

            return $response;
        }

    }
?>
