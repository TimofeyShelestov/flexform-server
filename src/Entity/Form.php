<?php

namespace App\Entity;

use App\Repository\FormRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormRepository::class)]
#[ORM\Table(name: 'forms')]
#[ORM\Index(columns: ['hash'], name: 'forms_hash_idx')]
#[ORM\Index(columns: ['created_at'], name: 'forms_created_at_idx')]
#[ORM\Index(columns: ['deleted_at'], name: 'forms_deleted_at_idx')]
class Form implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 1, max: 100)]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, options: ['default' => null])]
    private ?DateTimeImmutable $deleted_at = null;

    #[ORM\OneToMany(mappedBy: 'form_id', targetEntity: UserPermission::class)]
    private Collection $userPermissions;

    #[ORM\OneToMany(mappedBy: 'form_id', targetEntity: Submission::class)]
    private Collection $submissions;

    #[ORM\OneToMany(mappedBy: 'form_id', targetEntity: FormField::class, orphanRemoval: true)]
    private Collection $fields;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $recaptcha_token = null;

    #[ORM\Column(type: 'string', unique: true)]
    private ?string $hash = null;

    public function __construct()
    {
        $this->userPermissions = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->fields = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
        $this->hash = $this->generateHash();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    /**
     * @return Collection<int, UserPermission>
     */
    public function getUserPermissions(): Collection
    {
        return $this->userPermissions;
    }

    public function addUserPermission(UserPermission $userPermission): static
    {
        if (!$this->userPermissions->contains($userPermission)) {
            $this->userPermissions->add($userPermission);
            $userPermission->setForm($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Submission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function addSubmission(Submission $submission): static
    {
        if (!$this->submissions->contains($submission)) {
            $this->submissions->add($submission);
            $submission->setForm($this);
        }

        return $this;
    }

    public function removeSubmission(Submission $submission): static
    {
        if ($this->submissions->removeElement($submission)) {
            // set the owning side to null (unless already changed)
            if ($submission->getForm() === $this) {
                $submission->setForm(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FormField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(FormField $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setForm($this);
        }

        return $this;
    }

    public function removeField(FormField $field): static
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getForm() === $this) {
                $field->setForm(null);
            }
        }

        return $this;
    }

    public function getRecaptchaToken(): ?string
    {
        return $this->recaptcha_token;
    }

    public function setRecaptchaToken(?string $recaptcha_token): static
    {
        $this->recaptcha_token = $recaptcha_token;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function isRecaptchaEnabled(): bool
    {
        return $this->getRecaptchaToken() !== null && strlen($this->getRecaptchaToken()) > 0;
    }

    private function generateHash(): string
    {
        return md5(uniqid((string) rand(), true));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'createdAt' => $this->getCreatedAt(),
            'deletedAt' => $this->getDeletedAt(),
            'recaptchaToken' => $this->getRecaptchaToken(),
            'hash' => $this->getHash(),
        ];
    }
}