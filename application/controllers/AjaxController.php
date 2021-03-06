<?php

class AjaxController extends Zend_Controller_Action
{

	public function init()
	{
		$this->_helper->AjaxContext()
			->addActionContext('index', 'json')
			->addActionContext('album', 'json')
			->addActionContext('user', 'json')
			->initContext('json');
	}

	public function indexAction()
	{
		$post = $this->getRequest()->getParams();

		if (isset($post["urlsaver"])) {

			$_SESSION["urlsaver"] = $post["urlsaver"];
			return true;

		}

		if(isset($post["language"])){

			$url = preg_replace("/ua|ru/", $post["language"], $post["page"]);
			setcookie("language",$post["language"],time()+31556926 ,'/');
			$this->view->link = $url;

		}
	}

	public function userAction()
	{
		$usersDb = new Application_Model_DbTable_Users();
		$cityDb = new Application_Model_DbTable_City();
		$imagesDb = new Application_Model_DbTable_Images();
		$amazonModel = new Application_Model_Amazon();
		$random = new My_Resizer();
		$this->lang = Zend_Registry::get('Zend_Translate');

		$post = $this->getRequest()->getParams();
		if (isset($post["abouthide"]))setcookie("hideabout",1,time()+604800 ,'/');
		if (isset($post["banUser"])) $usersDb->updateItem(array("banned" => 1), $post["banUser"]);
		if (isset($post["mainImg"])) {

			$album = $imagesDb->getItem($post["mainImg"]);
			$imagesDb->updateIsMain(0, $album["album_id"]);
			$imagesDb->updateItem(array("is_main" => 1), $post["mainImg"]);

			$img = $imagesDb->getItem($post["mainImg"]);
			$this->view->img = "/data/img/vata/small_{$img["img_name"]}";

		}

		if (isset($post["imageCrop"])) {

			$name = basename($post["imageCrop"]);
			$dir = "./data/img/vata/";
			@unlink("{$dir}crop_{$name}");
			$random->load("{$dir}{$name}")->crop($post["x1"], $post["y1"], $post["x2"], $post["y2"])->save("{$dir}crop_{$name}");
			$amazonModel->goToCloud("crop_{$name}");

		}

		if (isset($post["getCityByObl"])) {

			$option = "<option value = ''>{$this->lang->translate("Місто")}</option>";
			foreach($cityDb->getByOblId($post["getCityByObl"]) as $value){
				$option.="<option value = '{$value["id"]}'>{$value["city"]}</option>";
			}
			$this->view->city = $option;
		}
	}

	public function albumAction()
	{

		$albumDb = new Application_Model_DbTable_Terrorist();
		$newsDb = new Application_Model_DbTable_News();
		$imagesDb = new Application_Model_DbTable_Images();
		$notify = new Application_Model_DbTable_Notify();
		$config = Zend_Registry::get('config');
		$randomModel = new Application_Model_Random();
		$usersDb = new Application_Model_DbTable_Users();
		$cacheModel = new Application_Model_Cache();

		$post = $this->getRequest()->getParams();

		if (isset($post["dropAlbum"])) $albumDb->updateItem(array("checked" => 2), $post["dropAlbum"]);
		if (isset($post["dropArticle"])) $newsDb->updateItem(array("status" => 2), $post["dropArticle"]);
		if (isset($post["approveArticle"])) $newsDb->updateItem(array("status" => 1), $post["approveArticle"]);
		if (isset($post["dropImg"])) $imagesDb->deleteItem($post["dropImg"]);
		if (isset($post["dropFacebook"])) $albumDb->updateItem(array("fb_posted" => 1), $post["dropFacebook"]);
		if (isset($post["saveAlbum"])){

			if($imagesDb->checkToMain($post["saveAlbum"])){

				$albumDb->updateItem(array("checked" => 1), $post["saveAlbum"]);
				if(APPLICATION_ENV != "testing")$randomModel->createThemeOnForum($albumDb->getItem($post["saveAlbum"]));
				if(APPLICATION_ENV != "testing")$cacheModel->clearCache();
				$album = $albumDb->getItem($post["saveAlbum"]);
				$owner = $usersDb->getItem($album["owner_id"]);

				if($owner["email"]){
					$arr = array(

						"email" => $owner["email"],
						"vars" => json_encode($album),
						"action" => "appoved"

					);
					$notify->createItem($arr);
				}

				$this->view->noimg = 0;

			}else{
				$this->view->noimg = 1;
			}

		}

		if (isset($post["propose"])){

			$arr = array(

				"email" => "vataclubs@gmail.com",
				"vars" => json_encode($post),
				"action" => "propose"

			);

			$notify->createItem($arr);

		}

	}


}





