<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Le titre doit être renseigné")
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Assert\NotBlank(message="La description doit être renseigné")
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="article")
     */
    private $commentList;

    public function __construct()
    {
        $this->commentList = new ArrayCollection();
    }

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


    public function afficherArticle()
    {
       return sprintf("Voici l'article %s", $this->getTitle());
    }

    public function __toString()
    {
       return sprintf("L'article %s", $this->getTitle());
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getCommentList(): Collection
    {
        return $this->commentList;
    }

    public function addCommentList(Comment $commentList): self
    {
        if (!$this->commentList->contains($commentList)) {
            $this->commentList[] = $commentList;
            $commentList->setArticle($this);
        }

        return $this;
    }

    public function removeCommentList(Comment $commentList): self
    {
        if ($this->commentList->removeElement($commentList)) {
            // set the owning side to null (unless already changed)
            if ($commentList->getArticle() === $this) {
                $commentList->setArticle(null);
            }
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'title'=> $this->title,
        );
    }
}
