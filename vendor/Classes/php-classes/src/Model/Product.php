<?php 
namespace Classes\Model;

use \Classes\DB\Sql;
use \Classes\Model;

class Product extends Model{
		
	CONST PATH = "/res/site/img/products/";				  
		
	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_products
					         ORDER BY desproduct");
	}
	
	public static function checkList($list){
		foreach($list as &$row){
			$prod = new Product();
			$prod->setData($row);
			$row = $prod->getValues();
		}
		return $list;
	}
	
	public static function formatPrice(){
		
	}

	public function save(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, 
		                                               :vlwidth, :vlheight, :vllength,
													   :vlweight, :desurl)", array(
				":idproduct"  => $this->getidproduct(),
				":desproduct" => $this->getdesproduct(),
				":vlprice"    => $this->getvlprice(),
				":vlwidth"    => $this->getvlwidth(),
				":vlheight"   => $this->getvlheight(),
				":vllength"   => $this->getvllength(),
				":vlweight"   => $this->getvlweight(),
				":desurl"     => $this->getdesurl() ));
		$this->setData($results[0]);		
	}
	
	public function get($idproduct){
		$sql     = new Sql();
		$results = $sql->select("SELECT * FROM tb_products
					             WHERE idproduct = :idproduct", array(
						           ':idproduct' => $idproduct ));
		$this->setData($results[0]);
	}
	
	public function delete(){
		$filename = $_SERVER['DOCUMENT_ROOT'] . Product::PATH . $this->getidproduct() . ".jpg";		
		$sql = new Sql();
		$sql->query("DELETE FROM tb_products
		             WHERE idproduct = :idproduct", array(
					    ":idproduct" => $this->getidproduct() ));
		if( file_exists($filename) ){
			unlink($filename);
		}
	}
		
	public function checkPhoto(){
		if ( file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
		                "res" . DIRECTORY_SEPARATOR . 
				  		"site" . DIRECTORY_SEPARATOR . 
						"img" . DIRECTORY_SEPARATOR . 
						"products" . DIRECTORY_SEPARATOR . 
						$this->getidproduct() . ".jpg" )){
							$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
						}else{
							$url = "/res/site/img/product.jpg";
						}
		return $this->setdesphoto($url);
	}
	
	public function getValues(){
		$this->checkPhoto();
		$values = parent::getValues();		
		return $values;
	}
	
	public function setPhoto($file){
		if ( !empty($file['name']) ){ 
			$extension = explode('.', $file['name']); //pego a extensão do arquivo que o usuario fez upload;
			$extension = end($extension);		
			switch($extension){
				case "jpg":
				case "jpeg":
					$image = imagecreatefromjpeg($file["tmp_name"]);
				break;
			
				case "gif": 
					$image = imagecreatefromgif($file["tmp_name"]);
				break;
			
				case "png": 
					$image = imagecreatefrompng($file["tmp_name"]);
				break;			
			}		
			$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
					"res" . DIRECTORY_SEPARATOR . 
					"site" . DIRECTORY_SEPARATOR . 
					"img" . DIRECTORY_SEPARATOR . 
					"products" . DIRECTORY_SEPARATOR . 
					$this->getidproduct() . ".jpg";		
		
			imagejpeg($image, $dist);
			imagedestroy($image);
			$this->checkPhoto();
		}
	}
	
	public function getFromURL($desurl){
		$sql = new Sql();
		$rows = $sql->select("SELECT * FROM tb_products 
		                      WHERE desurl = :desurl LIMIT 1", array(
									":desurl" => $desurl));
		$this->setData($rows[0]);		
	}
	
	public function getCategories(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories cat
		                     INNER JOIN tb_productscategories prod_cat 
				             USING(idcategory) 
							 WHERE prod_cat.idproduct = :idproduct", array(
								   ":idproduct" => $this->getidproduct()) );		
	}
	
	
}
?>