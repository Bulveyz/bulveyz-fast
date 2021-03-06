<?php

namespace Bulveyz\Commander;

use RedBeanPHP\R;
use Bulveyz\Middleware\CsrfSecurity;

/*
 * BCommander Console
 *
 * BCommander is a console with it you can create controllers,
 * models and much more, it greatly simplifies the development process,
 * here is assembled all the functional BCommander
 */

class BCommander
{
  public $controllerName; // Name of Controller
  public $modelName;  // Name of Model
  public $errors = []; // Errors Array
  
  /*
   * Start Bulveyz Commander
   *
   * Initializing the console. A global function
   * for generating a CSRF token and the template
   * itself has also been added
   */
  public function bCommander()
  {
    $loader = new \Twig_Loader_Filesystem(__DIR__ . '/');
    $twig = new \Twig_Environment($loader, array(
        'cache' => false
    ));
    $twig->addGlobal('csrf_token', CsrfSecurity::generateCsrfToken());
    echo $twig->render('commander.tmp');
  }

  /*
   * Create new controller
   *
   * We check the validity of the data and the presence
   * of the already existing controller, if all is well,
   * then replace the controller name with the controller
   * name in the name that the developer entered
   */
  public function makeController($controllerName = null)
  {
    $controllerName = ucwords($_POST['command']);
    $this->controllerName = $controllerName;
    
    if (file_exists("App/Controllers/{$this->controllerName}Controller.php")) {
      $this->errors[] = "The {$this->controllerName}Controller already exists!";
    } elseif (strripos($this->controllerName, '_') == 0) {
      $this->controllerName = ucwords(str_replace('_', '', $this->controllerName));
    }
    
    if (!empty($this->errors)) {
      echo array_shift($this->errors);
    } else {
      $newControllerTemplate = file_get_contents(__DIR__ . '/templates/makeController.txt');
      $newController = str_replace('ControllerName', $this->controllerName, $newControllerTemplate);
      file_put_contents("App/Controllers/{$this->controllerName}Controller.php", $newController);
    }
  }

  /*
  * Create new model
  *
  * We check the validity of the data and the presence
  * of the already existing model, if all is well,
  * then replace the model name with the model
  * name in the name that the developer entered
  */
  public function makeModel($modelName = null)
  {
    $modelName = ucwords($_POST['command']);

    $this->$modelName = $modelName;

    if (file_exists("App/Models/{$this->$modelName}.php")) {
      $this->errors[] = "The model {$this->$modelName} already exists!";
    } elseif (strripos($this->$modelName, '_') == 0) {
      $this->$modelName = ucwords(str_replace('_', '', $this->$modelName));
    }

    if (!empty($this->errors)) {
      echo array_shift($this->errors);
    } else {
      $newModelTemplate = file_get_contents(__DIR__ . '/templates/makeModel.txt');
      $newModel = str_replace('ModelName', $this->$modelName, $newModelTemplate);
      file_put_contents("App/Models/{$this->$modelName}.php", $newModel);
    }

  }

  /*
   * Create new controller and model for with name
   *
   * Call makeController and makeModel functions
   */
  public function makeControllerAndModel()
  {
    $this->makeController($_POST['command']);
    $this->makeModel($_POST['command']);
  }

  /*
   * Create templates auth in root dir (templates/auth)
   *
   * Check whether there is already authorization in
   * the project, then apply the templates, add the
   * function of the installation in Bulveyz.php, routes
   * in web.php and new html templates
   */
  public function makeAuth()
  {
    if (file_exists('App/Controllers/AuthController.php') || file_exists('templates/auth')) {
      $this->errors[] = 'Authorization has already been carried out or there are custom authorization files in the project!';
    }
    if (!empty($this->errors)) {
      echo array_shift($this->errors);
      exit();
    } else {
      $filename = __DIR__ . '../../App/Bulveyz.php';
      $file = file($filename);
      $file[49] .= file_get_contents(__DIR__ . '../../Auth/templates/Bulveyz.txt');
      file_put_contents($filename, $file);

      $authControllerTemplate = file_get_contents(__DIR__ . '../../Auth/templates/AuthController.txt');
      file_put_contents("App/Controllers/AuthController.php", $authControllerTemplate);

      $authRoutes = file_get_contents(__DIR__ . '../../Auth/templates/authRoutes.txt');
      file_put_contents('./routes/web.php', $authRoutes, FILE_APPEND);

      $headerComponent = file_get_contents(__DIR__ . '../../Auth/templates/header.txt');
      file_put_contents("templates/main/header.tmp", $headerComponent);

      $welcomeTemplate = file_get_contents(__DIR__ . '../../Auth/templates/welcome.txt');
      file_put_contents("templates/welcome.tmp", $welcomeTemplate);

      mkdir('templates/auth');
      copy(__DIR__ . '../../Auth/templates/login.txt', 'templates/auth/login.tmp');
      copy(__DIR__ . '../../Auth/templates/register.txt', 'templates/auth/register.tmp');
      copy(__DIR__ . '../../Auth/templates/reset.txt', 'templates/auth/reset.tmp');
      copy(__DIR__ . '../../Auth/templates/restore.txt', 'templates/auth/restore.tmp');
    }
  }

  /*
   * Drop all rows in table from DB
   */
  public function trashAll()
  {
    if ($table = R::findAll($_POST['command'])) {
      R::trashAll($table);
    } else {
      echo 'Table not found or empty!';
    }
  }
}