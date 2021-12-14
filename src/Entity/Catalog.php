<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity(repositoryClass=CatalogRepository::class)
 *  @Vich\Uploadable
 */
class Catalog
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filePath;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="file", fileNameProperty="filePath")
     *
     * Validate that the file is a JSON
     * @Assert\File(maxSize= "17000k", mimeTypes = {"application/json","text/plain"})
     */
    public $file;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "submitted"})
     */
    private $state = 'submitted';

    /**
     * @ORM\ManyToMany(targetEntity="Product",cascade={"persist"})
     * @ORM\JoinTable(name="catalog_products",
     *      joinColumns={@ORM\JoinColumn(name="catalog_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="style_number", referencedColumnName="style_number")}
     *      )
     */
    private $products;

    public function __construct() {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }


    /**
     * 
     * @param File|UploadedFile|null $file
     */
    public function setFile(?File $file = null)
    {
        $this->file = $file;

        if (null !== $file) {
            // Change the updated column so the Doctrine listener are triggered
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * If specified retrieves only unsync products, otherwise retrieves all products associated to the catalog.
     * @param bool $unsync
     * @return Collection|Product[]
     */
    public function getProducts($unsync = false): Collection
    {
        if ($unsync) {
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq('state', null));
            return $this->products->matching($criteria);
        }
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }
}
