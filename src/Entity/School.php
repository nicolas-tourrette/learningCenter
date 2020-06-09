<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolRepository")
 */
class School
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=8)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolClass", mappedBy="school")
     */
    private $schoolClasses;

    public function __construct()
    {
        $this->schoolClasses = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function __toString() 
    {
        return (string) $this->id; 
    }

    /**
     * @return Collection|SchoolClass[]
     */
    public function getSchoolClasses(): Collection
    {
        return $this->schoolClasses;
    }

    public function addSchoolClass(SchoolClass $schoolClass): self
    {
        if (!$this->schoolClasses->contains($schoolClass)) {
            $this->schoolClasses[] = $schoolClass;
            $schoolClass->setSchool($this);
        }

        return $this;
    }

    public function removeSchoolClass(SchoolClass $schoolClass): self
    {
        if ($this->schoolClasses->contains($schoolClass)) {
            $this->schoolClasses->removeElement($schoolClass);
            // set the owning side to null (unless already changed)
            if ($schoolClass->getSchool() === $this) {
                $schoolClass->setSchool(null);
            }
        }

        return $this;
    }
}
