<?php

namespace Araneum\Base\EntityTrait;

/**
 * Trait for createAt and updateAt fields in Entities
 */
trait DateTrait
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="updated_at")
     */
    private $updatedAt;

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Set updatedAt and createdAt to current if value not specified time on prePersist event
     *
     * @ORM\PrePersist
     */
    public function preCreateChangeDate()
    {
        $this->createdAt = $this->createdAt ?: new \DateTime();
        $this->updatedAt = $this->updatedAt ?: new \DateTime();
    }

    /**
     * Set updatedAt to current time on preUpdate event
     *
     * @ORM\PreUpdate
     */
    public function preUpdateChangeDate()
    {
        $this->updatedAt = new \DateTime();
    }
}
