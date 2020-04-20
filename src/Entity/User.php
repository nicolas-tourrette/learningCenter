<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;



/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="username", message="Un utilisateur possédant cet identifiant ou ce mail existe déjà.")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Length(min=8, minMessage="Le mot de passe doit contenir au moins 8 caractères.")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, minMessage="Votre nom doit contenir au moins 5 caractères.")
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $school;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $profilImage;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $apps = [];

    /**
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $partnerSchool;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $passwordRequestedAt;

    /**
    * @var string
    *
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastIP;

    /**
     * @ORM\Column(type="boolean")
     */
    private $paiementStatus;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $paiementDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paiementType;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Test", mappedBy="user", orphanRemoval=true)
     */
    private $tests;

    public function __construct(){
        $this->isActive = true;
        $this->updated = new \DateTime();
        $this->tests = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(string $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getProfilImage(): ?string
    {
        return $this->profilImage;
    }

    public function setProfilImage(string $profilImage): self
    {
        $this->profilImage = $profilImage;

        return $this;
    }

    public function getApps(): ?array
    {
        return $this->apps;
    }

    public function addApps(array $apps, $remainingApps){
        $currentApps = $this->apps;

        if($remainingApps > 0 || $remainingApps == "UNLIMITED"){
            $currentApps = array_merge($currentApps, $apps);
            $currentApps = array_unique($currentApps);
            $nbApps = count($currentApps);
        }
        else{
            return false;
        }

        $this->setApps($currentApps);
    }

    public function addPartnerApps(array $apps){
        $currentApps = $this->apps;
        $currentApps = array_merge($currentApps, $apps);
        $currentApps = array_unique($currentApps);
        $this->setApps($currentApps);
    }

    public function removeApp(string $index){
        $currentApps = $this->apps;
        unset($currentApps[array_search($index, $currentApps)]);
        $this->setApps($currentApps);
    }

    public function setApps(?array $apps): self
    {
        $this->apps = $apps;

        return $this;
    }

    public function getPartnerSchool(): ?string
    {
        return $this->partnerSchool;
    }

    public function setPartnerSchool(?string $partnerSchool): self
    {
        $this->partnerSchool = $partnerSchool;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        //$roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        if($password != null){
            $this->password = $password;
            return $this;
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt($passwordRequestedAt)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastIP(): ?string
    {
        return $this->lastIP;
    }

    public function setLastIP(?string $lastIP): self
    {
        $this->lastIP = $lastIP;

        return $this;
    }

    public function getPaiementStatus(): ?bool
    {
        return $this->paiementStatus;
    }

    public function setPaiementStatus(bool $paiementStatus): self
    {
        $this->paiementStatus = $paiementStatus;

        return $this;
    }

    public function getPaiementDate(): ?\DateTimeInterface
    {
        return $this->paiementDate;
    }

    public function setPaiementDate(?\DateTimeInterface $paiementDate): self
    {
        $this->paiementDate = $paiementDate;

        return $this;
    }

    public function getPaiementType(): ?string
    {
        return $this->paiementType;
    }

    public function setPaiementType(?string $paiementType): self
    {
        $this->paiementType = $paiementType;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->tests->contains($test)) {
            $this->tests[] = $test;
            $test->setUser($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->contains($test)) {
            $this->tests->removeElement($test);
            // set the owning side to null (unless already changed)
            if ($test->getUser() === $this) {
                $test->setUser(null);
            }
        }

        return $this;
    }
}
