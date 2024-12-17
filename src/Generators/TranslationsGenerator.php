<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Illuminate\Support\Arr;

class TranslationsGenerator extends EntityGenerator
{
    protected $translationPath;

    public function __construct()
    {
        parent::__construct();

        $this->translationPath = Arr::get($this->paths, 'translations', 'resources/lang/en/validation.php');
    }

    public function generate(): void
    {
        if (!file_exists($this->translationPath)) {
            $this->createTranslate();
        }

        // if ($this->isTranslationMissed('validation.exceptions.not_found')) {
        //     $this->appendNotFoundException();
        // }

        foreach (Arr::collapse($this->fields) as $field) {
            $this->addAttribute($this->translationPath, $field, $field);
        }
    }

    protected function isTranslationMissed($translation): bool
    {
        return __($translation) === 'validation.exceptions.not_found';
    }

    protected function createTranslate(): void
    {
        $stubPath = config('entity-generator.stubs.validation');

        $content = "<?php \n\n" . view($stubPath)->render();

        file_put_contents($this->translationPath, $content);

        $createMessage = "Created a new Translations dump on path: {$this->translationPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendNotFoundException(): void
    {
        
        $content = file_get_contents($this->translationPath);

        $stubPath = config('entity-generator.stubs.translation_not_found');

        $stubContent = view($stubPath)->render();

        $fixedContent = preg_replace('/\]\;\s*$/', "\n\t{$stubContent}", $content);

        file_put_contents($this->translationPath, $fixedContent);
    }

    public function addAttribute(string $filePath, string $key, string $value): bool
    {
        if (!\Illuminate\Support\Facades\File::exists($filePath)) {
            return false;
        }

        $config = require $filePath;

        if (isset($config['attributes'][$key])) {
            return true;
        }

        $config['attributes'][$key] = $value;
        $export = var_export($config, true);

        $export = preg_replace([
            '/\barray \(/',
            '/\)(,?)$/m'
        ], [
            '[',
            ']$1'
        ], $export);

        $output = "<?php\n\nreturn " . $export . ";";

        return file_put_contents($filePath, $output) !== false;
    }
}
