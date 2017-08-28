<?php
namespace SRESTO\Response;
use SRESTO\Utils\Util;
class RESTResponse extends HTTPResponse{
	/*public function __construct(){
		parent::__construct();
	}*/
	public function paginate($list,$count,$page,$limit){
		return $this->header([
				'Pagination-Count'=>$count,
				'Pagination-Page'=>$page,
				'Pagination-Limit'=>$limit])
				->json($list);
	}
	public function created($msg=NULL){
		if($msg==NULL) $msg='Created successfully';
		return $this->status(201)->message($msg);
	}
	public function updated($msg=NULL){
		if($msg==NULL)
			$this->status(204);
		else if(Util::isObject($msg))
			$this->status(200)->json($msg);
		else
			$this->status(200)->message($msg);
		return $this;
	}
	public function deleted(){
		return $this->status(204);
	}
	public function validationError($error_list){
		return $this->status(400)->json(array("message"=>"Validation errors in your request","errors"=>$error_list));
	}
	public function noCache($pragma=true){
		if($pragma)
			$this->header('Pragma','no-cache');
		return $this->header('Cache-Control','no-store');
	}
	public function sendToken($access_token,$refresh_token=null,$type='Bearer',$expires_in=3600){
		return $this->json([
                "access_token"=>$access_token,
                "token_type"=>$type,
                "expires_in"=>$expires_in,
                "refresh_token"=>$refresh_token
            ]);
	}
	public function sendError($code,$error,$error_description=null){
		return $this->status($code)->noCache(false)->json([
			'error'=>$error,
			'error_description'=>$error_description
		]);
	}
}