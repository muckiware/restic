<?php

namespace MuckiRestic\Entity\Result\ResticResponse;

class Version
{
    protected string $version;
    protected string $goVersion;
    protected string $goOs;
    protected string $goArch;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getGoVersion(): string
    {
        return $this->goVersion;
    }

    public function setGoVersion(string $goVersion): void
    {
        $this->goVersion = $goVersion;
    }

    public function getGoOs(): string
    {
        return $this->goOs;
    }

    public function setGoOs(string $goOs): void
    {
        $this->goOs = $goOs;
    }

    public function getGoArch(): string
    {
        return $this->goArch;
    }

    public function setGoArch(string $goArch): void
    {
        $this->goArch = $goArch;
    }
}
