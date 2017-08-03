<?php
namespace SRESTO;
class RESTResponse extends Response{
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
		else if($this->isObject($msg)){
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
}