<?php

class PhpVersionAutoloader{
  private $baseClassesDirPath = null;
  private $phpVersionArr = [];
  private $phpDir = null;
  private $classes = []; /** Keep a record of all loaded classes */

  /**
   * Allows to change base dir path.
   * If not set the path will be set to file _DIR_
   * 
   * @see $this->baseClassesDirPath
   *
   * @param string $path
   * @return void
   */
  public function setBaseClassesDirPath($path)
  {
    $this->baseClassesDirPath = $path;
  }

  /**
   * Map available dir name and php version
   * 
   * @see $this->phpVersionArr
   *
   * @param string $directory name 
   * @param string $phpVersion
   * @return void
   */
  public function registerPhpDir($dir, $phpVersion){
    $this->phpVersionArr[] = [$dir => $phpVersion];
  }

  /**
   * Compare curent php version with  $this->phpVersionArr to determin the right path for class load
   */
  public function selectPhpDir(){

    foreach ($this->phpVersionArr as $key => $phpVDir) {
      $this->position = $key;
      foreach($phpVDir as $key => $value){
        if (version_compare(PHP_VERSION, $value) >= 0){
          $this->phpDir = $key;
          break 2;
        }
      }
    }
  }

  /**
   * Register autloader
   *
   * @return void
   */
  public function register(){
    spl_autoload_register(function($className)
    {
        $namespace = str_replace("\\","/",__NAMESPACE__);
        $className = str_replace("\\","/",$className);

        $this->baseClassesDirPath = ($this->baseClassesDirPath === null) ? str_replace("\\","/",__DIR__) : $this->baseClassesDirPath;

        $class = $this->baseClassesDirPath."/classes/".$this->phpDir.'/'. (empty($namespace)?"":$namespace."/")."{$className}.php";
        $this->classes[] = $class;
        
        if (file_exists($class)){
          include_once($class);
        }else{
          // ... if not exsist try to load lower php version file?
          // ... or throw new Error("Error Processing Request", 1);
          
        }
    });
  }

}


/**
 * Use example
 */
$loader = new PhpVersionAutoloader(); // init PhpVersionAutoloader object
$loader->setBaseClassesDirPath('C:/xampp/htdocs/blog/blog autoloading'); // if not used will use _DIR_ to create path

$loader->registerPhpDir('php8.x', '8.0.0'); // as "folder name" => "php version" 
$loader->registerPhpDir('php7.x', '7.0.0'); // ...
$loader->registerPhpDir('php5.6', '5.6.0'); // ...

$loader->selectPhpDir(); // compare system php version and selects the correct phpX.X subfolder
$loader->register(); // register autoloader
