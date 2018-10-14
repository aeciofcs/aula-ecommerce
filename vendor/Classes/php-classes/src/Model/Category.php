<?php 
namespace Classes\Model;

use \Classes\DB\Sql;
use \Classes\Model;

class Category extends Model{
		
	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories
					         ORDER BY idcategory");
	}

	public function save(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
				":idcategory"  => $this->getidcategory(),
				":descategory" => $this->getdescategory() ));
		$this->setData($results[0]);
		Category::updateFile();
	}
	
	public function get($idcategory){
		$sql     = new Sql();
		$results = $sql->select("SELECT * FROM tb_categories
					             WHERE idcategory = :idcategory", array(
						           ':idcategory' => $idcategory ));
		$this->setData($results[0]);
	}
	
	public function delete(){
		$sql = new Sql();
		$sql->query("DELETE FROM tb_categories
		             WHERE idcategory = :idcategory", array(
					    ":idcategory" => $this->getidcategory() ));
		Category::updateFile();
	}
	
	public static function updateFile(){
		$categories = Category::listAll();
		$html = [];
		foreach ($categories as $row){
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
		                  "views" . DIRECTORY_SEPARATOR .
						  "categories-menu.html", implode('',$html));	
	}
	
	public function getProducts($related = true){
		$sql = new Sql();
		if( $related === true ){
			return $sql->select("SELECT * FROM tb_products 
								 WHERE idproduct IN (SELECT prod.idproduct FROM tb_products prod 
													 INNER JOIN tb_productscategories prod_cat USING(idproduct)
													 WHERE prod_cat.idcategory = :idcategory);", array(
															":idcategory" => $this->getidcategory() ));
		}else{
			return $sql->select("SELECT * FROM tb_products 
							     WHERE idproduct NOT IN (SELECT prod.idproduct FROM tb_products prod 
													     INNER JOIN tb_productscategories prod_cat USING(idproduct)
														 WHERE prod_cat.idcategory = :idcategory);", array(
																":idcategory" => $this->getidcategory() ));
		}		
	}
	
	public function addProduct(Product $product){
		$sql = new Sql();
		$sql->query("INSERT INTO tb_productscategories(idcategory, idproduct) 
		             VALUES(:idcategory, :idproduct)", array(
							":idcategory" => $this->getidcategory(),
							":idproduct"  => $product->getidproduct() ));
	}
	
	public function removeProduct(Product $product){
		$sql = new Sql();
		$sql->query("DELETE FROM tb_productscategories 
		             WHERE idcategory = :idcategory AND 
					       idproduct  = :idproduct", array(
							":idcategory" => $this->getidcategory(),
							":idproduct"  => $product->getidproduct() ));
	}
	
}
?>