<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("");
use Bitrix\Main\Loader;
Loader::includeModule("highloadblock");
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\UserTable;
?>

<?



class LikesBitrix{
    public $id;
    public $userid;
    public function __construct($id,$userid) {
        $this->id = $id;
        $this->userid = $userid;
        $this->hlbl = 1;;
        $this->hlblock = HL\HighloadBlockTable::getById($this->hlbl)->fetch();
        $this->entity=HL\HighloadBlockTable::compileEntity($this->hlblock);
        $this->entity_data_class = $this->entity->getDataClass();

    }
    function countLikesBitrix(){
        $datas=$this->entity_data_class;
        $rsData = $datas::getList(array( //делаю выборку записи по параметрам айди товара и айди юзера
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array("UF_USERID"=>$this->userid)
        ));
        $count=0;
        while($arData = $rsData->Fetch()){
            $count++;
        }
        return $count;
    }
    function getProductBitrix(){
        $datas=$this->entity_data_class;
        $rsData = $datas::getList(array( //делаю выборку записи по параметрам айди товара и айди юзера
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array("UF_USERID"=>$this->userid)
        ));
        while($arData = $rsData->Fetch()){
            $product[]=$arData['UF_PRODUCTID'];
        }
        return $product;
    }

    function addRemoveLikeBitrix(){
        $datas=$this->entity_data_class;
        $rsData = $datas::getList(array( //выборку записи по параметрам айди товара и айди юзера
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array("UF_PRODUCTID"=>$this->id,"UF_USERID"=>$this->userid)
        ));
        while($arData = $rsData->Fetch()){
            $mass[]=$arData;
        }
        if (empty($mass)){ // если массив пустой, значит добавляем в избранное
            $data = array(
                "UF_PRODUCTID"=>$this->id,
                "UF_USERID"=>$this->userid
            );
            $datas::add($data);
        }else{ // если существуют , то удаляем из избранного
            $datas::Delete($mass[0]['ID']);

        }
    }
    function clearLikeBitrix(){
        $datas=$this->entity_data_class;
        $rsData = $datas::getList(array( //елаю выборку записи по параметрам айди товара и айди юзера
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array("UF_USERID"=>$this->userid)
        ));
        while($arData = $rsData->Fetch()){
            $datas::Delete($arData['ID']);
        }
    }
}
class Likes{
    public $id;
    public function __construct($id) {
        $this->id = $id;
    }
    function addRemoveLike()
    {
        session_start(); // старт сессии
        if (isset($_SESSION['likes'])) { //существует ли сессия
            if (empty($_SESSION['likes'])) { //не пуста ли сессия
                $_SESSION['likes'][$this->id ] = $this->id ; //если пусто добавляем айди
            } else { // если не пусто
                foreach ($_SESSION['likes'] as $product) { // провоходим по сессии и смотрим
                    if ($product == $this->id ) { // если есть такой айди
                        unset($_SESSION['likes'][$this->id ]); // убираем с сессии
                    } else { // если нету
                        $_SESSION['likes'][$this->id ] = $this->id ; // добавляем в сессию

                    }
                }
            }
        } else { // если не существует, добавляем
            $_SESSION['likes'][$this->id ] = $this->id ;
        }
        return $_SESSION['likes']; // возвращаем сессию

    }
    function clearLikes(){
        session_start();
        unset($_SESSION['likes']); // или $_SESSION = array() для очистки всех данных сессии
        session_destroy();
    }
//считаем количество избранного
    function countLikes(){
        $count=0;
        foreach ($_SESSION['likes'] as $like){
            $count++;
        }
        return $count;
    }
    function getProduct(){
        $product=array();
        foreach ($_SESSION['likes'] as $like){
            $product[]=$like;
        }
        return $product;
    }
}



global $USER; //берем пользователя
$arFilter = Array(
    "ID"=>$USER->GetID()
);
$parameters=array(
    "select"=>Array("ID","NAME"),
    "filter"=>$arFilter,
);
$result = UserTable::getList($parameters)->fetch();
$id=$result['ID']; // получаем айди пользователя

if ($USER->IsAuthorized()){ //авторизован, делаем запрос к бд
    $myclass2=new LikesBitrix(32,$id); // иницилизируем класс с нашими данными
    print_r($myclass2->getProductBitrix()); // получаем список товаров
}else { // делаем добавление или удаление с сессии
    $myclass=new Likes(37);
    print_r( $myclass->getProduct());
}

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
