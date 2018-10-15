<?php 
namespace Classes\Model;

use \Classes\DB\Sql;
use \Classes\Model;
use \Classes\Model\User;

class Cart extends Model{
	const SESSION = "Cart";
	
	public static function getFromSession(){
		//verifica se precisa inserir um carrinho novo, se ja tem esse carrinho, 
		$cart = new Cart();
		
		//esse carrinho ja está na sessão?? 
		//   vê se a sessão foi definida e se o carrinho está na sessão!
		if ( isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 ){
			//se entrar aqui é pq existe uma sessão definida e o carrinho está na sessão e já foi inserido no banco.
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);//carrega o id do carrinho no objeto carrinho;
		}else{
			$cart->getFromSessionID();
			if ( !(int)$cart->getidcart() > 0 ){
				$data = [
					'dessessionid' => SESSION_ID()					
				];
				if ( User::checkLogin(false) ){
					$user = User::getFromSession();
					$data['iduser'] = $user->getiduser();
				}				
				$cart->setData($data);
				$cart->save();
				$cart->setToSession();				
			}
		}
		return $cart;
	}
	
	public function setToSession(){
		$_SESSION[Cart::SESSION] = $this->getValues();
	}
	
	public function getFromSessionID(){
		$sql = new Sql();		
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
							          ":dessessionid" => SESSION_ID() ]);		
		if ( Count($results) > 0 ){
			$this->setData($results[0]);
		}
	}
	
	public function get(int $idcart){
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
							":idcart" => $idcart]);
		
		if (Count($results) > 0){
			$this->setData($results[0]);
		}
		
	}
	
	public function save(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, 
		                                            :iduser, :deszipcode,
													:vlfreight, :nrdays)", [
		":idcart"       => $this->getidcart(),
		":dessessionid" => $this->getdessessionid(),
		":iduser"       => $this->getiduser(),
		":deszipcode"   => $this->deszipcode(),
		":vlfreight"    => $this->getvlfreight(),
		":nrdays"       => $this->getnrdays(),]);
		
		$this->setData($results[0]);
		
	}
	
	
}
?>