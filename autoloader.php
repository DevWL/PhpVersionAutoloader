<?php

class PhpVersionAutoloader{
  private $baseClassesDirPath = null;
  private $phpDirArr = [];
  private $phpVersionArr = [];
  private $phpDir = null;
  private $classes = []; /** Keep a record of all loaded classes */
  private $focePhpVersionId = null;
  private $matchPhpVersionId = null;

  public $usePreviousVersionIfClassNotFound = false;

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
   * NOTE! The order does matter. Start adding from newst version to the oldest
   * 
   * @see $this->phpVersionArr
   *
   * @param string $directory name 
   * @param string $phpVersion
   * @return void
   */
  public function registerPhpDir($dir, $phpVersion){
    $this->phpVersionArr[] = $phpVersion;
    $this->phpDirArr[] = $dir;
  }

  /**
   * Compare curent php version with  $this->phpVersionArr to determin the right path for class load
   */
  public function selectPhpDir(){

    foreach ($this->phpVersionArr as $key => $phpVersion) {
      if (version_compare(PHP_VERSION, $phpVersion) >= 0){
        $this->phpDir = $this->phpDirArr[$key];
        $this->matchPhpVersionId = $key;
        break;
      }
    }
  }


  public function focePhpVersionId(int $id)
  {
    $this->focePhpVersionId = $id;
  }

  /**
   * Register autloader
   *
   * @return void
   */
  public function register($id){
    spl_autoload_register(function($className) use ($id)
    {
        $namespace = str_replace("\\","/",__NAMESPACE__);
        $className = str_replace("\\","/",$className);

        $this->baseClassesDirPath = ($this->baseClassesDirPath === null) ? str_replace("\\","/",__DIR__) : $this->baseClassesDirPath;

        $class = $this->baseClassesDirPath."/classes/".$this->phpDirArr[$id].'/'. (empty($namespace)?"":$namespace."/")."{$className}.php";
        
        
        if (file_exists($class)){
          $this->classes[] = "+ Loaded class from: " . $this->phpDirArr[$id] . " for: - " . $class;
          include_once($class);
        }else if($this->usePreviousVersionIfClassNotFound === true){
          // if class does not exsist try to load file from lower php version directory. 
          $this->classes[] = "- Missing class in: " . $this->phpDirArr[$id] . " for: - " . $class;
          if($id + 1 <= count($this->phpVersionArr)){
            $this->register($id + 1);
          }
        }else{
          // do something scary
        }
    });
  }

  public function run()
  {
    $id = $this->matchPhpVersionId;
    if($this->focePhpVersionId !== null){
      $id = $this->focePhpVersionId;
    }
    
    $this->selectPhpDir(); // compare system php version and selects the correct phpX.X subfolder
    $this->register($id); // rgister autoloader 
  }

}


/**
 * Use example
 */
$loader = new PhpVersionAutoloader(); // create PhpVersionAutoloader object
$loader->setBaseClassesDirPath('C:/xampp/htdocs/blog/blog autoloading'); // if not used will use _DIR_ to create path

// Register supported PHP Version. The order does matter. Start adding from newst version to the oldest
$loader->registerPhpDir('php8.x', '8.0.0'); // as "folder name" => "php version" 
$loader->registerPhpDir('php7.x', '7.0.0'); // ...
$loader->registerPhpDir('php5.6', '5.6.0'); // ...

$loader->focePhpVersionId(0); // [0 => 'php8.x', 1 => 'php7.x', ...]

$loader->usePreviousVersionIfClassNotFound = true; // dafault is false

$loader->run(); // run autoloader
