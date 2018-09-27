<?php

namespace Classes;

use Rain\Tpl;

Class Page{
	
	private $tpl;
	private $options = [];
	private $defaults = [
		"data" => []
	];
	
	public function __construct($opts = array(), $tpl_dir = "/views/"){ 
		//As variaveis vão vir de acordo com as rotas. 
		//Dependendo da rota, será enviado dados para a classe tpl.
		
		$this->options = array_merge($this->defaults, $opts); 
		//Array_merge mescla dois arrays, o ultimo parametro sempre ira 
		//sobrescrever os anteriores.
		
		$config = array(
			"tpl_dir"   => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"     => false
		);
		Tpl::configure($config);
		
		$this->tpl = new Tpl;
		
		$this->setData($this->options["data"]);
				
		$this->tpl->draw("header");
		
	}
	
	private function setData($data = array()){
		foreach ($data as $key => $value){
			//atribuindo as variaveis no template
			$this->tpl->assign($key,$value);
		}		
	}
	
	//Conteudo da pagina. $name recebe o nome do template. $data recebe os dados 
	//que queremos passar. $returnHTML é para informar se é para jogar na tela ou se é 
	//para retornar um HTML.
	public function setTpl($name, $data = array(), $returnHTML = false){
		$this->setData($data);
		return $this->tpl->draw($name, $returnHTML);
	}
	
	public function __destruct(){
		
		$this->tpl->draw("footer");
	
	}
}

?>