<?php
namespace Bnacci\Gamio\Console\Commands;

use Bnacci\Gamio\Console\Commands\Concerns\WithInputValidation;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class CreateBadgeCommand extends Command
{
    use WithInputValidation;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'gamio:badge {argument?} {--option}';
    protected $signature = 'gamio:badge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is my custom command for the package.';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    protected $name;
    protected $level;
    protected $namespace = "App\\Gamio";

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->name  = $this->askWithValidation("What's badge name?", ["required", "string", "min:2"], "name");
        $this->level = $this->askWithValidation("What's level user gain this badge?", ["required", "numeric"]);

        $path = $this->getSourceFilePath();
        $this->makeDirectory(dirname($path));
        $contents = $this->getSourceFile();

        if ($this->confirm('Do you want to create this badge?', true)) {
            if (! $this->files->exists($path)) {
                if ($this->files->put($path, $contents)) {
                    $this->info("File : {$path} created");
                }

                $this->saveBadgeClasses();
            } else {
                $this->warn("File : {$path} already exits");
            }
        }
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../stubs/badge.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables()
    {
        return [
            'NAMESPACE' => "{$this->namespace}\\Badges",
            'CLASS_NAME' => $this->getSingularClassName($this->name),
            'GAIN_IN'    => $this->level,
            'BADGE_ID'   => Str::slug($this->name),
            'BADGE_NAME' => $this->name,
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return base_path("{$this->namespace}\\Badges") . '\\' . $this->getSingularClassName($this->name) . 'Badge.php';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    protected function saveBadgeClasses()
    {
        $file = base_path("{$this->namespace}\\Badges") . '\\UserBadges.php';

        $data = "{$this->namespace}\\Badges\\{$this->getSingularClassName($this->name)}Badge";

        if (file_exists($file)) {
            require_once $file;

            $classFQN = "{$this->namespace}\\Badges\\UserBadges";

            if (class_exists($classFQN)) {
                $obj     = new $classFQN();
                $classes = $obj->getData();

                if (! is_array($classes)) {
                    $classes = [];
                }
            } else {
                $classes = [];
            }
        } else {
            $classes = [];
        }

        // Evita duplicatas
        if (! in_array($data, $classes)) {
            $classes[] = $data;
        } else {
            // $this->warn("Classe {$data} já existe no arquivo.");
            return;
        }

        // Ordena (opcional)
        sort($classes);

        // Monta o conteúdo da classe
        $lines = "<?php\n\n";
        $lines .= "namespace {$this->namespace}\\Badges;\n\n";
        $lines .= "class UserBadges\n{\n";
        $lines .= "    public function getData()\n    {\n";
        $lines .= "        return [\n";

        foreach ($classes as $class) {
            $id = (new $class())->getId();
            $lines .= "            \"{$id}\" => \\{$class}::class,\n";
        }

        $lines .= "        ];\n";
        $lines .= "    }\n";
        $lines .= "}\n";

        // Salva no arquivo
        file_put_contents($file, $lines);
    }

}
