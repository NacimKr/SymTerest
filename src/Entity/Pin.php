<?php

namespace App\Entity;

use App\Entity\Traits\TimeStampable;
use App\Repository\PinRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PinRepository::class)]
#[ORM\Table(name: 'pins')]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Pin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"You should add a title")]
    #[Assert\Length(min:3, minMessage:"It's 3 letters minimum")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:"You should add a description")]
    #[Assert\Length(min:10)]
    private ?string $description = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'pin_image', fileNameProperty: 'imageName')]
    // #[Assert\NotNull(message:"You should add a image")]
    #[Assert\Image(
        maxSize:"1M",
        maxSizeMessage:"fichier est trop grand",
        mimeTypes:[
            'image/jpg',
            'image/png',
            'image/jpeg'
        ]
    )]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'pins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // #[ORM\ManyToOne(inversedBy: 'pins')]
    // private ?User $user = null;

    use TimeStampable;

    //On definit des constances si on veux faire passer des paramètre qui change pas ou rarement
    public const NUM_ITEMS_PER_PAGE = 10;

    // #[ORM\Column]
    // private ?\DateTimeImmutable $createdAt = null;

    // #[ORM\Column]
    // private ?\DateTimeImmutable $updatedAt = null;


    // public function __construct(){
    //     $this->created_at = new \DateTimeImmutable();
    //     $this->updated_at = new \DateTimeImmutable();
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    // public function getCreatedAt(): ?\DateTimeImmutable
    // {
    //     return $this->createdAt;
    // }

    // public function setCreatedAt(\DateTimeImmutable $createdAt): self
    // {
    //     $this->createdAt = $createdAt;

    //     return $this;
    // }

    // public function getUpdatedAt(): ?\DateTimeImmutable
    // {
    //     return $this->updatedAt;
    // }

    // public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    // {
    //     $this->updatedAt = $updatedAt;

    //     return $this;
    // }

    // #[ORM\PrePersist] // Avant la création d'un pins
    // #[ORM\PreUpdate] // Après la modification d'un pins
    // public function updatedTimeStamp(): void
    // {
    //     if($this->createdAt === null){ 
    //         $this->createdAt = new \DateTimeImmutable();
    //     }
    //     $this->updatedAt = new \DateTimeImmutable();
    // }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    // public function getUser(): ?User
    // {
    //     return $this->user;
    // }

    // public function setUser(?User $user): self
    // {
    //     $this->user = $user;

    //     return $this;
    // }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
