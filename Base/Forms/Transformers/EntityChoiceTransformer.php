<?php
namespace Araneum\Base\Forms\Transformers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityChoiceTransformer
 *
 * @package Araneum\Base\Forms\Transformers
 */
class EntityChoiceTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectRepository
     */
    private $entityRepository;

    /**
     * EntityChoiceTransformer constructor.
     *
     * @param ObjectRepository $entityRepository
     */
    public function __construct(ObjectRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * @param object|array $entity
     * @return int|int[]
     *
     * @throws TransformationFailedException
     */
    public function transform($entity)
    {
        if ($entity === null) {
            return null;
        } elseif (is_array($entity) || $entity instanceof Collection) {
            $ids = [];
            foreach ($entity as $subEntity) {
                $ids[] = $subEntity->getId();
            }

            return $ids;
        } elseif (is_object($entity)) {
            return $entity->getId();
        }

        throw new TransformationFailedException((is_object($entity)? get_class($entity) : '').'('.gettype($entity).') is not a valid class for EntityToIdTransformer');
    }

    /**
     * @param int|array $id
     * @return object|object[]
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($id)
    {
        if ($id === null) {
            return null;
        } elseif (is_numeric($id)) {
            $entity = $this->entityRepository->findOneBy(array('id' => $id));
            if ($entity === null) {
                throw new TransformationFailedException('A '.$this->entityRepository->getClassName().' with id #'.$id.' does not exist!');
            }

            return $entity;
        } elseif (is_array($id)) {
            if (empty($id)) {
                return new ArrayCollection();
            }

            $entities = $this->entityRepository->findBy(['id' => $id]);
            if (count($id) != count($entities)) {
                throw new TransformationFailedException('Some '.$this->entityRepository->getClassName().' with ids #'.implode(', ', $id).' do not exist!');
            }

            return new ArrayCollection($entities);
        }

        throw new TransformationFailedException(gettype($id).' is not a valid type for EntityToIdTransformer');
    }
}
