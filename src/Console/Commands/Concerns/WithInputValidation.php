<?php
namespace Bnacci\Gamio\Console\Commands\Concerns;

use Illuminate\Support\Facades\Validator;

trait WithInputValidation
{
    public function askWithValidation(
        string $message, array $rules = [], string $name = 'value'
    ) {
        $answer = $this->ask($message);

        $validator = Validator::make([
            $name => $answer,
        ], [
            $name => $rules,
        ]);

        if ($validator->passes()) {
            return $answer;
        }

        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return $this->askWithValidation($message, $rules, $name);
    }
}
