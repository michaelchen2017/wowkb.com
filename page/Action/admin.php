<?php
class admin extends Action{
	public $post;
	public $post_pics;
	public $post_tags;
	
	function __construct(){
		parent::__construct();
		$this->post = load("page_post");
		$this->post_pics = load("page_postPics");
		$this->post_tags = load("page_postTags");
		$_SESSION ['UserLevel'] = 6; //模拟管理登录，用于debug
	}
	
	function ACT_index(){
		
	}
	
	function ACT_update(){
		
	}
	
	function ACT_delete(){
		
	}
}