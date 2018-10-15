<?php 
namespace Classes\Model;

use \Classes\DB\Sql;
use \Classes\Model;
use \Classes\Mailer;

class User extends Model{
	
	const SESSION = "User";
	const SECRET  = "LojaVirtual_AFCS";
	const ERROR   = "UserError"; 
	
	public static function checkLogin($inadmin = true){
		if ( !isset($_SESSION[User::SESSION]) || 
			 !$_SESSION[User::SESSION] || 
			 !(int)$_SESSION[User::SESSION]["iduser"] > 0 ){			
			//Não está logado!!
			return false;
		}else{
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){
				return true;				
			}else if( $inadmin === false ){
				return true;
			}else{
				return false;
			}
		}		
	}
	
	public static function getFromSession(){
		$user = new User();
		if( isset($_SESSION[User::SESSION]) && $_SESSION[User::SESSION]['iduser'] > 0 ){			
			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
	}
	
	public static function login($login, $password){
		$sql     = new Sql();
		$results = $sql->select("SELECT * FROM tb_users us
								 INNER JOIN tb_persons per USING(idperson)
		                         WHERE deslogin=:LOGIN", array(
										":LOGIN"=>$login ) );
		if ( count($results) === 0 ){
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
		
		$data = $results[0];
		
		if ( password_verify($password, $data["despassword"]) === true ){
			
			$user = new User();
			$data['desperson'] = utf8_encode($data['desperson']);
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();
			
			return $user;
			
		}else{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}
	
	public static function verifyLogin($inadmin = true){
		if( !User::checkLogin($inadmin) ){
			if($inadmin){
				header("Location: /admin/login");
			}else{
				header("Location: /login");
			}
			exit;
		}
	}
	
	public static function logout(){
		$_SESSION[User::SESSION] = null;
	}
	
	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users us 
		                     INNER JOIN tb_persons pe USING(idperson) 
					         ORDER BY pe.desperson");
	}
		
	public function save(){
		$sql     = new Sql();
		$results = $sql->select( "CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, 
		                                             :nrphone, :inadmin)", 
								             		  Array( ":desperson"   => utf8_decode($this->getdesperson()),
										                     ":deslogin"    => $this->getdeslogin(),
												             ":despassword" => User::getPasswordHash($this->getdespassword()),
												             ":desemail"    => $this->getdesemail(),
												             ":nrphone"     => $this->getnrphone(),
												             ":inadmin"     => $this->getinadmin() ));
		$this->setData($results[0]);
	}

	public function get($iduser){
		$sql     = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a 
		                         INNER JOIN tb_persons b USING(idperson) 
					             WHERE a.iduser = :iduser", array(
						           ":iduser" => $iduser )
								);
		$results[0]['desperson'] = utf8_encode($results[0]['desperson']);
		$this->setData($results[0]);						
	}
	
	public function update(){
		$sql     = new Sql();
		$results = $sql->select( "CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, 
		                                                   :nrphone, :inadmin)", 
								             		        Array( ":iduser"      => $this->getiduser(),
															       ":desperson"   => utf8_decode($this->getdesperson()),
										                           ":deslogin"    => $this->getdeslogin(),
												                   ":despassword" => User::getPasswordHash($this->getdespassword()),
												                   ":desemail"    => $this->getdesemail(),
												                   ":nrphone"     => $this->getnrphone(),
												                   ":inadmin"     => $this->getinadmin() ));
		$this->setData($results[0]);		
	}
	
	public function delete(){
		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:iduser)", array( 
					":iduser" => $this->getiduser() ));
	}
	
	public static function getForgot($email){
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_persons per 
		                         INNER JOIN tb_users us USING(idperson)
								 WHERE per.desemail = :email", array(
									":email" => $email));
		if ( count($results) === 0 ){
			throw new \Exception("Não foi possível recuperar a senha.");
		}else{
			$data = $results[0];
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array( 
							":iduser" => $data["iduser"],
							":desip"  => $_SERVER["REMOTE_ADDR"]));
			if ( count($results2) === 0 ){
				throw new \Exception("Não foi possível recuperar a senha.");				
			}else{
				$dataRecovery = $results2[0];
				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
				//$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				$code = openssl_encrypt($dataRecovery["idrecovery"], "aes-256-cbc", User::SECRET, 0, $iv);
				$result = base64_encode($iv.$code);
				
				$link = "http://www.lojavirtual.com.br/admin/forgot/reset?code=$result";
				$mailer = new Mailer($data["desemail"], 
									 $data["desperson"],
									 "Redefinir Senha da Loja Virtual",
									 "forgot",
									 array("name" => $data["desperson"],
										   "link" =>$link));
				$mailer->send();
				
				return $data;
			}
		}		
	}
	
	public static function validForgotDecrypt($code){
				
		$result = base64_decode($code); 
	    $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');        
		$iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');        
		$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);		
        
		$teste = base64_decode("inPDv0qdasDMSBCL7f83XZqOGY0dUJsSjcrU2pFcTQzZHVuUUE9PQ==");
		//var_dump($idrecovery);
		//var_dump($code, $iv);
		//exit;
		$sql = new Sql();
		$results = $sql->select("Select * From tb_userspasswordsrecoveries a
                                 INNER JOIN tb_users b USING(iduser)
                                 INNER JOIN tb_persons c USING(idperson)
                                 Where a.idrecovery = :idrecovery And a.dtrecovery is null And 
                                       Date_Add(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array( 
								           ":idrecovery" => $idrecovery) );
		if ( count($results) === 0 ){
			throw new \Exception("Não foi possível recuperar a senha.",1);
		}else{
			return $results[0];
		}
	}
	
	public static function setForgotUsed($idrecovery){
		$sql = new Sql();
		$sql->query("UPDATE tb_userspasswordsrecoveries 
		             SET dtrecovery = NOW()
					 WHERE idrecovery = :idrecovery", array(
						"idrecovery" => $idrecovery) );
	}
	
	public function setPassword($password){
		$sql = new Sql();
		$sql->query("UPDATE tb_users 
		             SET despassword = :password
					 WHERE iduser = :iduser", array(
						  "password" => $password,
						  "iduser"   => $this->getiduser() ));
	}
	
	public static function setError($msg){
		$_SESSION[User::ERROR] = $msg; 
	}
	
	public static function getError(){
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";
		User::clearError();
		return $msg;
	}
	
	public static function clearError(){
		$_SESSION[User::ERROR] = NULL;
	}
	
	public static function getPasswordHash($password){
		return password_hash($password, PASSWORD_DEFAULT, [ 'cost' => 12 ] );
	}
}
?>