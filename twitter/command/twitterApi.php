<?php
ini_set( 'display_errors', 1 );
include("../lib/dbController.php");
class twitterApi{
    var $consumerKey = 'CK5wGiWbgdxg3Ad5274ruXgJu';
    var $consumerSecret = 'RdeEeTRM4XvX3NUlWfdlaBDoRrwNYdq7EWzE2kZ3WZanWbBDot';
    var $accessToken = '896923592-wgBDv2ctx51BE57VKxViLNivlCyCn7hF4RJQYMbx';
    var $accessTokenSecret = '0yjLlrEN3uZwG3kuAsNJfTs32mX6LfKWL1Ondb2onJfMk';
    var $dir = '';
    var $twObj = '';
    var $dbObj = '';
    var $search_key = "#ZOZOTOWN -RT";
    var $sql = array();
    public function init(){
        //sakura server can not use server full path
        $this->dir = realpath(dirname(__FILE__));
        include($this->dir."/TwitterOAuth.php");

        $this->twObj = new TwitterOAuth(
                              $this->consumerKey,
                              $this->consumerSecret,
                              $this->accessToken,
                              $this->accessTokenSecret
                                     );
         //DB
         $this->dbObj = new dbController();
         //var_dump($this->dbObj->con);
    }
    public function getMaxId(){
	    return 0;
    }
    public function _get(){
        $options = array('q'=>$this->search_key, 'count'=>'1','include_entities'=>'1');
	    //$options['since_id'] = $this->getMaxId();
	    $json_data = $this->twObj->get(
            'search/tweets',
             $options
         );

         return $json_data;
    }
    public function _set($json){
	    $datas = $json->statuses;
	    $sts_cnt = count($datas);
	    for ($i = $sts_cnt-1; $i >= 0; $i--) {
		    $tagArray = array();
		    $mediaHttpArray = array();
		    $mediaHttpsArray = array();
		    $result = $datas[$i];

		    $tw_id = $result->id_str;
		    $tw_text = str_replace(PHP_EOL,"__RETURN__",$result->text);
		    $tw_user = $result->user->name;
		    foreach ($result->entities->media as $media){
			    $mediaHttpArray[] = $media->media_url;
		    }
		    $tw_img_url_http_1 = implode("__IMG__", $mediaHttpArray);
		    foreach ($result->entities->media as $media){
			    $mediaHttpsArray[] = $media->media_url_https;
		    }
		    $tw_img_url_https_1 = implode("__IMG__", $mediaHttpsArray);

		    foreach ($result->entities->hashtags as $hash){
			    $tagArray[] = $hash->text;
		    }
		    $tw_hashtag = implode("#",$tagArray);
		    $this->sql[] = sprintf("insert into tw_tweets(tw_id,tw_text,tw_user,tw_hash,tw_img_http,tw_img_https) values('%s','%s','%s','%s','%s','%s')",
		                                           $tw_id,$tw_text,$tw_user,$tw_hashtag,$tw_img_url_http_1,$tw_img_url_https_1);
		}
		//var_dump($this->sql);
    }
}
$twObj = new twitterApi();
$twObj->init();
$json = $twObj->_get();
$twObj->_set($json);
$twObj->dbObj->insQueryArray($twObj->sql);
?>
