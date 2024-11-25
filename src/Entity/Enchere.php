<?php

namespace App\Entity;

use App\Repository\EnchereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnchereRepository::class)]
class Enchere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeureFin = null;

    #[ORM\Column]
    private ?float $prixDebut = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'laEnchere')]
    private Collection $lesParticipations;

    #[ORM\OneToOne(mappedBy: 'laEnchere', cascade: ['persist', 'remove'])]
    private ?Produit $leProduit = null;

    public function __construct()
    {
        $this->lesParticipations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDateHeureFin(): ?\DateTimeInterface
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(\DateTimeInterface $dateHeureFin): static
    {
        $this->dateHeureFin = $dateHeureFin;

        return $this;
    }
    public function getPrixDebut(): ?float
    {
        return $this->prixDebut;
    }
    public function setPrixDebut(float $prixDebut): static
    {
        $this->prixDebut = $prixDebut;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getLesParticipations(): Collection
    {
        return $this->lesParticipations;
    }

    public function addLesParticipation(Participation $lesParticipation): static
    {
        if (!$this->lesParticipations->contains($lesParticipation)) {
            $this->lesParticipations->add($lesParticipation);
            $lesParticipation->setLaEnchere($this);
        }

        return $this;
    }

    public function removeLesParticipation(Participation $lesParticipation): static
    {
        if ($this->lesParticipations->removeElement($lesParticipation)) {
            // set the owning side to null (unless already changed)
            if ($lesParticipation->getLaEnchere() === $this) {
                $lesParticipation->setLaEnchere(null);
            }
        }

        return $this;
    }

    public function getLeProduit(): ?Produit
    {
        return $this->leProduit;
    }

    public function setLeProduit(?Produit $leProduit): static
    {
        // unset the owning side of the relation if necessary
        if ($leProduit === null && $this->leProduit !== null) {
            $this->leProduit->setLaEnchere(null);
        }

        // set the owning side of the relation if necessary
        if ($leProduit !== null && $leProduit->getLaEnchere() !== $this) {
            $leProduit->setLaEnchere($this);
        }

        $this->leProduit = $leProduit;

        return $this;
    }

}
