<?php define('DS', DIRECTORY_SEPARATOR);

// Set this one to your own blueprint root if you changed it.
$blueprint_root = __DIR__ . DS . 'site' . DS . 'blueprints/pages';

/**
 * === Let's build an IDE-helper for Kirby for PHPStorm!
 *
 * You can create the IDE-helper by putting this file in your project, then run the following command:
 *   php _ide_helper_generator.php
 *
 * This will create an _ide_helper_models.php based in your blueprints and an _ide_helper.php based on
 * the kirby/extensions/methods.php file (which contains field-methods). TODO: add custom field method-files.
 *
 * If you change your blueprints, you should run the command again.
 *
 * You should *NOT* add the resulting php-files to your site with `include` or `require`. They are
 * only meant to be analysed by PHPStorm (or any other IDE of your choice).
 *
 * You could add them to Git, you could add `_ide_helper*` to your .gitignore, up to you!
 *
 * You can hint your IDE with `/** @var SomeblueprintPage $page * /` in your templates.
 *
 * Happy IDE-ing!
 */

// Import the Kirby Toolkit to make our life easier

$autoloader = __DIR__ . '/vendor/autoload.php';

require($autoloader);

$helperFile = new IDEHelperFile('_ide_helper_models.php');

foreach (\Kirby\Toolkit\Dir::read($blueprint_root) as $filename) {
  $blueprint = \Kirby\Data\Yaml::read($blueprint_root . DS . $filename);

  $filename = \Kirby\Toolkit\F::name($filename);
  $className = $filename == 'site' ? 'Site' : str::ucfirst($filename) . 'Page extends \Kirby\Cms\Page';

  if (!isset($blueprint['fields'])) {
    $helperFile->addEmpty($className);
    continue;
  }

  foreach (array_keys($blueprint['fields']) as $field) {
    $helperFile->add($className, $field, 'Field');
  }
}

$helperFile->save();

/**
 * Now for Kirby's build in magic methods
 */

$helperFile = new IDEHelperFile('_ide_helper.php');

$fieldMethodsFiles = [];
// Yeah, you can add your Field method-files to this array
//$fieldMethodsFiles[] = __DIR__ . DS . 'kirby' . DS . 'extensions' . DS . 'methods.php';


class Field { public static $methods; }
$fieldMethodsReturnTypes = [];
foreach ($fieldMethodsFiles as $methodsFile) {
  require_once $methodsFile;

  // Just regex them.
  preg_match_all(
    '/\@return (.*?)\n \*\/\nfield::\$methods\[\'(.*?)\'\]/',
    f::read($methodsFile),
    $matches,
    PREG_SET_ORDER
  );

  foreach ($matches as $match) {
    $return = $match[1];
    $method = $match[2];
    $fieldMethodsReturnTypes[$method] = $return;
  }
}

if (is_iterable(field::$methods)) {
  foreach (field::$methods as $name => $method) {
    $func = new ReflectionFunction($method);
    $args = array_map(function ($arg) { return $arg->name; }, $func->getParameters());
    $return = array_key_exists($name, $fieldMethodsReturnTypes)
      ? $fieldMethodsReturnTypes[$name] : '';

    $helperFile->add('Field', $name, $return, $args);
  }
}

// Also add the v:: validator class
foreach (v::$validators as $name => $validator) {
  $func = new ReflectionFunction($validator);
  $args = array_map(function ($arg) { return $arg->name; }, $func->getParameters());

  $helperFile->add('V', $name, 'boolean', $args, true);
}

$helperFile->save();


/**
 * Helpers
 */
class IDEHelperFile {

  private $filename;
  private $methods = [];

  public function __construct($filename)
  {
    $this->filename = $filename;
  }

  public function add($class, $method, $return, $args = [], $static = false)
  {
    $this->methods[$class][$method] = [
      'return' => $return,
      'static' => $static,
      'args' => $args,
    ];
  }

  public function addEmpty($class) {
    $this->methods[$class] = [];
  }

  public function save()
  {
    $helper = "<?php\n";

    foreach ($this->methods as $class => $methods) {
      $helper .= "\n/**\n";

      foreach ($methods as $method => $d) {
        $helper .= ' * @method ';
        $helper .= $d['static'] ? 'static' : '';
        $helper .= $d['return'] . " $method(";
        $helper .= $d['args'] ? '$' . implode(', $', $d['args']) : '';
        $helper .=  ")\n";
      }

      $helper .= " */\nclass $class {}\n";
    }

    \Kirby\Toolkit\F::write($this->filename, $helper);
  }
}