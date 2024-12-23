<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $prixPlancher = null;

    #[ORM\OneToOne(mappedBy: 'leProduit', cascade: ['persist', 'remove'])]
    private ?Enchere $laEnchere = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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

    public function getPrixPlancher(): ?float
    {
        return $this->prixPlancher;
    }

    public function setPrixPlancher(float $prixPlancher): static
    {
        $this->prixPlancher = $prixPlancher;

        return $this;
    }

    public function getLaEnchere(): ?Enchere
    {
        return $this->laEnchere;
    }

    public function setLaEnchere(?Enchere $laEnchere): static
    {
        // unset the owning side of the relation if necessary
        if ($laEnchere === null && $this->laEnchere !== null) {
            $this->laEnchere->setLeProduit(null);
        }

        // set the owning side of the relation if necessary
        if ($laEnchere !== null && $laEnchere->getLeProduit() !== $this) {
            $laEnchere->setLeProduit($this);
        }

        $this->laEnchere = $laEnchere;

        return $this;
    }


}
