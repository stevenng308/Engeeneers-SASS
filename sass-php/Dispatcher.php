<?
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'SASS.php');
	require_once 'Slim-2.x/Slim/Slim.php';
	\Slim\Slim::registerAutoloader();

	class Dispatcher{
		private $sass = null;
		function __construct(){
			if(!isset($this->sass) && is_null($this->sass)){
				$this->sass = new SASS();
			} else {
				echo 'SASS connection already established'; //LOGGER
			}
		}

		function getResult(){
			return $this->obj;
		}

		function invokeCall($action, $options){
			$model = $this->_getDispatcher();
			if(method_exists($model, $action)){
				return json_encode($model->$action($options));
			} else {
				echo "ERROR!! function not found"; //EXECEPTION
			}
		}

		private function _getDispatcher(){
			return $this->sass;
		}
	}
?>
