<?php
namespace SRESTO\Response;
use SRESTO\Utils\Util;
class RESTResponse extends HTTPResponse{
	/*public function __construct(){
		parent::__construct();
	}*/
	public function paginate($list,$count,$page,$limit){
		$this->headers['Pagination-Count']=$count;
		$this->headers['Pagination-Page']=$page;
		$this->headers['Pagination-Limit']=$limit;
		$this->json($list);
		return $this;
	}
	public function created($msg=NULL){
		if($msg==NULL) $msg='Created successfully';
		$this->status=201;
		$this->message($msg);
		return $this;
	}
	public function updated($msg=NULL){
		if($msg==NULL) $this->status=204;
		else if(Util::isObject($msg)){
			$this->status=200;
			$this->json($msg);
		}else{
			$this->status=200;
			$this->message($msg);
		}
		return $this;
	}
	public function deleted(){
		$this->status=204;
		$this->response='';
		return $this;
	}
	public function validationError($error_list){
		$this->status=400;
		$this->json(array("message"=>"Validation errors in your request","errors"=>$error_list));
		return $this;
	}
	public function noCache(){
		$this->header('Cache-Control','no-store');
		$this->header('Pragma','no-cache');
		return $this;
	}
	public function sendToken($access_token,$refresh_token=null,$type='Bearer',$expires_in=3600){
		$this->json([
                "access_token"=>$access_token,
                "token_type"=>$type,
                "expires_in"=>$expires_in,
                "refresh_token"=>$refresh_token
            ]);
		return $this;
	}
	public function sendError($code,$error,$error_description=null){
		$this->status=$code;
		$this->header('Cache-Control','no-store');
		$this->json([
			'error'=>$error,
			'error_description'=>$error_description
		]);
		return $this;
	}
}