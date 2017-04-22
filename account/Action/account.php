<?php
class account extends Action{
	public $obj_user;
	public $obj_user_favorite;
	
	function __construct() {
		parent::__construct();
		
		$this->obj_user = load("account_users");
		$this->obj_user_favorite = load("account_user_favorite");
		
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
// 		if(empty($userid)){
// 			go("/");
// 		}
		$this->assign("userid", $userid);
		$user_type_arr = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
		$user_type = $user_type_arr['account_type'];
		
		$this->assign("user_type", $user_type);
	}
	
	function ACT_index(){
		go("/");
	}
	
	function ACT_register(){
		
	}
	
	function ACT_signup(){
		/*
		 * Array
			(
			    [username] => michael
			    [email] => chenzixing2009@gmail.com
			    [pswd] => Abc1234567
			    [tel] => 4089812398
			    [register-submit] => 马上注册
			)
		 */
		if(isset($_POST['register-submit'])){
			$email = strtolower($_POST['email']);
			$user_register_info = array(
					"reg_time" => times::getTime(),
					"login_time" => times::getTime(),
					"username" => $_POST['username'],
					"email" => $email,
					"pswd" => $_POST['password'],
					"tel" => isset($_POST['gender'])?$_POST['gender']:"",
					"ip" => http::getIP(),
					"account_type"=> $_POST['account_type'],
					"isdesigner" => 0,
					"status" => 1,
					"level" => 0,
					);
			
			//debug::d($user_register_info);exit;
			$if_exist = $this->obj_user->getOne("*", array("email"=>$email));
			if(!empty($if_exist)){
				$error_msg = "该邮件已经注册！请选择其他邮箱，谢谢！";
				$this->assign("error_msg", $error_msg);
			}else{
				$res = $this->obj_user->Insert($user_register_info);
				
				if($res){
					go("/account/account.php?act=register_success");
				}else{
					$error_msg = "对不起哦亲~ 没有注册成功，请再次尝试，谢谢！";
					$this->assign("error_msg", $error_msg);
				}
			}
			
		}else{
			go("/");
		}
		
	}
	
	function ACT_register_success(){
		
	}
	
	function ACT_activate(){
		
	}
	
	function ACT_login(){
		/*
		 * Array
			(
			    [email] => chenzixing2009@gmail.com
			    [password] => Abc1234567
			    [login-submit] => 登 录
			)
					 
		 */
		
		if(isset($_POST) && !empty($_POST["email"]) && !empty($_POST["password"])){
			
			$result = $this->obj_user->getOne(array("uid", "pswd"), array("email"=>dbtools::escape($_POST["email"]), "visible"=>1));
// 			debug::d($result);exit;
			
			if(isset($result) && $result["pswd"] == $_POST["password"]){
				$_SESSION["userid"] = $result["uid"];
				
				go("/");
			}else{
				//邮箱或者密码不对
				$error_msg = "邮箱或者密码不对";
				$this->assign("error_msg", $error_msg);
			}
		}
		
		
	}
	
	function ACT_logout(){
		
		if(isset($_SESSION['userid'])){
			unset($_SESSION['userid']);
			go("/");
		}
	}
	
	function ACT_reset(){
		
	}
	
	function ACT_forget_pwd(){
		
	}
	
	function ACT_forget_pwd_process(){
		if(isset($_POST) && !empty($_POST['email'])){
			$email = trim($_POST['email']);
			$pwd_arr = $this->obj_user->getOne("*", array("email"=>$email, "visible"=>1));
			
			if(isset($pwd_arr) && !empty($pwd_arr))
			{
				$recipient = $pwd_arr['pswd'];
				if($this->sendmail($email, $recipient)){
					go("/account/account.php?act=login");
				}
				else{
					go("/");
				}
			}
			else{
				go("/");
			}
		}
		else{
			go("/");
		}
			
	}
	
// 	function ACT_test(){
// 		$recipient_mail = "chenzixing2009@gmail.com";
// 		$recipient = "michael & ginger";
		
// 		if($this->sendmail($recipient_mail, $recipient)){
// 			$res = "mail sent successfully!";
// 			$this->assign("res", $res);
// 		}
// 		else{
// 			$res = "mail sent unsuccessfully!";
// 			$this->assign("res", $res);
// 		}
// 	}
	
	function sendmail($recipient_mail, $recipient){
		require DOCUROOT . '/phpmailer/PHPMailerAutoload.php';
		
		$mail = new PHPMailer;
		
		// $mail->SMTPDebug = 3;                               // Enable verbose debug output
		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'wowjob2017@gmail.com';                 // SMTP username
		$mail->Password = 'Abc1234567';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to
		
		$mail->setFrom('michael@wowkb.com', 'wowkb admin');
		// $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
		$mail->addAddress($recipient_mail);               // Name is optional
		
		
		$mail->isHTML(true);                                  // Set email format to HTML
		
		// $mail->SMTPOptions = array(
		// 		'ssl' => array(
		// 				'verify_peer' => false,
		// 				'verify_peer_name' => false,
		// 				'allow_self_signed' => true
		// 		)
		// );
		
		$mail->Subject = 'Your password in wowkb.com : ' . $recipient;
		$mail->Body    = 'Your password in wowkb.com : ' . $recipient;
		$mail->AltBody = 'Your password in wowkb.com : ' . $recipient;
		
		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
			return false;
		} else {
			echo 'Message has been sent';
			
			return true;
		}
	}
	
	function ACT_apply_supplier(){
		
	}
	
	function ACT_apply_designer(){
		
	}
	
	
}