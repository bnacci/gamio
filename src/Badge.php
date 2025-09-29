<?php
namespace Bnacci\Gamio;

class Badge
{
    protected $gainIn;
    protected $id;
    protected $name;

    public function icon(): string | null
    {
        return null;
    }

    public function getGainIn()
    {
        return $this->gainIn;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
