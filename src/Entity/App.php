<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppRepository")
 */
class App
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $appCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $appName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $appVersion;

    /**
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $appPartnerSchool;

    /**
     * @ORM\Column(type="text")
     */
    private $appDescription;

    public function getAppCode(): ?string
    {
        return $this->appCode;
    }

    public function getAppName(): ?string
    {
        return $this->appName;
    }

    public function setAppName(string $appName): self
    {
        $this->appName = $appName;

        return $this;
    }

    public function getAppVersion(): ?string
    {
        return $this->appVersion;
    }

    public function setAppVersion(string $appVersion): self
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    public function getAppPartnerSchool(): ?string
    {
        return $this->appPartnerSchool;
    }

    public function setAppPartnerSchool(?string $appPartnerSchool): self
    {
        $this->appPartnerSchool = $appPartnerSchool;

        return $this;
    }

    public function getAppDescription(): ?string
    {
        return $this->appDescription;
    }

    public function setAppDescription(string $appDescription): self
    {
        $this->appDescription = $appDescription;

        return $this;
    }
}
