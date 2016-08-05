<?php
namespace Araneum\Base\Forms\FormTypes;

use Araneum\Base\Forms\Transformers\EntityChoiceTransformer;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class EntityChoiceType
 *
 * @package Araneum\Base\Forms\FormTypes
 */
class EntityChoiceType extends AbstractType
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * EntityChoiceType constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new EntityChoiceTransformer($options['em']->getRepository($options['class']))
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @return mixed
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $registry = $this->registry;

        $resolver->setDefaults([
            'empty_value'   => false,
            'empty_data'    => null,
            'em'            => null,
            'query_builder' => null,
            'field'         => 'id',
        ]);

        $resolver->setRequired(['class']);

        $resolver->setDefault('choices', function (Options $options) use ($registry) {
            if (null === $options['query_builder']) {
                $results = $options['em']
                    ->createQueryBuilder()
                    ->select('e.id', 'e.'.$options['field'])
                    ->from($options['class'], 'e', 'e.id')
                    ->orderBy('e.'.$options['field'], 'ASC')
                    ->getQuery()
                    ->getArrayResult()
                ;
            } else {
                $results = $options['query_builder']
                    ->getQuery()
                    ->getArrayResult()
                ;
            }

            return array_map(
                function ($value) {
                    return end($value);
                },
                $results
            );
        });

        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            if (is_callable($queryBuilder)) {
                $queryBuilder = call_user_func($queryBuilder, $options['em']->getRepository($options['class']));
            }

            return $queryBuilder;
        };

        $emNormalizer = function (Options $options, $em) use ($registry) {
            if (!empty($em)) {
                if ($em instanceof ObjectManager) {
                    return $em;
                }

                return $registry->getManager($em);
            }

            $em = $registry->getManagerForClass($options['class']);
            if (empty($em)) {
                throw new \Exception(sprintf(
                    'Class "%s" seems not to be a managed Doctrine entity',
                    $options['class']
                ));
            }

            return $em;
        };

        $resolver->setNormalizer('em', $emNormalizer);
        $resolver->setNormalizer('query_builder', $queryBuilderNormalizer);


    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
