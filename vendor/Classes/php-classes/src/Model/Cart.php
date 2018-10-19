<?php 
namespace Classes\Model;

use \Classes\DB\Sql;
use \Classes\Model;
use \Classes\Model\User;

class Cart extends Model{
	const SESSION       = "Cart";
	const SESSION_ERROR = "CartError";
	
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
	
	public static function removeToSession(){
		$_SESSION[Cart::SESSION] = NULL;		
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
		":deszipcode"   => $this->getdeszipcode(),
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
		$this->getCalculateTotal();
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
		$this->getCalculateTotal();
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
	
	public function getProductsTotals(){
		$sql = new Sql();
		$results = $sql->select("SELECT Case When Prod.vlprice Is Null Then 0 
											 Else SUM(prod.vlprice) End vlprice,  
									    Case When prod.vlwidth Is Null Then 0 
										     Else SUM(prod.vlwidth) End vlwidth,
									    Case When prod.vlheight Is Null Then 0 
									  		 Else SUM(prod.vlheight) End vlheight, 
									    Case When prod.vllength Is Null Then 0 
											 Else SUM(prod.vllength) End vllength,
									    Case When prod.vlweight Is Null Then 0 
											 Else SUM(prod.vlweight) End vlweight, 
									    COUNT(*) AS nrqtd										
								 FROM tb_products prod 
								 INNER JOIN tb_cartsproducts cart_prod USING(idproduct)
								 WHERE cart_prod.idcart = :idcart AND dtremoved IS NULL;", [
									":idcart" => $this->getidcart() ]);
		if(count($results) > 0){
			return $results[0];			
		}else{
			return [];
		}
	}
	
	public function setFreight($nrzipcode){
		$nrzipcode = str_replace('-','',$nrzipcode);
		$totals = $this->getProductsTotals();
		if ( $totals['nrqtd'] > 0 ){
			if ($totals['vlwidth'] < 2 ) $totals['vlwidth'] = 2;
			if ($totals['vllength'] < 16 ) $totals['vllength'] = 16;
			$qs = http_build_query([
					'nCdEmpresa'          => '',
					'sDsSenha'            => '',
					'nCdServico'          => '40010',
					'sCepOrigem'          => '59625485',
					'sCepDestino'         => $nrzipcode, //'09853120',
					'nVlPeso'             => $totals['vlweight'],
					'nCdFormato'          => '1',
					'nVlComprimento'      => $totals['vllength'],
					'nVlAltura'           => $totals['vlheight'],
					'nVlLargura'          => $totals['vlwidth'],
					'nVlDiametro'         => '0',
					'sCdMaoPropria'       => 'S',
					'nVlValorDeclarado'   => $totals['vlprice'],
					'sCdAvisoRecebimento' => 'S'	]);			
			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			$result = $xml->Servicos->cServico;	
			
			if ($result->MsgErro != ''){
				Cart::setMsgError($result->MsgErro);				
			}else{
				Cart::clearMsgError();
			}
			
			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));			
			$this->setdeszipcode($nrzipcode);
			$this->save();
			
			return $result;
		}else{
			
		}		
	}
	
	public static function formatValueToDecimal($value):float{
		$value = str_replace('.','',$value);
		return str_replace(',','.',$value);
	}
		
	public static function setMsgError($msg){
		$_SESSION[Cart::SESSION_ERROR] = $msg;
	}
		
	public static function getMsgError(){
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
		Cart::clearMsgError();
		return $msg;
	}
		
	public static function clearMsgError(){
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}
	
	public function updateFreight(){
		if(is_null($this->getvlfreight())){
			$this->setvlfreight(0);
		}
		if(is_null($this->getnrdays())){
			$this->setnrdays(0);
		}
		
		if($this->getdeszipcode() != ''){
			$this->setFreight($this->getdeszipcode());			
		}
	}
	
	public function getValues(){
		$this->getCalculateTotal();
		return parent::getValues();
	}
	
	public function getCalculateTotal(){		
		$this->updateFreight();
		$totals = $this->getProductsTotals();
		
		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());
	}
	
	public function checkZipCode(){
		$products = $this->getProducts();
		if(!Count($products) > 0){
			$this->setdeszipcode('');
		}
	}

	
	
}
?>