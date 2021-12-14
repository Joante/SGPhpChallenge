<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string", length=255)
     */
    private $style_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $price_amount;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $price_currency;

    /**
     * @ORM\Column(type="array")
     */
    private $images = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $state;


    public function getStyleNumber(): ?string
    {
        return $this->style_number;
    }

    public function setStyleNumber(string $style_number): self
    {
        $this->style_number = $style_number;

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

    public function getPriceAmount(): ?int
    {
        return $this->price_amount;
    }

    public function setPriceAmount(int $price_amount): self
    {
        $this->price_amount = $price_amount;

        return $this;
    }

    public function getPriceCurrency(): ?string
    {
        return $this->price_currency;
    }

    public function setPriceCurrency(string $price_currency): self
    {
        $this->price_currency = $price_currency;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * For JsonResponse purpose
     * @return array
     */
    public function toArray()
    {
        $product =  [
            'styleNumber' => $this->getStyleNumber(),
            'name' => $this->getName(),
            'price' => $this->getPriceCurrency().' '.$this->getPriceAmount(),
        ];

        foreach ($this->getImages() as $x => $image) {
            $product['image'.$x] = $image;
        }

        return $product;
    }
}
