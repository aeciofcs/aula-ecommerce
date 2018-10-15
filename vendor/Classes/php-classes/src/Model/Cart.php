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
	
	public function addProduct(Product $product){
		$sql = new Sql();
		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) 
					 VALUES(:idcart, :idproduct)", [
					 ":idcart"    => $this->getidcart(),
					 ":idproduct" => $product->getidproduct() ]);
	}
	
	public function removeProduct(Product $product, $all = false ){
		$sql = new Sql();
		if ($all){
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW()
						 WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
						 "idcart"    => $this->getidcart(),
						 "idproduct" => $product->getidproduct() ]);			
		}else{
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW()
						 WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
						 "idcart"    => $this->getidcart(),
						 "idproduct" => $product->getidproduct() ]);			
		}		
	}
	
	public function getProducts(){
		$sql = new Sql();
		return Product::checkList($sql->select("SELECT prod.idproduct, prod.desproduct, prod.vlprice, prod.vlwidth, 
													   prod.vlheight, prod.vllength, prod.vlweight, prod.desurl,
									                   COUNT(*) AS nrqtd,
									                   SUM(prod.vlprice) AS vltotal
							                    FROM tb_cartsproducts carts_prod
							                    INNER JOIN tb_products prod USING(idproduct)
							                    WHERE carts_prod.idcart = :idcart AND carts_prod.dtremoved IS NULL 
							                    GROUP BY prod.idproduct, prod.desproduct, prod.vlprice, prod.vlwidth,
								                         prod.vlheight, prod.vllength, prod.vlweight, prod.desurl
							                    ORDER BY prod.desproduct", [
														":idcart" => $this->getidcart()	]));
	}
}
?>